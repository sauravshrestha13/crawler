<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use PhpQuery\PhpQuery;
use App\Helpers\Crawler;
use Response;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function export(Request $request)
    {
        Crawler::run($request->first);
        $file=$request->first.".csv";
        return Response::download($file);
    }
    
}
