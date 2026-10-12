<?php

namespace App\Controllers\c_grp;

use App\Controllers\BaseController;

class QuizController extends BaseController
{

    public function AfficherQuiz(): string{
        return view('header')
            . view('/v_grp/quiz')
            . view('footer');

    }
}