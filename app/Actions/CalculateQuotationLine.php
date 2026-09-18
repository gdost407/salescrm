<?php

namespace App\Actions;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class CalculateQuotationLine
{
    /** @return array{subtotal: string, tax_total: string, total: string} */
    public function handle(string $type, string $rate, string $quantity, string $gstRate): array
    {
        $amount = BigDecimal::of($rate)->multipliedBy($quantity)->toScale(2, RoundingMode::HalfUp);

        if ($type === 'inventory') {
            $total = $amount;
            $subtotal = $total->multipliedBy(100)->dividedBy(BigDecimal::of(100)->plus($gstRate), 2, RoundingMode::HalfUp);
            $tax = $total->minus($subtotal);
        } else {
            $subtotal = $amount;
            $tax = $subtotal->multipliedBy($gstRate)->dividedBy(100, 2, RoundingMode::HalfUp);
            $total = $subtotal->plus($tax);
        }

        return ['subtotal' => (string) $subtotal, 'tax_total' => (string) $tax, 'total' => (string) $total];
    }
}
