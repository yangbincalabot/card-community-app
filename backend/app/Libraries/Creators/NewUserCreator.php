<?php
namespace Libraries\Creators;


use App\Models\Area;
use App\Models\Carte;
use App\Models\CompanyBind;
use App\Models\Tag;
use App\Models\User\UserAuth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class NewUserCreator
{
    public static function addUser($oldUser, $data){
       $newData = array();
       $newData['nickname'] = $data['name'] ?$data['name']: $oldUser->nickname;
       $newData['phone'] = $data['phone'] ?$data['phone']: $oldUser->phone;
       $newData['email'] = $data['email'] ?$data['email']: $oldUser->email;
       $newData['avatar'] = $data['avatar'] ?? $oldUser->avatar;
       $newData['type'] = User::USER_TYPE_ONE;
       $newData['cash_password'] = $oldUser->cash_password;
       return $newData;
    }

    public static function addAuth($oldAuthInfo, $newId){
        $newData = array();
        $newData['user_id'] = $newId;
        $newData['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
        $newData['identifier'] = $oldAuthInfo['identifier'];
        $newData['credential'] = $oldAuthInfo['credential'];
        $newData['expires_in'] = $oldAuthInfo['expires_in'];
        $newData['selected'] = UserAuth::NOT_SELECTED;
        return $newData;
    }


    public static function updateOrCreate($resData, $newId){
        $user = User::query()->find($newId);
        $data = [];
        $data['uid'] = $user->id;
        //$data['cid'] = $request->get('cid'); // 绑定的公司需要特殊处理
        $data['name'] = $resData['name'];
        $data['company_name'] = $resData['company_name'];
        $data['avatar'] = $resData['avatar'] ?? getletterAvatar($resData['name']);
        $data['phone'] = $resData['phone'];
        $data['wechat'] = $resData['wechat'];
        $data['email'] = $resData['email'];
        $data['introduction'] = $resData['introduction'];
        $data['industry_id'] = $resData['industry_id'];
        $data['position'] = $resData['position'];
        $data['open'] = $resData['open'];
        $data['images'] = $resData['images'];
        $data['longitude'] = $resData['longitude'];
        $data['latitude'] = $resData['latitude'];
        $data['address_title'] = $resData['address_title'];
        $data['address_name'] = $resData['address_name'];
        $data['card_color'] = $resData['card_color'] ?? 1;
        //  $data['type'] = Tag::TYPE_OWN;
        // 检测输入文本是否合法
        $secMsg = $resData['name'] . $resData['wechat'] . $resData['introduction'] . $resData['position'] . $resData['address_title'] . $resData['address_name'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容有不合法的词汇，请修改后重新提交');
        }
        $addressInfo = Area::getAddressInfo($data['address_title']);
        $data['province'] = $addressInfo['province'];
        $data['city'] = $addressInfo['city'];
        if(empty($data['longitude']) && !empty($addressInfo['longitude'])){
            $data['longitude'] = $addressInfo['longitude'];
        }

        if(empty($data['latitude']) && !empty($addressInfo['latitude'])){
            $data['latitude'] = $addressInfo['latitude'];
        }
        $carte = Carte::query()->updateOrCreate(['uid' => $user->id], $data);


        $cid =  $resData['cid'];
        if ($cid) {
            $bindStatus = CompanyBind::NOT_REVIEWED_STATUS;
            CompanyBind::addCompanyBind($user->id, $cid, $carte->id, $bindStatus);
        }

        // 标签处理
        $inputTags = array_filter($resData['tags']); // 表单提现的标签，数组格式

        // 直接添加
        $tagsData = self::getCreateData($inputTags);
        if(!empty($tagsData) && is_array($tagsData)){
            $user->tags()->createMany($tagsData);
        }
        return $carte;

    }


    public static function getCreateData(Array $tags){
        return collect($tags)->map(function($tag){
            return ['title' => $tag, 'status' => Tag::TYPE_OWN];
        })->toArray();
    }


}
