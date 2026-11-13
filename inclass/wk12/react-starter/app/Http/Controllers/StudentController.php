<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('students/index', [
            'students' => [
                ['id' => 1, 'name' => 'Alex Rivera', 'major' => 'ITWS', 'year' => 2028],
                ['id' => 2, 'name' => 'Priya Shah', 'major' => 'CSCI', 'year' => 2027],
                ['id' => 3, 'name' => 'Sam Ortiz', 'major' => 'ITWS', 'year' => 2029],
            ],
        ]);
    }
}
