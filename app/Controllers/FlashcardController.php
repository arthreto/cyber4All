<?php

namespace App\Controllers;

class FlashcardController extends BaseController
{
    public function selection(): string
    {

        return view('header')
            . view('flashcard/selectionMode')
            . view('footer');
    }
    public function theme(): string
    {

        return view('header')
            . view('flashcard/selectionTheme')
            . view('footer');
    }

}

