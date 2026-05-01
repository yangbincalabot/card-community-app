<?php


namespace App\Services;


use App\Models\ApplicationAssociation;
use App\Models\Association;
use App\Models\Carte;
use App\Models\CompanyCard;
use App\Models\CompanyCardLog;
use App\Models\CompanyCardRole;
use App\Models\CompanyRole;
use App\Models\Industry;
use App\Models\User;
use App\Models\User\UserAuth;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AssociationsService
{

    public function getAssociations($user, array $condition = []){
        $query = Association::query()->latest();
        if ($user && $user instanceof User){
            $query->where('user_id', $user->id);
        }else{

        }

        return $query->paginate();
    }

    // 商家添加协会
    public function createAssociations(User $user, array $formData){
        // 检测输入文本是否合法
        $secMsg = $formData['name'].$formData['desc'].$formData['service_desc'].$formData['contact_us'].$formData['instructions'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }

        $association = Association::query()->create([
            'user_id' => $user->id,
            'cid' => $user->companyCard->id,
            'name' => $formData['name'],
            'image' => $formData['image'],
            'status' => Association::STATUS_NOT_REVIEWED,
            'images' => $formData['images'],
            'desc' => $formData['desc'],
            'fee' => $formData['fee'] ?? 0,
            'pid' => $formData['pid'] ?? 0,
            'service_images' => $formData['service_images'],
            'service_desc' => $formData['service_desc'],
            'member_wall' => $formData['member_wall'],
            'contact_us' => $formData['contact_us'],
            'instructions' => $formData['instructions'],
        ]);

        // 添加默认角色，把自己也加进入
        $role = CompanyRole::query()->create([
            'uid' => $user->id,
            'aid' => $association->id,
            'name' => '理事会',
            'sort' => 1,
        ]);

        CompanyCardRole::query()->create([
           'company_id' => $user->companyCard->id,
           'role_id' => $role->id,
            'aid' => $association->id,
            'role_sort' => 1
        ]);
    }

    public function showOwnAssociation(User $user, $id) {
        $association = Association::query()->findOrFail($id);
        if ($association->user_id !== $user->id && $user->is_admin === User::IS_ADMIN_FALSE){
            abort(403, '非法操作');
        }

        return $association;
    }

    public function selectAssociation($id) {
        $query = Association::query()->where('status', Association::STATUS_SUCCESS);
        if ($id) {
            $query->where('id', '<>', $id);
        }
        $list = $query->where('user_id', '<>', 0)->where('pid', 0)->get();
        return $list;
    }

    public function updateAssociation(User $user, $id, array $formData){
        $isDifferent = false;
        $association = $this->showOwnAssociation($user, $id);

        foreach (['name', 'desc'] as $value){
            if ($formData[$value] != $association->{$value}){
                $isDifferent = true;
                break;
            }
        }


        $association->image = $formData['image'];
        $association->name = $formData['name'];
        $association->desc = $formData['desc'];
        $association->images = $formData['images'];

        $association->service_images = $formData['service_images'];
        $association->service_desc = $formData['service_desc'];
        $association->member_wall = $formData['member_wall'];
        $association->contact_us  = $formData['contact_us'];
        $association->instructions = $formData['instructions'];

        $association->pid = $formData['pid'] ?? 0;
//        if ($association->user_id > 0){
//            $association->fee = $formData['fee'];
//        }
        // 检测输入文本是否合法
        $secMsg = $formData['name'].$formData['desc'].$formData['service_desc'].$formData['contact_us'].$formData['instructions'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }

        if ($isDifferent && in_array($association->status, [Association::STATUS_FAILURE])){
            $association->status = Association::STATUS_NOT_REVIEWED;
            $association->remark = '重新编辑协会信息';
        }
        $association->save();
    }

    public function deleteAssociation($id){
        Association::query()->where('id', $id)->delete();
        // 删除协会后，解除用户和协会之间的关系
        User::query()->where('aid', $id)->update(['aid' => 0]);
    }




    public function getList(Request $request){
        return $this->defaultSearch($request);
    }


    // 默认搜索
    private function defaultSearch(Request $request){

        $query = $this->baseCondition(Association::query());

        $append = [];
        $param = $request->all();
        $this->baseSearch($query, $param, $append);
        return $query->paginate()->appends($append);
    }

    // 定制搜索
    private function customSearch(array $params, array $other){


        $query = $this->baseCondition(Association::query());


        $append = [
            'params' => [],
            'searchType' => 'custom',
        ];




        // 处理联合
        $model = null;
        if(!empty(array_filter($params)) || !empty($other)){
            $query = Carte::query()->where('id', 0);
            if(empty(array_filter($params)) && !empty($other)){
                $index = 10000;
                $subQuery[$index] = $this->baseCondition(Carte::query());
                $this->baseSearch($subQuery[$index], $other, $append);
                $query->union($subQuery[$index]);
            }
            $subQuery = [];
            foreach ($params as $k => $v){
                if (empty($v)) {
                    continue;
                }
                foreach ($v as $key => $param){
                    $index = mt_rand(100, 9999) + $key;
                    // 删除行业的name字段
                    if(isset($param['name'])){
                        unset($param['name']);
                    }
                    $param = array_merge($param, $other);
                    $subQuery[$index] = $this->baseCondition(Carte::query());
                    $this->baseSearch($subQuery[$index], $param, $append);
                    $query->union($subQuery[$index]);
                }
            }


//            $carteQuery = Carte::query()->where('open', Carte::OPEN_ONE)->where('uid', '<>', 0)
//                ->where('name', '<>', '')->where('company_name', '<>', '')->where('phone', '<>', '')->with(['user' => function($query){
//                $query->with('companyCard');
//            }, 'industry'])->latest()->orderBy('id', 'desc');
//
//            if($model){
//                return $carteQuery->mergeBindings($model->getQuery())
//                    ->from(DB::raw("({$model->toSql()}) as assets_device"))
//                    ->paginate()->appends($append);
//            }

            return $query->paginate()->appends($append);

        }
        return $query->latest()->orderBy('id', 'desc')->paginate()->appends($append);
    }


    private function baseCondition($query){
        $query->with(['company' => function($query){
            $query->with('industry');
        }])->where('user_id', '>', 0)->where('status', Association::STATUS_SUCCESS);
        return $query;
    }


    // 组装搜索条件
    private function baseSearch($query, array $param, &$append){
        // 关键字查找
        $condition = [];
        if(isset($param['keyword'])){
            $keyword = $param['keyword'];
            if(!empty($keyword)){
                $query->where(function($query) use ($keyword){
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere(function($query) use ($keyword){
                            $cids = CompanyCard::where('company_name',  'like', '%' . $keyword . '%')->pluck('id')->toArray();
                            if($cids){
                                $query->whereIn('cid', $cids);
                            }
                        });
                });
                $condition['keyword'] = $keyword;
            }
        }




        if(isset($param['industry_id'])){
            // 所属行业
            $industry_id = $param['industry_id'];
            if(!empty($industry_id)){
                if(strpos($industry_id, '-') !== false){
                    $idLimits = explode('-', $industry_id);
                    if($idLimits && $idLimits[0] > 0 && $idLimits[1] == 0){
                        // 父级不默认，子级默认，找出所有下级
                        $industryIds = Industry::query()->where('parent_id', $idLimits[0])->pluck('id')->toArray();
                        $cids = CompanyCard::query()->whereIn('industry_id', $industryIds)->pluck('id')->toArray();
                        $query->whereIn('cid', $cids);
                    }
                }
                if(is_numeric($industry_id)){
                    $cids = CompanyCard::query()->where('industry_id', $industry_id)->pluck('id')->toArray();
                    $query->whereIn('cid', $cids);
                }

            }
            if(!empty($industry_id)){
                $condition['industry_id'] = $industry_id;
            }
        }



        if(isset($param['select_province']) && !empty($param['select_province'])){
            // 所在省份
            $province = $param['select_province'];
            if(!empty($province)){

                $cids = Carte::query()->where('province', $province)->pluck('cid')->toArray();
                $query->whereIn('cid', $cids);
                $condition['province'] = $province;
            }
        }

        if(isset($param['province']) && !empty($param['province'])){
            // 所在省份
            $province = $param['province'];
            if(!empty($province)){
                $cids = Carte::query()->where('province', $province)->pluck('cid')->toArray();
                $query->whereIn('cid', $cids);
                $condition['province'] = $province;
            }
        }


        if(isset($param['select_city']) && !empty($param['select_city'])){
            // 所在城市
            $city = $param['select_city'];
            if(!empty($city)){
                $cids = Carte::query()->where('city', $city)->pluck('cid')->toArray();
                $query->whereIn('cid', $cids);
                $condition['city'] = $city;
            }
        }

        if(isset($param['city']) && !empty($param['city'])){
            // 所在城市
            $city = $param['city'];
            if(!empty($city)){
                $cids = Carte::query()->where('city', $city)->pluck('cid')->toArray();
                $query->whereIn('cid', $cids);
                $condition['city'] = $city;
            }
        }




        $query->latest()->orderBy('id', 'desc');
        if(isset($append['params'])){
            $append['params'][] = $param;
        }else{
            $append = $condition;
        }
        return $query;
    }


    public function applicationSociety(User $user, $aid, $reason, $type, $avatar_url, $carte_id, $role_id){
        if ($type == ApplicationAssociation::TYPE_COMPANY && $user->companyCardStatus === false){
            abort(403, '请升级企业会员');
        }

        $carte = Carte::query()->find($carte_id);
        if (!$carte) {
            abort(403, '提交的名片不合法');
        }

        $association = Association::query()->findOrFail($aid);
        if ($association->user_id == 0){
            abort(403, '非法操作');
        }

        if (empty($reason)){
            abort(403, '请输入申请理由');
        }

        if ($association->user_id === $user->id){
            abort(403, '创建者不能申请');
        }

        $base = [
            'user_id' => $user->id,
            'aid' => $aid,
        ];

        if (ApplicationAssociation::query()->where($base)->where('status', ApplicationAssociation::STATUS_NOT_REVIEWED)->exists()){
            abort(403, '等待对方审核');
        }

        if (ApplicationAssociation::query()->where($base)->where('status', ApplicationAssociation::STATUS_SUCCESS)->exists()){
            abort(403, '您已是该协会成员');
        }

        $base['reason'] = $reason;
        $base['type'] = $type;
        $base['avatar_url'] = $avatar_url ?: $carte->avatar;
        $base['carte_id'] = $carte_id;
        $base['role_id'] = $role_id;

        ApplicationAssociation::query()->create($base);
    }

    public function applicationList($aid){
        return ApplicationAssociation::query()->with('user')->where('aid', $aid)->latest()->paginate();
    }

    public function verify($aid, $id, $status){
        $applicationAssociation = ApplicationAssociation::query()->with('user')->where(['id' => $id, 'aid' => $aid])->firstOrFail();
        if ($status > ApplicationAssociation::STATUS_NOT_REVIEWED  && in_array($status, ApplicationAssociation::STATUS)){
            if ($status == ApplicationAssociation::STATUS_SUCCESS){
                // 处理审核通过的操作
//                $role_id = CompanyRole::query()->where(['aid' => $aid])->orderBy('sort', 'DESC')->value('id');
                $role_id = $applicationAssociation->role_id;
                if (empty($role_id)){
                    abort(403, '请先创建角色');
                }

                $company_id = 0;

                if ($applicationAssociation->type == ApplicationAssociation::TYPE_COMPANY){
                    try {
                        $company_id = $applicationAssociation->user->companyCard->id;
                    }catch (\Exception $exception){
                        $company_id = 0;
                        \Log::error($exception->getTraceAsString());
                        \Log::error($exception->getMessage());
                    }
                }



                CompanyCardRole::query()->firstOrCreate([
                    'company_id' => $company_id,
                    'role_id' => $role_id,
                    'aid' => $aid,
                    'carte_id' => $applicationAssociation->carte_id,
                ], [
                    'role_sort' => CompanyCardRole::query()->where(['role_id' => $role_id, 'aid' => $aid])->max('role_sort') + 1,
                    'avatar_url' => $applicationAssociation->avatar_url,
                    'is_company' => $applicationAssociation->type == ApplicationAssociation::TYPE_PERSONAL ? CompanyCardRole::IS_COMPANY_FALSE : CompanyCardRole::IS_COMPANY_TRUE,
                ]);
                $uid = $applicationAssociation->user_id;
                User::query()->where('id', $uid)->update(['aid' => $applicationAssociation->aid]);
            }else{
                // 如果拒绝申请，需要处理退款流程
                DB::beginTransaction();
                try{
                    $association = $applicationAssociation->association;
                    if ($association && bccomp($applicationAssociation->fee, 0.00, 2) > 0){
                        $user = $applicationAssociation->user;


                        if($applicationAssociation->pay_type == ApplicationAssociation::PAY_TYPE_BALANCE){
                            // 余额支付
                            if($user->balance){
                                $user->balance->increment('money', $applicationAssociation->fee);
                                $this->userBalanceLogs($user, UserBalanceLog::LOG_TYPE_INCOME, UserBalanceLog::TYPE_APPLICATION_REFUND, $applicationAssociation->fee);
                            }

                            if ($association->user && $association->user->balance && bccomp($applicationAssociation->fee, 0.00, 2) > 0){
                                $association->user->balance->decrement('money', $applicationAssociation->fee);
                                $this->userBalanceLogs($association->user, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_APPLICATION_REFUND, $applicationAssociation->fee);
                            }


                        }elseif ($applicationAssociation->pay_type == ApplicationAssociation::PAY_TYPE_WECHAT && bccomp($applicationAssociation->fee, 0.00, 2) > 0){
                            // 微信支付

                            // 处理微信退款
                            $refundOrderSn = $applicationAssociation->out_trade_no;
                            \Log::info('协会退款单号：' . $refundOrderSn);
                            $money = bcmul($applicationAssociation->fee,100);
                            // 如果开启沙箱模式
                            if (config('pay.wechat.mode') === 'dev'){
                                $money = 101;
                            }
                            app('wechat_pay')->refund([
                                'out_trade_no' => $refundOrderSn,
                                'total_fee' => $money,
                                'refund_fee' => $money,
                                'out_refund_no' => $refundOrderSn,
                                'notify_url' => ngrok_url('society.wechat.refund_notify'),
                            ]);

                        }
                    }
                    DB::commit();
                }catch (\Exception $exception){
                    DB::rollBack();
                    \Log::error($exception->getTraceAsString());
                    abort(403, $exception->getMessage());
                }
            }

            $applicationAssociation->status = $status;
            $applicationAssociation->save();
        }else{
            abort(403, '非法操作');
        }
    }

    public function subAudit($pid){
        return Association::query()->where(['status' => Association::STATUS_SUCCESS, 'pid' => $pid])->paginate();
    }

    public function subAuditVerify($aid, $status){
        $info = Association::query()->where('id', $aid)->first();
        if (empty($info)) {
            abort(403, '该协会不存在，无法操作');
        }
        if ($info->pat != Association::PAT_UNDER_REVIEW) {
            abort(403, '审核状态已确定，无法再次进行操作');
        }
        if (!in_array($status, [Association::PAT_SUCCESS, Association::PAT_FAIL])) {
            abort(403, '该提交状态不确认，无法操作');
        }
        $info->pat = $status;
        $info->save();
    }

    public function checkJoined(User $user, $aid) {
        $association = Association::query()->where('status', Association::STATUS_SUCCESS)->findOrFail($aid);
        if ($association->user_id === $user->id){
            return true;
        }
        // 审核通过或者存在未审核记录，隐藏申请入口
        return ApplicationAssociation::query()->where([
            'user_id' => $user->id,
            'aid' => $aid
        ])->whereIn('status', [ApplicationAssociation::STATUS_SUCCESS, ApplicationAssociation::STATUS_NOT_REVIEWED])->exists();
    }


    public function balancePay(User $user, $aid, $cashPassword){
        $association = Association::query()->findOrFail($aid);
        if (!$association->user){
            abort(403, '参数有误');
        }

        $companyRole = CompanyRole::query()->where(['id' => request()->get('role_id'), 'aid' => $aid])->first();
        if (!$companyRole) {
            abort(403, '参数有误');
        }

        if (!Hash::check($cashPassword, $user->cash_password)){
            abort(403, '支付密码错误');
        }

        $base = [
            'user_id' => $user->id,
            'aid' => $aid
        ];

        if (ApplicationAssociation::query()->where($base)->where('status', ApplicationAssociation::STATUS_NOT_REVIEWED)->exists()){
            abort(403, '您已提交过,等待审核');
        }

        $userBalance = $user->balance;
        if (!$userBalance){
            $userBalance = UserBalance::addDefaultData($user->id);
        }
        $money = $userBalance->money;
        if (bccomp($money, $association->fee) > -1){
            DB::beginTransaction();
            try{
                // 增加当前会员流水
                $userBalance->decrement('money', $association->fee);
                $this->userBalanceLogs($user, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_APPLICATION_SOCIETY_PAY, $association->fee);


                // 增加协会创建人流水
                if (!$association->user->balance){
                    UserBalance::addDefaultData($association->user->id);
                }
                $association->user->balance->increment('money', $association->fee);
                $this->userBalanceLogs($association->user, UserBalanceLog::LOG_TYPE_INCOME, UserBalanceLog::TYPE_APPLICATION_SOCIETY_INCOME, $association->fee);

                $base['reason'] = request()->get('reason');
                $base['pay_type'] = ApplicationAssociation::PAY_TYPE_BALANCE;
                $base['fee'] = $association->fee;
                $base['type'] = request()->get('type');
                $base['avatar_url'] = request()->get('avatar_url');
                $base['carte_id']  = request()->get('carte_id');
                $base['role_id'] = request()->get('role_id');

                ApplicationAssociation::query()->create($base);

                DB::commit();
            }catch (\Exception $exception){
                DB::rollBack();
                \Log::error($exception->getTraceAsString());
                abort(403, $exception->getMessage());
            }

        }else{
            abort(403, '余额不足');
        }
    }


    public function wechatPay(User $user, $aid, $reason, $type, $avatar_url, $carte_id, $role_id){
        $association = Association::query()->findOrFail($aid);
        if (!$association->user){
            abort(403, '参数有误');
        }
        $companyRole = CompanyRole::query()->where(['id' => $role_id, 'aid' => $aid])->first();
        if (!$companyRole) {
            abort(403, '参数有误');
        }

        $base = [
            'user_id' => $user->id,
            'aid' => $aid
        ];

        if (ApplicationAssociation::query()->where($base)->where('status', ApplicationAssociation::STATUS_NOT_REVIEWED)->exists()){
            abort(403, '您已提交过,等待审核');
        }

        $order_no = createOrderNo();


        if (bccomp($companyRole->fee, 0.00, 2) > 0){
            DB::beginTransaction();
            try{
                $this->userBalanceLogs($user, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_APPLICATION_SOCIETY_PAY, $association->fee);


                // 统一下单
                $userAuthWhere['user_id'] = $user->id;
                $userAuthWhere['identity_type'] = UserAuth::IDENTITY_TYPE_WX_MINI;
                $userOpenId = UserAuth::where($userAuthWhere)->value('identifier');
                $money = bcmul($companyRole->fee,100);
                if (config('pay.wechat.mode') === 'dev'){
                    // 如果开启沙箱测试
                    $money = 101;
                }
                $order = [
                    'out_trade_no' => $order_no,
                    'body' => '申请协会',
                    'total_fee' => $money,
                    'openid' => $userOpenId,
                    'notify_url' => route('society.wechat.notify'),
                ];
                \Log::info('协会支付单号：' . $order_no);
                $pay = app('wechat_pay')->miniapp($order);


                // 缓存
                $cacheData = [
                    'user_id' => $user->id,
                    'aid' => $association->id,
                    'reason' => $reason,
                    'fee' => $companyRole->fee,
                    'pay_type' => ApplicationAssociation::PAY_TYPE_WECHAT,
                    'role_id' => $role_id,
                    'type' => $type,
                    'avatar_url' => $avatar_url,
                    'carte_id' => $carte_id,
                ];

                Cache::rememberForever($order_no, function () use ($cacheData){
                    return json_encode($cacheData);
                });
                DB::commit();
                return $pay;
            }catch (Exception $exception){
                DB::rollBack();
                Log::error($exception->getTraceAsString());
                abort(403, $exception->getMessage());
            }
        }else{
            abort(403, '金额参数有误');
        }

        return null;
    }

    // 会员流水
    protected function userBalanceLogs(User $user, $logType, $type,  $money){
        // UserBalanceLog::LOG_TYPE_PAY
        // UserBalanceLog::TYPE_COMPANY_FEE
        return UserBalanceLog::addLog($user->id, $logType, $type, $money);
    }


    public function wechatPayNotify(){
        DB::beginTransaction();
        try{
            $data = app('wechat_pay')->verify();
            $applicationInfo = Cache::get($data->out_trade_no);
            if (!$applicationInfo){
                return 'fail';
            }

            $applicationInfo = json_decode($applicationInfo, true);
            $association = Association::query()->find($applicationInfo['aid']);
            if (empty($association)) {
                return 'fail';
            }

            // 增加协会创建人流水
            if (!$association->user->balance){
                UserBalance::addDefaultData($association->user->id);
            }
            $association->user->balance->increment('money', $association->fee);
            $this->userBalanceLogs($association->user, UserBalanceLog::LOG_TYPE_INCOME, UserBalanceLog::TYPE_APPLICATION_SOCIETY_INCOME, $association->fee);

            $base['user_id'] = $applicationInfo['user_id'];
            $base['aid'] = $applicationInfo['aid'];
            $base['reason'] = $applicationInfo['reason'];
            $base['pay_type'] = $applicationInfo['pay_type'];
            $base['fee'] = $applicationInfo['fee'];
            $base['out_trade_no'] = $data->out_trade_no;
            $base['role_id'] = $applicationInfo['role_id'];
            $base['type'] = $applicationInfo['type'];
            $base['avatar_url'] = $applicationInfo['avatar_url'];
            $base['carte_id'] = $applicationInfo['carte_id'];
            ApplicationAssociation::query()->create($base);
            Cache::forget($data->out_trade_no);
            DB::commit();
            return app('wechat_pay')->success();
        }catch (\Exception $exception){
            DB::rollBack();
            \Log::error($exception->getTraceAsString());
            return 'fail';
        }
    }


    public function wechatRefundNotify(){
        DB::beginTransaction();
        try{
            // 校验回调参数是否正确
            $data = app('wechat_pay')->verify();

            $applicationAssociation = ApplicationAssociation::query()->where('out_trade_no', $data->out_trade_no)->find();
            if (!$applicationAssociation){
                return 'fail';
            }

            // 减去协会创建者流水
            $association = $applicationAssociation->association;
            if ($association && $association->user && $association->user->balance){
                $association->user->balance->decrement('money', $association->fee);
                $this->userBalanceLogs($association->user, UserBalanceLog::LOG_TYPE_PAY, UserBalanceLog::TYPE_APPLICATION_REFUND, $applicationAssociation->fee);
            }
            DB::commit();
            return app('wechat_pay')->success();
        }catch (\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return 'fail';
        }
    }

    // 获取协会信息
    public function info($aid) {
        $association = Association::query()->with(['roles', 'company' => function($query) {
            $query->with('industry');
        }])->findOrFail($aid);
        return $association;
    }
}
