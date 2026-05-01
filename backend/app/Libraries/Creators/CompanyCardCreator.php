<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/18
 * Time: 16:20
 */

namespace App\Libraries\Creators;


use App\Models\Carte;
use Illuminate\Http\Request;
use App\Models\CompanyCard;
use Illuminate\Support\Facades\DB;

class CompanyCardCreator
{
    public function updateOrCreate(Request $request)
    {
        DB::beginTransaction();

        $data = [];
        $data['company_name'] = $request->get('company_name');
        $data['logo'] = $request->get('logo');
        $data['contact_number'] = $request->get('contact_number');
        $data['industry_id'] = $request->get('industry_id');
        $data['introduction'] = $request->get('introduction');
        $data['website'] = $request->get('website');
        $data['images'] = $request->get('images');
        $data['longitude'] = $request->get('longitude');
        $data['latitude'] = $request->get('latitude');
        $data['address_title'] = $request->get('address_title');
        $data['address_name'] = $request->get('address_name');
        $secMsg = $data['company_name'] . $data['introduction'] . $data['website'] . $data['address_title'] . $data['address_name'];
        if (!msgSecCheck($secMsg)) {
            abort(403, '输入内容又不合法的词汇，请修改后重新提交');
        }
        try {
            $data['initial'] = getInitial($data['company_name']); // 公司名首字母
            $uid = $request->user()->id;
            $oldInfo = CompanyCard::query()->where('uid', $uid)->first();
            if (empty($oldInfo)) {
                $companyCard = CompanyCard::query()->create($data);
                $companyCard->role_sort = $companyCard->id;
                $companyCard->save();
            } else {
                $companyCard = CompanyCard::query()->updateOrCreate(['uid' => $uid], $data);
            }
//            $companyCard = CompanyCard::updateOrCreate(['uid' => $uid], $data);
            Carte::query()->where('uid', $uid)->update(['cid' => $companyCard->id]);
            DB::commit();
            return $companyCard;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            DB::rollBack();
            abort(500, '操作出错');
        }
    }
}