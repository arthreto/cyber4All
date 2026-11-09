<?php

namespace App\Controllers;

use App\Models\ThemeModel;

class FlashcardController extends BaseController
{

    public function selection(): string
    {

        return view('header')
            . view('flashcard/selectionMode')
            . view('footer');
    }
    public function choisirMode($mode)
    {
        session()->set('mode', $mode);

        return redirect()->to(base_url('selectionTheme'));
    }
    public function theme(): string
    {
        $model_theme = new ThemeModel();

        $data['themes'] = $model_theme->getThemes();
        $data['mode'] = session()->get('mode');

        return view('header')
            . view('flashcard/selectionTheme',$data)
            . view('footer');
    }
    public function sexercer(): string
    {

        return view('header')
            . view('flashcard/exerce')
            . view('footer');
    }
    public function apprendre(): string
    {

        return view('header')
            . view('flashcard/apprend')
            . view('footer');
    }

}

