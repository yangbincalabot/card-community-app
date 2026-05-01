<?php
return [
    'default' => env('DEFAULT_SCAN', 'showapi_scan') , // 默认网关
    'gateways' => [
        'showapi_scan' => [
            'app_id' => env('SHOWAPI_APP_ID'),
            'app_access' => env('SHOWAPI_APP_ACCESS'),
            'api_url' => 'http://route.showapi.com/1334-1',
        ],
        'aliapi_scan' => [
            'app_code' => env('ALI_APP_CODE'),
            'api_url' => 'http://dm-57.data.aliyun.com/rest/160601/ocr/ocr_business_card.json'
        ]
    ]
];