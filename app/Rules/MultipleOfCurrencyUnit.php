<?php

namespace App\Rules;

use App\Services\InstallmentCalculator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * قاعدة "بلا كسور حسب العملة": مضاعفات الألف للدينار، ومضاعفات الخمسين للدولار.
 */
class MultipleOfCurrencyUnit implements ValidationRule
{
    public function __construct(protected string $currency) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $unit = InstallmentCalculator::unitFor($this->currency);

        if ((int) $value % $unit !== 0) {
            $unitLabel = $this->currency === 'USD' ? '50' : '1000';
            $fail("قيمة :attribute يجب أن تكون من مضاعفات {$unitLabel}.");
        }
    }
}
