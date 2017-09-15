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
    $comment = \App\Models\Comment::first();
    $user = \App\User::first();
    $user->notify(new \App\Notifications\UserCommented($comment));
    return 'Sent';
});

Route::get('php.info', function () {
    return phpinfo();
});

Route::get('/register/confirm', 'Auth\RegisterConfirmationController@index')->name('register.confirm-email');

Route::get('{all?}', function () {
    ob_start();
    require(public_path('app/index.html'));
    return ob_get_clean();
})->where('all', '.+');


