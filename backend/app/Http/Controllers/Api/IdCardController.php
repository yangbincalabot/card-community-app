<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\IdCardRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Medz\IdentityCard\China\Identity;

class IdCardController extends Controller
{
    public function checkIdCard (Request $request) {
        $id_card = $request->get('id_card');
        $peopleIdentity = new Identity($id_card);
        $peopleRegion = $peopleIdentity->legal();
        $response = ['status' => $peopleRegion];
        return new IdCardRequest($response);
    }
}
