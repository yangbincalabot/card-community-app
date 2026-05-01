<?php

namespace App\Models;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Industry extends Model
{
    use SoftDeletes, ModelTree, AdminBuilder;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setParentColumn('parent_id');
        $this->setOrderColumn('sort');
        $this->setTitleColumn('name');
    }

    protected $fillable = ['name', 'sort', 'parent_id'];
    protected $hidden = ['deleted_at'];



    public static function getIndustries(){
        return self::query()->where('parent_id', 0)->with(['children' => function($query){
            $query->setOrderBy();
        }])->setOrderBy()->get();
    }

    // 排序方式
    public function scopeSetOrderBy($query){
        return $query->orderBy('sort', 'asc')->orderBy('id', 'desc');
    }

}
