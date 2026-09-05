<?php

namespace App\Pricing\PriceList\Enums;

enum AdjustmentType: string
{
    case PERCENTAGE = 'PERCENTAGE';
    case FIXED = 'FIXED';
    case OVERRIDE = 'OVERRIDE';
}
