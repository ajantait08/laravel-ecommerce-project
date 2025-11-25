<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    //return view('login');
    return redirect('/login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register',function(){
    return view('register');
})->name('register');

Route::get('/dashboard',function(){
    return view('dashboard');
})->name('dashboard');

//Route::get('/login',);

Route::post('login', [AuthController::class, 'login'])->name('login.submit');
Route::post('register',[AuthController::class, 'register'])->name('register.submit');
