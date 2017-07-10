<?php

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

Route::get('test', function () {
    //return \App\Models\Song::with(['artists', 'genre'])->get();
    //return \App\Models\Song::whereId(21)->with('comments.author')->orderBy('created_at', 'desc')->get();
    return \App\Models\Comment::first()->author()->first();
});

Route::get('php.info', function () {
    return phpinfo();
});

Route::get('{all?}', function () {
    ob_start();
    require(public_path('app/index.html'));
    return ob_get_clean();
})->where('all', '.+');


