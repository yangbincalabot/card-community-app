<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\UpdateCarteRequest;
use App\Http\Resources\baseResource;
use App\Http\Resources\UserBalanceResource;
use App\Http\Resources\UserResource;
use App\Libraries\Creators\CarteCreator;
use App\Models\Association;
use App\Models\Carte;
use App\Models\Configure;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User\UserApplyAgent;
use App\Models\User\UserBalance;
use App\Models\User\WxBizDataCrypt;
use Illuminate\Support\Facades\Auth;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Libraries\Creators\NewUserCreator;

class UserController extends Controller
{
    public function index(Request $request){
        $user_info = $request->user()->load(['carte' => function($query){
            $query->select('id','uid', 'cid', 'name', 'company_name','avatar','phone','position','open');
        }]);
        return new UserResource($this->getUserInfo($user_info));
    }

    public function getUserInfo ($user_info) {
        $carte = $user_info->carte;
        unset($user_info->carte);
        $user_info->name =  $user_info->nickname;
        if (!empty($carte)) {
            $carte->name && $user_info->name = $carte->name;
            $carte->phone && $user_info->phone = $carte->phone;
            $user_info->company_name = $carte->company_name;
            $user_info->position = $carte->position;
            $user_info->open = $carte->open;
            $carte->avatar && $user_info->avatar = $carte->avatar;
        }
        $user_info->name || $user_info->name = $user_info->phone;

        if ($user_info->is_admin === User::IS_ADMIN_TRUE){
            // 平台默认协会
            $user_info->default_aid = Association::query()->where('user_id', 0)->value('id');
        }

        $user_info->is_manage = false;
        $associationInfo = Association::query()->where('user_id', $user_info->id)->first();
        if (!empty($associationInfo)) {
            $user_info->is_manage = true;
        } else if (empty($associationInfo) && $user_info->aid == 0) {
            $user_info->is_manage = true;
        }
        return $user_info;
    }

    public function createCarteInfo(Request $request){
        $user = $request->user();
        $id = (int) $request->get('id');
        $carte = [];
        if($id > 0){
            $carte = Carte::query()->with('industry')->find($id);
        }
        return new UserResource(compact('user', 'carte'));
    }

    // 会员资金
    public function balance(Request $request){
        $user = $request->user();
        // 没有记录，新增默认
        if(!$user->balance){
            UserBalance::addDefaultData($user->id);
        }
        return new UserBalanceResource($user->load('balance'));
    }


    // 推荐汇总
    public function myRecommend(Request $request){
        $user = $request->user();
        $user->lower_count = User\UserRelation::where('from_user_id', $user->id)->count();
        $user_balance = $user->load('balance');
        $user->awarded_bonuses = bcadd($user_balance->balance->reward_money,$user_balance->balance->sales_money,2);
        return new UserBalanceResource($user);
    }

    public function getPhone(Request $request) {
        $config = config('wechat');
        $appid = $config['mini_program']['default']['app_id'];
        $code = $request->get('code');
        $weChatResponse = app('Libraries\Socialite\WeChatMiNiProvider')->getJsCode2Session($code);
        $sessionKey = $weChatResponse['session_key'];
        $encryptedData = $request->get('encryptedData');
        $iv = $request->get('iv');
        $pc = new WxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode == 0) {
            $object = json_decode($data);
            $phoneNumber = $object->phoneNumber;
            User::where('id',Auth::id())->update(['phone'=>$phoneNumber]);

            // 根据手机号更新名片信息
            $user = Auth::user();
            $carte = $user->carte;
            // 如果用户没有名片，就关联
            if(!$carte){
                Carte::query()->where('uid', 0)->where('phone', $phoneNumber)->update(['uid' => $user->id]);
            }

            if($carte){
                // 修改标签信息
                Tag::query()->where([
                    'info_id' => $carte->id,
                    'type' => Tag::TYPE_OTHER_PERSON,
                    'other_uid' => 0
                ])->update(['other_uid' => $user->id]);
            }


            return $data;
        } else {
            abort(403,$errCode);
        }
    }

    // 只获取手机号
    public function getOnlyPhone(Request $request) {
        $config = config('wechat');
        $appid = $config['mini_program']['default']['app_id'];
        $code = $request->get('code');
        $weChatResponse = app('Libraries\Socialite\WeChatMiNiProvider')->getJsCode2Session($code);
        $sessionKey = $weChatResponse['session_key'];
        $encryptedData = $request->get('encryptedData');
        $iv = $request->get('iv');
        $pc = new WxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode == 0) {
            return $data;
        } else {
            abort(403,$errCode);
        }
    }

    // 名片小程序码
    public function getQrcode(Request $request){
        $user_id = $request->get('user_id', 0);
        if(is_numeric($user_id) && $user_id > 0){
            $user = User::find($user_id);
            if(!$user){
                abort(404, '用户不存在');
            }
        }else{
            $user = $request->user();
        }

        $reset = $request->get('reset'); // 重新生成小程序码
        if(empty($user->qrcode) || $reset === true){
            $config = config('wechat.mini_program.default');
            $app = Factory::miniProgram($config);
            $page = ''; // 未上线的小程序不能使用page参数
            if(intval(Configure::getValue('IS_AUDIT')) === Configure::IS_AUDIT_NO){
                // 名片码地址，主要处理相关业务
                $page = 'pages/my/cardCode/cardCodeHandle/index';
            }

            $scence = sprintf('user_id@%d', $user->id); // 参数
            $response = $app->app_code->getUnlimit($scence, [
                'page' => $page,
                'width' => 170
            ]);
            if($response instanceof \EasyWeChat\Kernel\Http\StreamResponse){
                $filename = time() . mt_rand(100, 999) . '.png';
                $target_path = 'qrcode/' . $filename;
                $result = Storage::disk('public')->put($target_path, $response->getBodyContents());
                if($result === false){
                    abort(503, '生成小程序码失败');
                }
                $user->qrcode = $target_path;
                $user->save();
            }else{
                abort(503, '获取小程序码失败');
            }
        }
        // 已做url处理
        return $user->qrcode;
    }


    // 获取当前用户的用户列表
    public function getUserList(Request $request) {
        $user = $request->user();
        $userAuthModel = new User\UserAuth();
        $userAuth = $userAuthModel::query()->where('user_id', $user['id'])->first();
        $userArr = $userAuthModel::query()->where('identifier', $userAuth['identifier'])->pluck('user_id');
        $list = User::query()->with(['carte'])->whereIn('id', $userArr)->orderByRaw("id = {$user['id']} desc")->get();
        return new UserResource(compact('user', 'list'));
    }

    // 切换用户
    public function changeUser(Request $request) {
        $id = $request->get('id');
        $user = User::query()->find($id);
        if (!empty($user)) {
            DB::beginTransaction();
            $userAuthModel = new User\UserAuth();
            $currentAuthInfo = $userAuthModel::query()->where('user_id', $id)->first();
            if (empty($currentAuthInfo)) {
                DB::rollBack();
                abort(403, '切换失败，请稍后重试');
            }
            $currentAuthInfo->selected = $userAuthModel::SELECTED;
            $currentAuthInfo->save();
            $userAuthModel::query()->where('identifier', $currentAuthInfo->identifier)
                ->where('id', '<>', $currentAuthInfo->id)->update(['selected' => $userAuthModel::NOT_SELECTED]);
            DB::commit();
            $token = $user->createToken('DuLeErTongChe Password Grant Client')->accessToken;
            return new UserResource(compact('user', 'token'));
        }
        abort(403, '切换失败，请稍后重试');
    }

    // 添加新用户
    public function addUserCarte(UpdateCarteRequest $request, CarteCreator $creator) {
        DB::beginTransaction();
        $oldUser = $request->user();
        $data = $request->all();
        $newUserData = NewUserCreator::addUser($oldUser, $data);
        $newUser = User::query()->create($newUserData);
        if (empty($newUser)) {
            DB::rollBack();
            abort(403, '添加出错,请稍后重试');
        }
        $oldAuthInfo = User\UserAuth::query()->where('user_id', $oldUser['id'])->first();
        $newAuthData = NewUserCreator::addAuth($oldAuthInfo, $newUser->id);
        $newAuthInfo = User\UserAuth::query()->create($newAuthData);
        if (empty($newAuthInfo)) {
            DB::rollBack();
            abort(403, '添加出错,请稍后重试');
        }
        $cate = NewUserCreator::updateOrCreate($data, $newUser->id);
        if (empty($cate)) {
            DB::rollBack();
            abort(403, '添加出错,请稍后重试');
        }
        DB::commit();
        return new UserResource(collect());
    }

    // 浏览协会足迹
    public function footPrint(Request  $request){
        $aid = $request->get('aid');
        if (!$aid) {
            abort(403, '参数有误');
        }
        $association = Association::query()->with('company')->findOrFail($aid);
        $data = [
            'user_id' => $request->user()->id,
            'aid' => $association->id,
            'company_id' => $association->company->id
        ];
        User\FootPrint::query()->firstOrCreate($data, $data);
    }

    // 足迹列表
    public function footPrintList(Request  $request){
        $list = User\FootPrint::query()->where('user_id', $request->user()->id)->latest()->with(['association', 'company' => function($query){
            $query->with('industry');
        }])->paginate();
        return new baseResource($list);
    }

}
