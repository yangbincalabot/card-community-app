<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ScanFiles extends Model
{
    protected $guarded = ['id'];

    public static function addScanFile($uid, $filePath){
        if(file_exists($filePath)){
            $savePath = 'scan/' . time() . mt_rand(1000, 9999) . '.png';
            Storage::disk('public')->put($savePath, file_get_contents($filePath));
            self::query()->create([
                'uid' => $uid,
                'file' => $savePath
            ]);
        }
    }
}
