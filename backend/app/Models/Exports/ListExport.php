<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27 0027
 * Time: 16:04
 */
namespace App\Models\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromArray;

class ListExport implements FromArray
{
    protected $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function array(): array
    {
        return $this->list;
    }
}