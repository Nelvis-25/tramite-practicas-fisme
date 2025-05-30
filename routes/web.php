<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActaController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdf/acta/{id}', [ActaController::class, 'verActa'])->name('pdf.acta');
