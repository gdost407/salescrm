<?php

use App\Actions\CalculateQuotationLine;

test('quotation line calculations use exact decimal rounding', function (string $type, string $rate, string $quantity, string $gst, array $expected) {
    expect((new CalculateQuotationLine)->handle($type, $rate, $quantity, $gst))->toBe($expected);
})->with([
    'exclusive service' => ['service', '100', '2', '18', ['subtotal' => '200.00', 'tax_total' => '36.00', 'total' => '236.00']],
    'inclusive inventory' => ['inventory', '118', '2', '18', ['subtotal' => '200.00', 'tax_total' => '36.00', 'total' => '236.00']],
    'zero GST' => ['inventory', '99.95', '1', '0', ['subtotal' => '99.95', 'tax_total' => '0.00', 'total' => '99.95']],
    'fractional quantity' => ['service', '99.99', '1.125', '18', ['subtotal' => '112.49', 'tax_total' => '20.25', 'total' => '132.74']],
    'inclusive rounding' => ['inventory', '100', '1', '18', ['subtotal' => '84.75', 'tax_total' => '15.25', 'total' => '100.00']],
    'half cent' => ['service', '0.05', '1', '10', ['subtotal' => '0.05', 'tax_total' => '0.01', 'total' => '0.06']],
    'four decimal tax rate' => ['service', '10000', '1', '18.1234', ['subtotal' => '10000.00', 'tax_total' => '1812.34', 'total' => '11812.34']],
]);
