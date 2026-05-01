<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\MembershipRequest;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MembershipController extends Controller
{
    protected $service;
    public function __construct(MembershipService  $service)
    {
        $this->service = $service;
    }

    public function post(MembershipRequest  $request) {
        $aid = $request->get('aid');
        $carte_id = $request->get('carte_id');
        $this->service->post($aid, $carte_id, $request->user());
    }
}
