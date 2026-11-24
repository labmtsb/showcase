<?php

namespace App\Http\Controllers;

use App\Models\Project;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    //

    /**
     * method index project
     */
    public function index()
    {
        $projects = Project::where('user_id', Auth::user()->id)->get();

        return view('pages.project.show-project');
    }
}
