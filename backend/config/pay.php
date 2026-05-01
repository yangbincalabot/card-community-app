<?php

return [
    'alipay' => [
        'app_id'         => '',
        'ali_public_key' => '',
        'private_key'    => '',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => 'wxf6b97c1bb59555fe',
        'miniapp_id'      => 'wxf6b97c1bb59555fe',
//        'app_id'      => 'wx06a4882e2b358fe1',
//        'miniapp_id'      => 'wx06a4882e2b358fe1',
        'mch_id'      => '1544679061',
        'key'         => 'FBd3LhpIHaJA3PlufoeDLBzVAqnVzhzz',
        'type' => 'miniapp',
        'cert_client' => base_path('/resources/wechat_pay/apiclient_cert.pem'),
        'cert_key'    => base_path('/resources/wechat_pay/apiclient_key.pem'),
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
//        'mode' => 'dev',
    ],
];
