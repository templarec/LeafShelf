<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;



class ISBNQuery extends Controller
{
    function fetch(Request $request){

        $isbn =  $request->isbn;
        $url = "https://api2.isbndb.com/book/$isbn";
        $key = env('ISBNdb_KEY');
        $header = [
            'Accept' => '*/*',
            'Authorization' => $key,
        ];

        $response = Http::withHeaders($header)->get($url);
        return [
            $response->json()];
    }
}
