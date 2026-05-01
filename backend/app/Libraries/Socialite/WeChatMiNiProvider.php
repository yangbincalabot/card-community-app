<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Libraries\Socialite;

use GuzzleHttp\Client;
/**
 * Class WeChatProvider.
 *
 */
class WeChatMiNiProvider
{
    /**
     * The base url of WeChat API.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.weixin.qq.com/sns';

    /**
     * {@inheritdoc}.
     */
    protected $openId;

    /**
     * {@inheritdoc}.
     */
    protected $scopes = ['snsapi_login'];

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = true;

    /**
     * Return country code instead of country name.
     *
     * @var bool
     */
    protected $withCountryCode = false;


    protected $component;






    /**
     * 微信小程序登录独有的获取用户唯一标识的方法
     * {@inheritdoc}.
     */
    public function getJsCode2Session($code)
    {
        $response = $this->getHttpClient()->get($this->getJsCode2SessionUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query' => $this->getJsCode2SessionFields($code),
        ]);

        return $this->parseJsCode2SessionResponse($response->getBody());
    }

    protected function parseJsCode2SessionResponse($body)
    {
        if (!is_array($body)) {
            $body = json_decode($body, true);
        }

        return $body;
    }

    /**
     * {@inheritdoc}.
     */
    protected function getJsCode2SessionUrl()
    {
        if ($this->component) {
            return $this->baseUrl.'/jscode2session';
        }

        return $this->baseUrl.'/jscode2session';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getJsCode2SessionFields($code)
    {
        $config = config('wechat');
        return array_filter([
            'appid' => $config['mini_program']['default']['app_id'],
            'secret' => $config['mini_program']['default']['secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ]);
    }

    protected static $guzzleOptions = ['http_errors' => false];

    protected function getHttpClient()
    {
        return new Client(self::$guzzleOptions);
    }
}
