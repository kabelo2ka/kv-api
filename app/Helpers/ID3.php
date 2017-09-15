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

    public function analyze()
    {
        return $this->getID3->analyze($this->filename);
    }

    public function getInfo()
    {
        return $this->analyze();
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
            // Send file
            header("Content-Type: " . $mimetype);

            if (isset($getID3->info['id3v2']['APIC'][0]['image_bytes'])) {
                header("Content-Length: " . $getID3->info['id3v2']['APIC'][0]['image_bytes']);
            }

            return $cover;
        }
        return false;
    }

    public function getCoverPicture()
    {
        return $this->getCoverImage();
    }
}