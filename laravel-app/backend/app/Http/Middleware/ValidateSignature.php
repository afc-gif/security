<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\ValidateSignature as Middleware;
use Illuminate\Support\Facades\Redirect;

class ValidateSignature extends Middleware
{
    protected $except = [
        // 'fbalbum',
    ];

    protected function redirectToRoute($default)
    {
        return Redirect::to($default);
    }
}
