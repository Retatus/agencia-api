<?php

namespace App\Quotation\Services;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationStatus;
use DomainException;
use Illuminate\Support\Facades\DB;

class QuotationWorkflowService
{
    private const TRANSITIONS = [
        'DRAFT' => ['READY', 'CANCELLED'],
        'READY' => ['DRAFT', 'SENT', 'CANCELLED', 'EXPIRED'],
        'SENT' => ['CONFIRMED', 'REJECTED', 'CANCELLED', 'EXPIRED'],
        'CONFIRMED' => ['CANCELLED'],
        'REJECTED' => [],
        'EXPIRED' => [],
        'CANCELLED' => [],
    ];

    public function assertEditable(Quotation $quotation): void
    {
        $code = $this->statusCode($quotation);

        if ($code !== 'DRAFT') {
            throw new DomainException(
                "La cotización está en estado {$code} y no puede editarse. Reábrala como borrador cuando la transición esté permitida."
            );
        }
    }

    public function allowedActions(Quotation $quotation): array
    {
        $code = $this->statusCode($quotation);
        $currentCalculation =
            $quotation->calculation_status === Quotation::CALCULATION_CURRENT
            && empty($quotation->pending_calculation_items ?? []);

        return [
            'edit' => $code === 'DRAFT',
            'recalculate' => $code === 'DRAFT',
            'mark_ready' => $code === 'DRAFT' && $currentCalculation,
            'reopen' => $code === 'READY',
            'send' => $code === 'READY' && $currentCalculation,
            'resend' => $code === 'SENT',
            'confirm' => $code === 'SENT',
            'reject' => $code === 'SENT',
            'cancel' => in_array($code, ['DRAFT', 'READY', 'SENT', 'CONFIRMED'], true),
            'print' => in_array($code, ['READY', 'SENT', 'CONFIRMED'], true)
                && $currentCalculation,
            'export_pdf' => in_array($code, ['READY', 'SENT', 'CONFIRMED'], true)
                && $currentCalculation,
        ];
    }

    public function transition(
        Quotation $quotation,
        string $targetCode,
        ?string $reason = null,
    ): Quotation {
        return DB::transaction(function () use ($quotation, $targetCode, $reason) {
            $quotation = Quotation::query()
                ->with(['status', 'passengers', 'itineraries.items'])
                ->lockForUpdate()
                ->findOrFail($quotation->getKey());

            $currentCode = $this->statusCode($quotation);
            $allowedTargets = self::TRANSITIONS[$currentCode] ?? [];

            if (!in_array($targetCode, $allowedTargets, true)) {
                throw new DomainException(
                    "No se permite cambiar la cotización de {$currentCode} a {$targetCode}."
                );
            }

            if ($targetCode === 'READY') {
                $this->assertReady($quotation);
            }

            if (in_array($targetCode, ['REJECTED', 'CANCELLED'], true) && blank($reason)) {
                throw new DomainException('Debe indicar el motivo del cambio de estado.');
            }

            $statusId = QuotationStatus::query()
                ->where('code', $targetCode)
                ->where('active', true)
                ->value('id');

            if (!$statusId) {
                throw new DomainException("El estado {$targetCode} no está configurado o está inactivo.");
            }

            $timestamps = [
                'SENT' => 'sent_at',
                'CONFIRMED' => 'confirmed_at',
                'REJECTED' => 'rejected_at',
                'CANCELLED' => 'cancelled_at',
            ];

            $values = [
                'quotation_status_id' => $statusId,
                'status_changed_at' => now(),
                'status_reason' => $reason,
            ];

            if (isset($timestamps[$targetCode])) {
                $values[$timestamps[$targetCode]] = now();
            }

            $quotation->update($values);

            return $quotation->fresh()->load([
                'customer',
                'currency',
                'status',
                'passengers.passengerType',
                'passengers.country',
                'itineraries.items',
            ]);
        });
    }

    private function assertReady(Quotation $quotation): void
    {
        $errors = [];

        if ($quotation->calculation_status !== Quotation::CALCULATION_CURRENT) {
            $errors[] = 'existen cálculos pendientes';
        }

        if (!empty($quotation->pending_calculation_items ?? [])) {
            $errors[] = 'existen servicios pendientes de revisar';
        }

        if (!$quotation->passengers->where('active', true)->count()) {
            $errors[] = 'no existen pasajeros activos';
        }

        if (!$quotation->itineraries->count()) {
            $errors[] = 'no existe itinerario';
        }

        $activeItems = $quotation->itineraries
            ->flatMap(fn ($itinerary) => $itinerary->items)
            ->where('active', true);

        if (!$activeItems->count()) {
            $errors[] = 'no existen servicios activos';
        }

        $catalogWithoutPrice = $activeItems->first(fn ($item) =>
            $item->item_type === 'CATALOG' && !$item->price_id
        );

        if ($catalogWithoutPrice) {
            $errors[] = 'hay servicios de catálogo sin precio resuelto';
        }

        if ((float) $quotation->total <= 0) {
            $errors[] = 'el total debe ser mayor que cero';
        }

        if (
            $quotation->commercial_valid_until
            && $quotation->commercial_valid_until->isBefore(today())
        ) {
            $errors[] = 'la vigencia comercial ya venció';
        }

        if ($errors) {
            throw new DomainException(
                'La cotización no está lista para enviar: '.implode('; ', $errors).'.'
            );
        }
    }

    private function statusCode(Quotation $quotation): string
    {
        return (string) ($quotation->status?->code
            ?? $quotation->status()->value('code')
            ?? 'DRAFT');
    }
}
