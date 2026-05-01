<?php


namespace App\Services;


use App\Models\Department;

class DepartmentService
{
    public function list($uid, $is_page = false){
        $query = Department::query()->where('uid', $uid)->orderBy('sort', 'ASC')->latest();
        if($is_page === true){
            return $query->paginate();
        }
        return $query->get();
    }

    public function store(Array $formData){
        return Department::query()->create($formData);
    }
}
