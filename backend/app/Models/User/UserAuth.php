<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserAuth extends Model
{
    protected $table = 'user_auth';     // 数据表名
    public static $snakeAttributes = false;   // 设置关联模型在打印输出的时候是否自动转为蛇型命名
    protected $guarded = ['id'];        // 过滤的字段

    // 用户注册方式
    const IDENTITY_TYPE_PASSWORD = 1;   // 账号密码注册
    const IDENTITY_TYPE_WX = 2;         // 微信登录
    const IDENTITY_TYPE_WX_MINI = 3;    // 微信小程序登录
    const IDENTITY_TYPE_MOBILE = 4;    // 手机号码登录

    // 是否选中
    const SELECTED = 1;
    const NOT_SELECTED = 0;

    /**
     * Find the user identified by the given $identifier.
     *
     * @param $identifier email|phone
     *
     * @return mixed
     */
    public function findForPassport($identifier)
    {
        return self::orWhere('email', $identifier)->orWhere('username', $identifier)->first();
    }
}
