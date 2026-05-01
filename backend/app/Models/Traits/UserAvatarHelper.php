<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use GuzzleHttp\Client;

trait UserAvatarHelper
{
    public function cacheAvatar()
    {
        //Download Image
        $guzzle = new Client();
        $response = $guzzle->get($this->avatar);
        //Get ext
        $content_type = explode('/', $response->getHeader('Content-Type')[0]);
        $ext = array_pop($content_type);

        $avatar_name = $this->id . '_' . time() . '.' . $ext;
        $save_path = 'public/avatars/' . $avatar_name;

        //Save File
        $content = $response->getBody()->getContents();
//        file_put_contents($save_path, $content);
        Storage::put($save_path, $content);
        //Delete old file
        if ($this->avatar) {
            @unlink(storage_path('public/avatars/' . $this->avatar));
        }
        //Save to database
        $this->avatar = 'avatars/' . $avatar_name;
        $this->save();
    }
}
