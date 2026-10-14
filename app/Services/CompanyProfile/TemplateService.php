<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Template;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreTemplateRequest
     */
    public function simpan(array $data): Template
    {
        return DB::transaction(function () use ($data): Template {
            $template = Template::create($data);
            $this->nonaktifkanTemplateLainSejenis($template);

            return $template;
        });
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateTemplateRequest
     */
    public function perbarui(Template $template, array $data): Template
    {
        return DB::transaction(function () use ($template, $data): Template {
            $template->update($data);
            $this->nonaktifkanTemplateLainSejenis($template);

            return $template;
        });
    }

    // Boleh banyak template, tetapi hanya satu yang aktif per jenis (DATABASE.md): website selalu jelas
    // memakai template mana untuk Full Color dan Monochrome.
    private function nonaktifkanTemplateLainSejenis(Template $template): void
    {
        if (! $template->status_aktif) {
            return;
        }

        // Termasuk yang terhapus agar tidak ada dua template aktif bila kelak dipulihkan dari sampah.
        Template::withTrashed()
            ->whereKeyNot($template->getKey())
            ->where('jenis', $template->jenis)
            ->where('status_aktif', true)
            ->update(['status_aktif' => false]);
    }
}
