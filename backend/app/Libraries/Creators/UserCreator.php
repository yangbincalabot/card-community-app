<?php
namespace Libraries\Creators;


use App\Models\User\UserAuth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use GuzzleHttp\Client;

class UserCreator
{
    public function create($data){
        DB::beginTransaction();
        $userData['nickname'] =  $data['nickname'] ?? '';
        $userData['avatar'] = $data['avatar'] ?:'avatars/default.jpg';
        $user = User::create($userData);
        $user_id = $user->id;
        $userAuthData['user_id'] = $user_id;
        $userAuthData['identity_type'] = $data['identity_type'];
        $userAuthData['identifier'] = $data['identifier'];
        $userAuthData['expires_in'] = 0;
        $userAuthData['other_param'] = $data['other_param'];
        UserAuth::create($userAuthData);
        if ($data['avatar']) {
            $this->changeAvatar($user_id, $data['avatar']);
        }
        DB::commit();
        return $user;
    }

    public function createUser($data){
        DB::beginTransaction();
        $userData['nickname'] =  $data['nickname'] ?? '';
        $userData['avatar'] = $data['avatar'] ?: 'avatars/default.jpg';
        $user = User::create($userData);
        $user_id = $user->id;
        $userAuthData['user_id'] = $user_id;
        $userAuthData['identity_type'] = $data['identity_type'];
        $userAuthData['identifier'] = $data['identifier'];
        $userAuthData['credential'] = $data['credential'];
        $userAuthData['expires_in'] = 0;
        $userAuthData['other_param'] = $data['other_param'] ?? '';
        UserAuth::create($userAuthData);
        if ($data['avatar']) {
            $this->changeAvatar($user_id, $data['avatar']);
        }
        DB::commit();
        return $user;
    }


    public function changeAvatar($user_id, $avatar, $nickName='')
    {
        //Download Image
        $guzzle = new Client();
        $response = $guzzle->get($avatar);
        //Get ext
        $content_type = explode('/', $response->getHeader('Content-Type')[0]);
        $ext = array_pop($content_type);

        $avatar_name = $user_id . '_' . time() . '.' . $ext;
        $save_path = 'public/avatars/' . $avatar_name;

        //Save File
        $content = $response->getBody()->getContents();
//        file_put_contents($save_path, $content);
        Storage::put($save_path, $content);
        $upData['avatar'] = 'avatars/' . $avatar_name;
        $nickName && $upData['nickname'] = $nickName;
        User::where('id', $user_id)->update($upData);
    }
}