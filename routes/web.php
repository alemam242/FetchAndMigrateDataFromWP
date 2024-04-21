<?php

use App\Http\Controllers\FetchJsonDataController;
use App\Http\Controllers\TestMigrateController;
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
    return view('welcome');
});


Route::get("/posts",[FetchJsonDataController::class,"showPosts"])->name("showJsonData");
Route::get("/categories",[FetchJsonDataController::class,"showCategories"])->name("showJsonData");
Route::get("/store-post",[FetchJsonDataController::class,"storePost"])->name("storeJsonData");
Route::get("/store-category",[FetchJsonDataController::class,"storeCategory"])->name("storeJsonData");


// Sample Test
Route::get("/migrate-post",[TestMigrateController::class,"migratePost"]);
Route::get("/migrate-category",[TestMigrateController::class,"migrateCategory"]);