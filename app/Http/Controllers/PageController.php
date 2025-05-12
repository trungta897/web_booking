<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function howItWorks()
    {
        return view('pages.how-it-works');
    }

    public function pricing()
    {
        return view('pages.pricing');
    }
}
