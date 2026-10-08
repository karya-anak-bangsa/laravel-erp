<?php

namespace App\View\Composers;

use App\Models\CompanyProfile\KontakKami;
use Illuminate\View\View;

/**
 * Angka badge menu sidebar, dirujuk lewat kunci 'badge' di config/menu.php.
 */
class MenuComposer
{
    public function compose(View $view): void
    {
        $view->with('badgeMenu', [
            'kontak-kami-belum-dibaca' => KontakKami::query()->belumDibaca()->count(),
        ]);
    }
}
