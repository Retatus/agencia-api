<?php

namespace App\Quotation\Enums;

enum PaymentStatus: string
{
    case NOT_REQUIRED = 'NOT_REQUIRED';
    case PENDING = 'PENDING';
    case PARTIAL = 'PARTIAL';
    case PAID = 'PAID';
}
