<?php

use App\Services\InstallmentCalculator;

it('splits a debt evenly by count when it divides cleanly', function () {
    $installments = InstallmentCalculator::byCount(900_000, 'IQD', 3, 30, '2026-01-01');

    expect($installments)->toHaveCount(3);
    expect(array_column($installments, 'amount'))->toBe([300_000, 300_000, 300_000]);
});

it('gives the exact regression case: 1,000,000 IQD over 3 installments', function () {
    $installments = InstallmentCalculator::byCount(1_000_000, 'IQD', 3, 30, '2026-01-01');

    expect(array_column($installments, 'amount'))->toBe([333_000, 333_000, 334_000]);
});

it('keeps every non-final IQD installment a multiple of 1000', function () {
    $installments = InstallmentCalculator::byCount(1_000_000, 'IQD', 7, 15, '2026-01-01');

    foreach (array_slice($installments, 0, -1) as $installment) {
        expect($installment['amount'] % 1000)->toBe(0);
    }
});

it('keeps every non-final USD installment a multiple of 50', function () {
    $installments = InstallmentCalculator::byCount(1000, 'USD', 3, 30, '2026-01-01');

    foreach (array_slice($installments, 0, -1) as $installment) {
        expect($installment['amount'] % 50)->toBe(0);
    }
});

it('never lets a non-final installment round down to zero', function () {
    // 4,000 / 3 = 1,333 naively, which floors to 0 when rounded down to the nearest 1,000 unit
    $installments = InstallmentCalculator::byCount(4000, 'IQD', 3, 30, '2026-01-01');

    foreach (array_slice($installments, 0, -1) as $installment) {
        expect($installment['amount'])->toBeGreaterThanOrEqual(1000);
    }
});

it('produces a negative final installment if the caller passes a count too high for the amount', function () {
    // byCount() trusts its caller — it does not itself validate that count is sane
    // for the given amount. StoreDebtRequest/StoreMyDebtRequest are responsible for
    // rejecting this combination before it ever reaches the calculator (see the
    // "rejects an installment count too high for the debt amount" feature tests).
    $installments = InstallmentCalculator::byCount(2000, 'IQD', 10, 30, '2026-01-01');

    expect(end($installments)['amount'])->toBeLessThan(0);
});

it('always sums installments back to the exact total amount, regardless of rounding', function () {
    $cases = [
        [1_000_000, 'IQD', 3],
        [1_000_000, 'IQD', 7],
        [7_777_000, 'IQD', 11],
        [1000, 'USD', 3],
        [999, 'USD', 4],
        [50, 'USD', 2],
    ];

    foreach ($cases as [$total, $currency, $count]) {
        $installments = InstallmentCalculator::byCount($total, $currency, $count, 30, '2026-01-01');

        expect(array_sum(array_column($installments, 'amount')))->toBe($total);
    }
});

it('computes installment count from a fixed installment amount, ceiling up', function () {
    $installments = InstallmentCalculator::byInstallmentAmount(1_000_000, 'IQD', 300_000, 30, '2026-01-01');

    // 1,000,000 / 300,000 = 3.33 -> 4 installments, last one absorbs the remainder
    expect($installments)->toHaveCount(4);
    expect(array_column($installments, 'amount'))->toBe([300_000, 300_000, 300_000, 100_000]);
    expect(array_sum(array_column($installments, 'amount')))->toBe(1_000_000);
});

it('numbers installments sequentially starting at 1', function () {
    $installments = InstallmentCalculator::byCount(300_000, 'IQD', 3, 30, '2026-01-01');

    expect(array_column($installments, 'seq_number'))->toBe([1, 2, 3]);
});

it('spaces due dates by the given interval starting from the first due date', function () {
    $installments = InstallmentCalculator::byCount(300_000, 'IQD', 3, 15, '2026-01-01');

    expect(array_column($installments, 'due_date'))->toBe([
        '2026-01-01',
        '2026-01-16',
        '2026-01-31',
    ]);
});

it('uses 1000 as the currency unit for IQD and 50 for USD', function () {
    expect(InstallmentCalculator::unitFor('IQD'))->toBe(1000);
    expect(InstallmentCalculator::unitFor('USD'))->toBe(50);
});
