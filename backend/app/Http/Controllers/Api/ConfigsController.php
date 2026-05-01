<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ConfigsResource;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConfigsController extends Controller
{
    public function getActivityGroup(Request $request) {
        $group = $request->get('group');
        $configModel = new SystemConfig();
        $response = $configModel->getActivityGroup($group);
        return new ConfigsResource(collect($response));
    }

    public function getActivityText() {
        $configModel = new SystemConfig();
        $response = $configModel->getActivityText();
        return $response;
    }
}
