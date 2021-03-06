<?php

use Illuminate\Http\Request;

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


Route::group(['prefix' => 'v1'], function(){

    Route::post('/user', [
        'uses' => 'UserController@signUp'
    ]);
    Route::get('/users/{user}', function (App\User $user) {
        return $user;
    });
    Route::post('/user/authenticate', [
        'uses' => 'UserController@signIn'
    ]);
    Route::get('/user/authorize', [
        'uses' => 'UserController@checkToken',
        'middleware' => 'auth.jwt'
    ]);
    Route::get('/users', function(){
        return \App\User::whereUsername('kabelo')->get();
    });

    Route::get('/user/albums', [
        'uses' =>  'AlbumController@authAlbums',
        'middleware' => 'auth.jwt'
    ]);



    Route::get('/songs', 'SongController@index');
    Route::post('/song', [
        'uses' => 'SongController@create',
        'middleware' => 'auth.jwt'
    ]);
    Route::get('/songs/{id}', 'SongController@show');
    Route::get('songs/{id}/stream', 'SongController@stream');
    Route::post('song/like', [
        'as' => 'song.like',
        'uses' => 'LikeController@likeSong',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/songs/{song}/comments', 'SongCommentsController@index');
    Route::post('/songs/{song}/comments',  [
        'uses' => 'SongCommentsController@store',
        'middleware' => 'auth.jwt',
    ]);

    Route::post('/songs/plays', 'SongController@storePlay');
    Route::get('/songs/{id}', 'SongController@show');
    Route::get('/songs/{song}/plays', 'SongController@storePlay');

    Route::post('comment/like', [
        'as' => 'comment.like',
        'uses' => 'LikeController@likeComment',
        'middleware' => 'auth.jwt',
        ]);


    Route::get('/genres', 'GenreController@index');


    Route::get('/artists', [
        'uses' =>  'ArtistController@index'
    ]);
    Route::get('/artists/{id}', [
        'uses' =>  'ArtistController@show'
    ]);

    Route::get('/albums', [
        'uses' =>  'AlbumController@index'
    ]);
    Route::get('/albums/{id}', [
        'uses' =>  'AlbumController@show'
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
        ApiRoute::resource('genres', 'GenreController');
        ApiRoute::resource('albums', 'AlbumController');
    }
);