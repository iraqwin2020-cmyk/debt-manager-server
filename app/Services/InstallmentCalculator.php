<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * حاسبة الأقساط الذكية — تحسب الطرف الناقص (العدد أو المبلغ) تلقائياً، وتضمن أن
 * كل قسط عدد صحيح ومضاعف لوحدة عملته (1000 للدينار، 50 للدولار) ما عدا القسط
 * الأخير الذي يمتص أي فارق ناتج عن القسمة ليتطابق المجموع تماماً مع أصل الدين.
 */
class InstallmentCalculator
{
    public static function unitFor(string $currency): int
    {
        return $currency === 'USD' ? 50 : 1000;
    }

    /**
     * @return array<int, array{seq_number: int, amount: int, due_date: string}>
     */
    public static function byCount(int $totalAmount, string $currency, int $count, int $intervalDays, string $firstDueDate): array
    {
        $unit = self::unitFor($currency);
        $roughPerInstallment = intdiv($totalAmount, $count);
        $perInstallment = max($unit, intdiv($roughPerInstallment, $unit) * $unit);

        return self::build($totalAmount, $perInstallment, $count, $intervalDays, $firstDueDate);
    }

    /**
     * @return array<int, array{seq_number: int, amount: int, due_date: string}>
     */
    public static function byInstallmentAmount(int $totalAmount, string $currency, int $installmentAmount, int $intervalDays, string $firstDueDate): array
    {
        $count = (int) ceil($totalAmount / $installmentAmount);

        return self::build($totalAmount, $installmentAmount, $count, $intervalDays, $firstDueDate);
    }

    protected static function build(int $totalAmount, int $perInstallment, int $count, int $intervalDays, string $firstDueDate): array
    {
        $installments = [];
        $date = Carbon::parse($firstDueDate);
        $remaining = $totalAmount;

        for ($i = 1; $i <= $count; $i++) {
            $isLast = $i === $count;
            $amount = $isLast ? $remaining : $perInstallment;
            $remaining -= $amount;

            $installments[] = [
                'seq_number' => $i,
                'amount' => $amount,
                'due_date' => $date->copy()->addDays(($i - 1) * $intervalDays)->toDateString(),
            ];
        }

        return $installments;
    }
}
