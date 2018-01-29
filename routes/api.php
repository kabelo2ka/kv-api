<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::group(['prefix' => 'v1'], function () {

    Route::get('policies/terms', 'PolicyController@getTermsOfUse');
    Route::get('policies/privacy', 'PolicyController@getPrivacyPolicy');

    Route::post('/user', [
        'uses' => 'UserController@signUp'
    ]);
    Route::get('/users/{user}', function (App\User $user) {
        return $user;
    });
    Route::patch('/users', [
        'uses' => 'UserController@update'
    ]);
    Route::post('/user/authenticate', [
        'uses' => 'UserController@signIn'
    ]);
    Route::get('/user/authorize', [
        'uses' => 'UserController@checkToken',
        'middleware' => 'auth.jwt'
    ]);
    Route::get('/users', function () {
        return \App\User::whereUsername('kabelo')->get();
    });
    // Resend confirmation email to user
    Route::get('/register/confirm/send', 'Auth\RegisterConfirmationController@resend')
        ->middleware('auth.jwt');

    Route::get('/user/songs', [
        'uses' => 'SongController@authSongs',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/user/albums', [
        'uses' => 'AlbumController@authAlbums',
        'middleware' => 'auth.jwt'
    ]);

    // Search Songs
    Route::get('/songs/search', 'SongController@search');

    Route::get('/songs', 'SongController@index');
    Route::post('songs/upload', 'SongController@uploadFile')
        ->middleware('auth.jwt')
        ->middleware('must-be-confirmed');
    Route::post('/songs', 'SongController@store')
        ->middleware('auth.jwt');
    Route::get('/songs/{slug}', 'SongController@show');
    Route::delete('/songs/{slug}', 'SongController@destroy')
        ->middleware('auth.jwt');
    Route::patch('/songs/{id}', 'SongController@update')
        ->middleware('auth.jwt');

    Route::get('songs/{id}/stream', 'SongController@stream');
    Route::get('songs/{id}/download', 'SongController@download');
    Route::post('song/like', [
        'as' => 'song.like',
        'uses' => 'LikeController@likeSong',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/songs/{song}/comments', 'SongCommentsController@index');
    Route::post('/songs/{song}/comments', [
        'uses' => 'SongCommentsController@store',
        'middleware' => 'auth.jwt',
    ]);

    Route::post('/songs/plays', 'SongController@storePlay');
    Route::get('/songs/{song}/plays', 'SongController@storePlay');
    Route::get('/trending-songs', 'SongController@trending');

    Route::post('comment/like', [
        'as' => 'comment.like',
        'uses' => 'LikeController@likeComment',
        'middleware' => 'auth.jwt',
    ]);


    Route::get('/genres', 'GenreController@index');


    Route::get('/artists', [
        'uses' => 'ArtistController@index'
    ]);
    Route::get('/artists/{id}', [
        'uses' => 'ArtistController@show'
    ]);

    Route::get('/albums', [
        'uses' => 'AlbumController@index'
    ]);
    Route::get('/albums/{id}',  'AlbumController@show');
    Route::post('/albums', 'AlbumController@store')
        ->middleware('auth.jwt');
    Route::patch('/albums/{slug}', 'AlbumController@update')
        ->middleware('auth.jwt');
    Route::delete('/albums/{slug}', 'AlbumController@destroy')
        ->middleware('auth.jwt');

    Route::get('/notifications', [
        'uses' => 'NotificationsController@index',
        'middleware' => 'auth.jwt',
    ]);
    Route::get('/notifications/{id}/read', [
        'uses' => 'NotificationsController@markAsRead',
        'middleware' => 'auth.jwt',
    ]);


});


ApiRoute::group(
    [
        'middleware' => ['api'],
        'namespace' => 'App\Http\Controllers'],
    function () {
        //ApiRoute::resource('users', 'UserController');
        //ApiRoute::resource('songs', 'SongController');
        //ApiRoute::resource('artists', 'ArtistController');
        //ApiRoute::resource('genres', 'GenreController');
        ApiRoute::resource('albums', 'AlbumController');
    }
);