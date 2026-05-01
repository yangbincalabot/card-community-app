<?php


namespace App\Services;


use App\Models\Carte;
use App\Models\CompanyCard;
use App\Models\Goods;
use App\Models\GoodsOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserBalanceLog;

class GoodsService
{
    public function list($condition = [], $user = null){
        $query = Goods::query()->orderBy('id', 'desc');

        $appends = [];
        if($user){
            if($user instanceof User && $user->companyCardStatus === true){
                $query->where('user_id', $user->id);
            }
        }

        if($condition){
            if(isset($condition['is_show'])){
                $query->where('is_show', $condition['is_show']);
                $appends['is_show'] = $condition['is_show'];
            }

            if(isset($condition['cid']) && $condition['cid'] > 0){
                $query->where('cid', $condition['cid']);
                $appends['cid'] = $condition['cid'];
            }

            if(isset($condition['keywords']) && strlen($condition['keywords']) > 0){
                $query->where('title', 'LIKE', '%' . $condition['keywords'] . '%');
                $appends['keywords'] = $condition['keywords'];
            }
        }

        return $query->paginate(16)->appends($appends);
    }

    /**
     * 添加商品，企业会员直接保存cid,关联企业账号的获取其企业id
     * @param $formData
     * @param $user
     */
    public function add($formData, $user){
        $cid = $this->getCid($user);
        Goods::query()->create([
            'user_id' => $user->id,
            'cid' => $cid,
            'title' => $formData['title'],
            'price' => $formData['price'],
            'content' => $formData['content'],
            'image' => $formData['image'],
            'images' => $formData['images'],
            'is_show' => $formData['is_show']
        ]);
    }

    public function update($updateData, $id, User $user){
        $goods = Goods::query()->findOrFail($id);
        if($goods->user_id !== $user->id){
            abort(403, '非法操作');
        }

        try{
            DB::beginTransaction();
            if(count($updateData) === 2){
                $goods->is_show = $updateData['is_show'];
            }else{
                $goods->title = $updateData['title'];
                $goods->content = $updateData['content'];
                $goods->image = $updateData['image'];
                $goods->images = $updateData['images'];
                $goods->is_show = $updateData['is_show'];
                $goods->price = $updateData['price'];
            }
            $goods->save();
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            \Log::error($exception->getTraceAsString());
            abort(403, $exception->getMessage());
        }

    }

    public function delete($id, User $user){
        $goods = Goods::query()->findOrFail($id);
        if($goods->user_id !== $user->id){
            abort(403, '非法操作');
        }
        try {
            $goods->delete();
        } catch (\Exception $e) {
            \Log::error($e->getTraceAsString());
            abort(403, '删除失败');
        }
    }

    public function detail($id){
        return Goods::query()->findOrFail($id);
    }


    // 获取公司名片
    private function getCid(User $user){
        $cid = 0;
        if($user->companyCardStatus === true){
            $cid = CompanyCard::query()->where('uid', $user->id)->value('id');
        }else{
            $cid = Carte::query()->where('uid', $user->id)->value('cid');
            if(!$cid){
                abort(403, '请先关联公司或升级企业会员');
            }
        }
        return $cid;
    }


    public function paySuccess($id, $pay_sm){
        $goodsOrder = GoodsOrder::query()->find($id);
        if($goodsOrder){
            $goodsOrder->is_pay = GoodsOrder::IS_PAY_TRUE;
            $goodsOrder->payed_at = Carbon::now();
            if($pay_sm){
                $goodsOrder->pay_sm = $pay_sm;
            }
            $goodsOrder->save();

            // 添加商品销量
            Goods::query()->where('id', $goodsOrder->goods_id)->increment('sales');

            // 商品价格不为0时，添加商家收入记录
            if($goodsOrder->price > 0){
                $user_id = CompanyCard::query()->where('id', $goodsOrder->goods->cid)->value('uid');
                $query = User\UserBalance::query()->where('user_id', $user_id);
                $query->increment('money', $goodsOrder->price);
                $query->increment('total_revenue', $goodsOrder->price);
                $remark = $goodsOrder->goods->title;
                UserBalanceLog::addLog($user_id, UserBalanceLog::LOG_TYPE_INCOME, UserBalanceLog::TYPE_SALES_GOODS,$goodsOrder->price, $remark);
            }
        }

    }

}
