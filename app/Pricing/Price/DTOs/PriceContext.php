<?php

namespace App\Pricing\Price\DTOs;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class PriceContext
{
    public function __construct(
        public int $serviceVariantId,
        public int $priceTypeId,
        public int $currencyId,
        public CarbonImmutable $serviceDate,
        public int $quantity,
        public ?int $passengerTypeId = null,
        public ?int $commercialPolicyId = null,
    ) {
        if ($this->quantity < 1) {
            throw new InvalidArgumentException(
                'La cantidad tarifaria debe ser al menos 1.'
            );
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            serviceVariantId: (int) $data['service_variant_id'],
            priceTypeId: (int) $data['price_type_id'],
            currencyId: (int) $data['currency_id'],
            serviceDate: CarbonImmutable::parse($data['service_date']),
            quantity: (int) $data['quantity'],
            passengerTypeId: isset($data['passenger_type_id'])
                ? (int) $data['passenger_type_id']
                : null,
            commercialPolicyId: isset($data['commercial_policy_id'])
                ? (int) $data['commercial_policy_id']
                : null,
        );
    }
}
