<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ApplyCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'coupon' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'coupon.required' => 'من فضلك أدخلي كود الخصم.',
            'coupon.max' => 'كود الخصم طويل جداً.',
        ];
    }

    public function code(): string
    {
        $validated = $this->validated();

        return (string) $validated['coupon'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'coupon' => Str::upper(trim((string) $this->input('coupon'))),
        ]);
    }
}
