<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ManagerId implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currentUserId = auth()->user()->id;
        if(in_array($currentUserId, $value) && $currentUserId != 1) {
            $fail('Không thể chọn bản thân làm người duyệt!');
        }
    }
}
