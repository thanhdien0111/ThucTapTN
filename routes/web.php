<?php

use Illuminate\Support\Facades\Route;
use App\Models\Session;
use App\Http\Controllers\SessionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/checkin', [SessionController::class, 'checkin']);
    Route::get('/checkout', [SessionController::class, 'checkout']);
    // Cập nhật route cho phương thức index
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions');
});


