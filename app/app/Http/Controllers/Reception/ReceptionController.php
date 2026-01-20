<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ReceptionController extends Controller
{
    public function dashboard(): RedirectResponse
    {
        return redirect()->route('reception.calendar');
    }
}
