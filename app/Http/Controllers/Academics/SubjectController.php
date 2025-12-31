<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return Inertia::render('Academics/Subjects/Index');
    }

    public function create()
    {
        return Inertia::render('Academics/Subjects/Create');
    }
}
