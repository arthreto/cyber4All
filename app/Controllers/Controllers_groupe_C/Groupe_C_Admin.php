<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Controllers\BaseController;

class Groupe_C_Admin extends BaseController
{
    public function GroupC_Admin(): string
    {
        return view('header')
            . view('notice')
            . view('group_c_admin')
            . view('footer');
    }
}