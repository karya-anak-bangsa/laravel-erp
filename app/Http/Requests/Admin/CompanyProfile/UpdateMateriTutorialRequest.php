<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\MateriTutorial;
use Illuminate\Support\Arr;

class UpdateMateriTutorialRequest extends StoreMateriTutorialRequest
{
    public function rules(): array
    {
        return [
            // Tutorial tidak ikut diubah: alamat materi memuat slug tutorialnya.
            ...Arr::except(parent::rules(), 'id_tutorial'),
            'urutan_ke' => ['required', 'integer', 'min:0', 'max:'.MateriTutorial::URUTAN_MAKS],
        ];
    }
}
