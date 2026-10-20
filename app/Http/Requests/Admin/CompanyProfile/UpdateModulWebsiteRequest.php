<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModulWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Checkbox tak tercentang tidak terkirim; form juga mengirim hidden input bernilai 0.
        $this->merge(['status_aktif' => $this->boolean('status_aktif')]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'status_aktif' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status_aktif' => 'status',
        ];
    }
}
