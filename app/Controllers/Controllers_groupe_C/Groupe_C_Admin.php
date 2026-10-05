<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Controllers\BaseController;

class Groupe_C_Admin extends BaseController
{
    public function GroupeC_Admin(): string
    {
        return view('header')
            . view('notice')
            . view('groupe_c/groupe_c_ADMIN/groupe_c_admin')
            . view('footer');
    }
}