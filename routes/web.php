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

Route::get('server', function () {
    // dd($_SERVER);
});

Route::get('php.info', function () {
    return phpinfo();
});

Route::get('test', function () {
    $filename = public_path('uploads/songs/Wild-Ones.mp3');
    $getID3 = new getID3;
    $ThisFileInfo = $getID3->analyze($filename);

    // Optional: copies data from all subarrays of [tags] into [comments] so
    // metadata is all available in one location for all tag formats
    // metainformation is always available under [tags] even if this is not called
    getid3_lib::CopyTagsToComments($ThisFileInfo);

    $cover = null;
    if (isset($getID3->info['id3v2']['APIC'][0]['data'])) {
        $cover = $getID3->info['id3v2']['APIC'][0]['data'];
    } elseif (isset($getID3->info['id3v2']['PIC'][0]['data'])) {
        $cover = $getID3->info['id3v2']['PIC'][0]['data'];
    }

    if (isset($getID3->info['id3v2']['APIC'][0]['image_mime'])) {
        $mimetype = $getID3->info['id3v2']['APIC'][0]['image_mime'];
    } else {
        $mimetype = 'image/jpeg'; // or null; depends on your needs
    }

    if ($cover !== null) {
        $cover='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
        echo '<img src="'. @$cover.'"/>'; exit();

        // Send file
        header("Content-Type: " . $mimetype);

        if (isset($getID3->info['id3v2']['APIC'][0]['image_bytes'])) {
            header("Content-Length: " . $getID3->info['id3v2']['APIC'][0]['image_bytes']);
        }

        echo($cover);
    }

    });


Route::get('tes2', function () {

    $filename = public_path('uploads/songs/Wild-Ones.mp3');
    $fileInfo = new \App\Helpers\ID3($filename);
    return $fileInfo->getCoverImage();

});

    Route::get('/register/confirm', 'Auth\RegisterConfirmationController@index')->name('register.confirm-email');

    Route::get('{all?}', function () {
        ob_start();
        require(public_path('app/index.html'));
        return ob_get_clean();
    })->where('all', '.+');


