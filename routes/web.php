<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActaController;
use App\Mail\NotificacionDeRecordatoria;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdf/acta/{id}', [ActaController::class, 'verActa'])->name('pdf.acta');
