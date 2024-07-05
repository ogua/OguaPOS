<?php

use App\Http\Controllers\WebController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckposAuthentication;
use App\Livewire\Pos\EditPosComponent;
use App\Livewire\PosComponent;
use Illuminate\Support\Facades\Route;

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
    return redirect()->to("/admin");
});

//Route::get('/download-order/{id}', [WebController::class, 'downloadorder'])->name('downloadorder');

Route::get('/download-invoice/{record}', [WebController::class, 'invoice'])->name('download-invoice');

Route::get('/send-invoice/{record}', [WebController::class, 'sendinvoice'])->name('send-invoice');

Route::get('/download-order/{record}', [WebController::class, 'order'])->name('download-order');

Route::get('/order-delivery-report/{record}', [WebController::class, 'delivery'])->name('delivered-order');

Route::get('/pos',PosComponent::class)->name('pos')->middleware(CheckposAuthentication::class);

Route::get('/pos/{record}/{edit}',EditPosComponent::class)->name('edit-pos');

Route::get('/check', [WebController::class, 'check']);

Route::get('/print-label', [WebController::class, 'printlabel']);


Route::get('/pos-invoice/{salesid}',[WebController::class, 'pos_invoice'])->name('pos-invoice')
->middleware(CheckposAuthentication::class);

Route::get('/sale-packing-slip/{salesid}',[WebController::class, 'sale_packing_slip'])->name('sale-packing-slip');