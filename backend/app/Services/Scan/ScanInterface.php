<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 18:09
 */

namespace App\Services\Scan;

interface ScanInterface
{
    public function resolve($file);
}