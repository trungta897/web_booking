<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::withCount('tutors')->get();
        return view('subjects.index', compact('subjects'));
    }
}
