<?php

namespace App\Http\Controllers\Admin\Prompt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    public function index(){
        return view('prompt.index');
    }
}
