<?php

use Illuminate\Support\Facades\Route;
use Modules\Browser\Pages\BrowserPage;

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

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/admin/browser', [BrowserPage::class, 'index'])->name('browser.post');
    Route::post('/admin/browser/upload', [BrowserPage::class, 'upload'])->name('browser.upload');
});
