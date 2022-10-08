<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{
    public function index()
    {
        $licenses = License::all();

        $post = Http::withOptions(['verify' => false])->post('http://m-pedia.id.lar/api/input_data', [
            'licenses' => $licenses,
        ]);

        dd($post->json());
    }
}
