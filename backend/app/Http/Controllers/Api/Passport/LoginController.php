<?php

namespace App\Http\Controllers\Api\Passport;

use App\Models\User;
use App\Models\User\UserAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use EasyWeChat\Factory;

class LoginController extends Controller
{
    //
    public function login(Request $request){
        $loginType = $request->get('type');
        switch ($loginType){
            case 'wx_mini':
                return $this->miniProgramLogin($request);
                break;
            default:
                return $this->miniProgramLogin($request);
                break;
        }
    }

    public function miniProgramLogin(Request $request){
        $code = $request->get('code');
        $postUserInfoData = $request->get('userInfo');
        $config = config('wechat.mini_program.default');
        $app = Factory::miniProgram($config);
        $weChatResponse = $app->auth->session($code);
        if(isset($weChatResponse['openid'])){
            // 根据返回的 openid 或 unionid 检查数据库中是否已经保存，如果有，就登录，没有则新增用户
            $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
            $userAuthWhere['identifier'] = $weChatResponse['openid'];
            $userAuthWhere['selected'] = UserAuth::SELECTED;
            $userAuthInfo = UserAuth::where($userAuthWhere)->first();
            if($userAuthInfo){
                $this->checkNickname($userAuthInfo, $postUserInfoData['avatarUrl'], $postUserInfoData['nickName']);
                return $this->loginUser($userAuthInfo->user_id);
            }
            // 新增用户并绑定该微信 openid 和 unionid
            $registerUserData['nickname'] = $postUserInfoData['nickName'];
            $registerUserData['avatar'] = $postUserInfoData['avatarUrl'];
            $registerUserData['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
            $registerUserData['identifier'] = $weChatResponse['openid'];
            $registerUserData['other_param'] = '';
            if(isset($weChatResponse['unionid'])){
                $registerUserData['other_param'] = $weChatResponse['unionid'];
            }
            return $this->userNotFound(UserAuth::IDENTITY_TYPE_WX_MINI,$registerUserData);
        }
    }

    public function checkNickname($userInfo, $avatarUrl, $nickName) {
        if (empty($userInfo->nickname) && $avatarUrl && $nickName) {
            app('Libraries\Creators\UserCreator')->changeAvatar($userInfo->user_id, $avatarUrl, $nickName);
        }
    }

    public function codeGetInfo(Request $request) {
        $code = $request->get('code');
        $config = config('wechat.mini_program.default');
        $app = Factory::miniProgram($config);
        $weChatResponse = $app->auth->session($code);
        if(isset($weChatResponse['openid'])){
            // 根据返回的 openid 或 unionid 检查数据库中是否已经保存，如果有，就登录，没有则新增用户
            $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
            $userAuthWhere['identifier'] = $weChatResponse['openid'];
            $userAuthWhere['selected'] = UserAuth::SELECTED;
            $userAuthInfo = UserAuth::where($userAuthWhere)->first();
            if($userAuthInfo){
                return $this->loginUser($userAuthInfo->user_id);
            }
            // 新增用户并绑定该微信 openid 和 unionid
            $registerUserData['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
            $registerUserData['identifier'] = $weChatResponse['openid'];
            $registerUserData['other_param'] = '';
            if(isset($weChatResponse['unionid'])){
                $registerUserData['other_param'] = $weChatResponse['unionid'];
            }
            return $this->userNotFound(UserAuth::IDENTITY_TYPE_WX_MINI,$registerUserData);
        }
    }

    private function loginUser($user_id)
    {
        return $this->userFound($user_id);
    }

    public function userFound($user_id){
        $user = User::find($user_id);
        $token = $user->createToken('DuLeErTongChe Password Grant Client')->accessToken;
        return response()->json([
            'token' => $token,
            'user_info' => $user,
        ]);
    }

    public function userNotFound($driver,$registerUserData){
        $userData = [];
        switch ($driver){
            case UserAuth::IDENTITY_TYPE_WX_MINI:
                $userData['nickname'] = $registerUserData['nickname'] ?? '';
                $userData['avatar'] = $registerUserData['avatar'] ?? '';
                $userData['identity_type'] = $registerUserData['identity_type'];
                $userData['identifier'] = $registerUserData['identifier'];
                $userData['expires_in'] = 0;
                $userData['other_param'] = $registerUserData['other_param'];
                break;
        }

        $user = app('Libraries\Creators\UserCreator')->create($userData);
        return $this->loginUser($user->id);
    }

}
