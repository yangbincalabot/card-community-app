<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/18
 * Time: 18:43
 */

namespace App\Libraries\Creators;

use App\Models\Area;
use App\Models\Carte;
use App\Models\CompanyBind;
use App\Models\Tag;
use App\Models\User\Attention;
use App\Models\User\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarteCreator
{
    public function updateOrCreate(Request $request)
    {
        DB::beginTransaction();
        $user = $request->user();

        $data = [];
        $data['uid'] = $request->user()->id;
        //$data['cid'] = $request->get('cid'); // 绑定的公司需要特殊处理
        $data['name'] = $request->get('name');
        $data['company_name'] = $request->get('company_name');
        $data['avatar'] = $request->get('avatar') ?? getletterAvatar($request->get('name'));
        $data['phone'] = $request->get('phone');
        $data['wechat'] = $request->get('wechat');
        $data['email'] = $request->get('email');
        $data['introduction'] = $request->get('introduction');
        $data['industry_id'] = $request->get('industry_id');
        $data['position'] = $request->get('position');
        $data['open'] = $request->get('open');
        $data['images'] = $request->get('images');
        $data['longitude'] = $request->get('longitude');
        $data['latitude'] = $request->get('latitude');
        $data['address_title'] = $request->get('address_title');
        $data['address_name'] = $request->get('address_name');
        $data['card_color'] = $request->get('card_color', 1);
        //  $data['type'] = Tag::TYPE_OWN;
        // 检测输入文本是否合法
        $secMsg = $data['name'] .$data['company_name']. $data['wechat'] . $data['introduction'] . $data['position'] . $data['address_title'] . $data['address_name'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容有不合法的词汇，请修改后重新提交');
        }
        try {
            $addressInfo = Area::getAddressInfo($data['address_title']);
            $data['province'] = $addressInfo['province'];
            $data['city'] = $addressInfo['city'];

            if (empty($data['longitude']) && !empty($addressInfo['longitude'])) {
                $data['longitude'] = $addressInfo['longitude'];
            }

            if (empty($data['latitude']) && !empty($addressInfo['latitude'])) {
                $data['latitude'] = $addressInfo['latitude'];
            }
            $carte = Carte::query()->updateOrCreate(['uid' => $user->id], $data);


            $cid = $request->get('cid');
            $isRebinding = false; // 是否重新绑定新的公司
            if ($carte->cid != $cid) {
                $isRebinding = true;
            }
            if (!empty($cid) && $isRebinding) {
                // 绑定自己的公司，直接通过
                if ($user->companyCardStatus && $user->companyCard->id == $cid) {
                    $carte->cid = $cid;
                    $carte->save();
                    $bindStatus = CompanyBind::AUDIT_SUCCESS_STATUS;
                } else {
                    $bindStatus = CompanyBind::NOT_REVIEWED_STATUS;
                }

                CompanyBind::addCompanyBind($user->id, $cid, $carte->id, $bindStatus);
            }


            // 检查首字母
            $fromUserCollection = Attention::where('from_id', $carte->id)->where('status', Attention::ATTENTION_STATUS_ONE)->first();
            $initial = getInitial($request->get('name')); // 真实姓名首字母
            if ($fromUserCollection && $fromUserCollection->initial !== $initial) {
                Attention::changeInitial($carte->id, $initial);
            }


            // 标签处理
            $userTags = $user->getTagsNames(); // 用户数据库里的标签
            $inputTags = array_filter($request->get('tags')); // 表单提现的标签，数组格式
            $targetDeleteIds = []; // 需要设置删除状态的标签id
            $tagsData = [];

            if ($userTags) {
                $intersections = array_intersect($userTags, $inputTags);
                if (empty($intersections)) {
                    // 交集为空时，将以前的tag改为删除状态，将用户提交的做为新数据
                    $targetDeleteIds = array_keys($userTags);
                    $tagsData = $this->getCreateData($inputTags);
                } else {
                    // 不为空，检查哪些数据要修改删除状态
                    $target_delete = array_diff($userTags, $inputTags);
                    $target_create = array_diff($inputTags, $userTags);

                    if (!empty($target_delete) && is_array($target_delete)) {
                        $targetDeleteIds = array_keys($target_delete);
                    }
                    if (!empty($target_create)) {
                        $tagsData = $this->getCreateData($target_create);
                    }
                }

            } else {
                // 直接添加
                $tagsData = $this->getCreateData($inputTags);
            }
            if (!empty($tagsData) && is_array($tagsData)) {
                $user->tags()->createMany($tagsData);
            }
            if (!empty($targetDeleteIds) && is_array($targetDeleteIds)) {
                Tag::where('uid', $user->id)->whereIn('id', $targetDeleteIds)->update(['status' => Tag::STATUS_DELETE]);
            }
            DB::commit();
            return $carte;

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            DB::rollback();
            abort(500, '操作出错');
        }
    }


    // 手动创建/更新名片
    public function updateOrCreateOther(Request $request)
    {
        DB::beginTransaction();
        $user = $request->user();
        try {
            $data = [];
            $data['uid'] = 0;
//            $data['cid'] = $request->get('cid',0); // 绑定的公司需要特殊处理
            $data['name'] = $request->get('name');
            $data['company_name'] = $request->get('company_name');
            $data['avatar'] = $request->get('avatar') ?? getletterAvatar($request->get('name'));
            $data['phone'] = $request->get('phone');
            $data['wechat'] = $request->get('wechat');
            $data['email'] = $request->get('email');
            $data['introduction'] = $request->get('introduction');
            $data['industry_id'] = $request->get('industry_id');
            $data['position'] = $request->get('position');
            $data['open'] = $request->get('open');
            $data['images'] = $request->get('images');
            $data['longitude'] = $request->get('longitude');
            $data['latitude'] = $request->get('latitude');
            $data['address_title'] = $request->get('address_title');
            $data['address_name'] = $request->get('address_name');
            $data['card_color'] = $request->get('card_color', 1);
            // $data['type'] = Tag::TYPE_OWN;

            $addressInfo = Area::getAddressInfo($data['address_title']);
            $data['province'] = $addressInfo['province'];
            $data['city'] = $addressInfo['city'];
            if (empty($data['longitude']) && !empty($addressInfo['longitude'])) {
                $data['longitude'] = $addressInfo['longitude'];
            }

            if (empty($data['latitude']) && !empty($addressInfo['latitude'])) {
                $data['latitude'] = $addressInfo['latitude'];
            }
            if ($request->get('id') > 0) {
                $condition = ['id' => $request->get('id'), 'uid' => 0];
                Carte::query()->where($condition)->update($data);
                $carte = Carte::query()->where($condition)->first();
            } else {
                $carte = Carte::query()->updateOrCreate(['uid' => 0, 'phone' => $data['phone']], $data);
            }
            // 检测输入文本是否合法
            $secMsg = $data['name'] .$data['company_name']. $data['wechat'] . $data['introduction'] . $data['position'] . $data['address_title'] . $data['address_name'];
            if (!msgSecCheck($secMsg)) {
                abort(403, '输入内容有不合法的词汇，请修改后重新提交');
            }
            $cid = $request->get('cid');
            $isRebinding = false; // 是否重新绑定新的公司
            if ($carte->cid != $cid) {
                $isRebinding = true;
            }
            if (!empty($cid) && $isRebinding && $carte) {
                if ($user->companyCardStatus && $user->companyCard->id == $cid) {
                    $carte->cid = $cid;
                    $carte->save();
                    $bindStatus = CompanyBind::AUDIT_SUCCESS_STATUS;
                } else {
                    $bindStatus = CompanyBind::NOT_REVIEWED_STATUS;
                }
                CompanyBind::addCompanyBind(0, $cid, $carte->id, $bindStatus);
            }


            $fromUserCollection = Attention::query()->firstOrCreate([
                'uid' => $user->id,
                'from_id' => $carte->id,
                'status' => Attention::ATTENTION_STATUS_ONE
            ], [
                'exchange_type' => Attention::EXCHANGE_TYPE_FOUR,
                'initial' => getInitial($request->get('name'))
            ]);


            // 标签处理
            $inputTags = $request->get('tags');
            Tag::query()->updateOrCreate([
                'uid' => $user->id,
                'other_uid' => 0,
                'info_id' => $carte->id
            ], [
                'type' => Tag::TYPE_OTHER_PERSON,
                'title' => trim($inputTags),
                'status' => Tag::STATUS_NORMAL
            ]);
            DB::commit();
            return $carte;

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            DB::rollback();
            abort(500, '操作出错');
        }
    }

    protected function getCreateData(Array $tags)
    {
        return collect($tags)->map(function ($tag) {
            return ['title' => $tag, 'status' => Tag::TYPE_OWN];
        })->toArray();
    }
}