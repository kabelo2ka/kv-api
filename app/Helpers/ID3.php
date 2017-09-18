<?php
/**
 * Author Kabelo
 * Date: 2017/09/15
 * Time: 6:32 PM
 */

namespace app\Helpers;


use getID3;
use getid3_lib;

class ID3
{

    private $filename;
    private $getID3;
    protected $info;

    /**
     * ID3 constructor.
     * @param $filename
     * @param bool $analyze Set to false if you want to write to file
     */
    public function __construct($filename, $analyze = true)
    {
        $this->filename = $filename;
        $this->getID3 = new getID3;
        // Optional: copies data from all subarrays of [tags] into [comments] so
        // metadata is all available in one location for all tag formats
        // meta information is always available under [tags] even if this is not called
        if($analyze){
            $fileInfo = $this->analyze();
            getid3_lib::CopyTagsToComments($fileInfo);
        }
    }

    public function analyze($filename = null)
    {
        if($filename !== null) { $this->filename = $filename; };
        $this->info = $this->getID3->analyze($this->filename);
        return $this->info;
    }

    public function getInfo($filename = null)
    {
        return $this->analyze($filename);
    }


    public function getCoverImage()
    {
        $getID3 = $this->getID3;

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
            $cover='data:'.$this->info['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($this->info['comments']['picture'][0]['data']);
            echo '<img src="'. @$cover.'"/>'; exit();

            return @$cover;
        }
        return false;
    }

    public function getCoverPicture()
    {
        return $this->getCoverImage();
    }

}