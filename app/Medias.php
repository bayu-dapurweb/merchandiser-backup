<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Medias extends Model
{
    use SoftDeletes;

    public static function addMediasByUrl($url)
    {
        $content = url_get_content($url);

        $headers = get_headers($url, 1);

        $filename = md5($content);
        $store_res = file_put_contents(__DIR__ . '/../storage/app/uploads/avatar/'.$filename.'.png', $content);

        $media = new Medias;
        $media->url = 'uploads/avatar/'.$filename.'.png';;
        $media->save();

        return $media;
    }

    
}
