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
    public function getAll(Request $request)
    {
        Crawler::getAll();
        $file="all.csv";
        return Response::download($file);
    }

    public function getIds()
    {
        return response()->json( ["data"=>Crawler::getIds("")]);
    }

    public function getData(Request $request)
    {   
        $dom=Crawler::getDOM($request->ids);
        $data=[];
        if($dom['status'] != "error")
            $data=array_merge($data,Crawler::parseOnlyDOM($dom['body']));

        return response()->json( ["data"=>$data]);
        
    }
    
}
