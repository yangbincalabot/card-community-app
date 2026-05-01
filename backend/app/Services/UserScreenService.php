<?php


namespace App\Services;


use App\Models\UserScreen;
use Illuminate\Support\Facades\DB;
use Log;
use Carbon\Carbon;

class UserScreenService
{
    public function list($uid){

        $userScreen = UserScreen::query()->select(['area', 'industry'])->firstOrCreate(['uid' => $uid]);

        return $userScreen;
    }

    public function store($uid, array $data){
        $userScreens = UserScreen::query()->firstOrCreate(['uid' => $uid]);
        DB::beginTransaction();
        try{
            $userScreens->area = $data['params']['area'];
            $userScreens->industry = $data['params']['industry'];
            $userScreens->save();
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            Log::error($exception->getTraceAsString());
        }

    }
}
