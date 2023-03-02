<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', [WebhookController::class, 'inbox'])->middleware(
    'WebHook'
);
Route::get('/notRegistered', [ReplyController::class, 'notRegistered']);
Route::get('/processregister', [WebhookController::class, 'processregister']);

Route::get('postuser', [PostController::class, 'index']);
Route::get('/invalids' , function(){
    return view('invalid');
});
