import { test } from 'node:test';
import assert from 'node:assert/strict';
import { calculateLine, money } from '../../public/assets/js/quotation-editor.js';

for (const [name, type, rate, quantity, gst, expected] of [
    ['exclusive', 'service', '100', '2', '18', ['200.00', '36.00', '236.00']],
    ['inclusive', 'inventory', '118', '2', '18', ['200.00', '36.00', '236.00']],
    ['zero tax', 'inventory', '99.95', '1', '0', ['99.95', '0.00', '99.95']],
    ['fractional', 'service', '99.99', '1.125', '18', ['112.49', '20.25', '132.74']],
    ['inclusive rounding', 'inventory', '100', '1', '18', ['84.75', '15.25', '100.00']],
    ['half cent', 'service', '0.05', '1', '10', ['0.05', '0.01', '0.06']],
    ['four decimal tax rate', 'service', '10000', '1', '18.1234', ['10000.00', '1812.34', '11812.34']],
]) {
    test(name, () => assert.deepEqual(Object.values(calculateLine(type, rate, quantity, gst)).map(money), expected));
}
