<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CardSquareResource;
use App\Models\Carte;
use App\Models\ReceiveCarte;
use App\Models\Tag;
use App\Models\User;
use App\Models\User\Attention;
use App\Services\CardSquareService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CardSquareController extends Controller
{
    // 名片广场
    public function index(Request $request, CardSquareService $service){
        $cardSquares = $service->getList($request);
        return new CardSquareResource($cardSquares);
    }

    // 名片详情
    public function detail(Request $request, CardSquareService $service){
        $carte = Carte::findOrFail($request->get('id'));
        if($carte->user){
            Carte::unBind($carte->user);
        }

        // 名片信息
        $carte = Carte::with(['company_card', 'user'])->findOrFail($request->get('id'));
        $user = $request->user();
        $is_collection = false; // 对方是否收藏
        if($user->carte){
            $userCollection = Attention::where([
                'uid' => $carte->uid,
                'from_id' => $user->carte->id,
                'status' => Attention::ATTENTION_STATUS_ONE
            ])->first();
            if($userCollection){
                $is_collection = true;
            }
        }


        if($carte->open != 1 && $user->id != $carte->uid && $is_collection === false){
            $responseData = [];
            $responseData['phone'] = $carte->phone ?: $carte->user->phone;
            $responseData['wechat'] = $carte->wechat ?: $carte->user->nickname;
            $responseData['email'] = $carte->email;
            $responseData['address_title'] = $carte->address_title;
            $responseData['name'] = $carte->name;

            foreach($responseData as $key => $value){
                $carte->{$key} = $this->hideStr($key, $value);
            }

        }

        $service->visits($carte, $request);

        // 线下收到的名片，只有名片详情的用户为当前用户才显示数据
        $offlineBusinessCard = [];
        if($carte->uid === $user->id){
            $fromUserIds = $user->receiveCartes()->where('type', ReceiveCarte::TYPE_SCAN)->pluck('from_user_id')->toArray();
            if($fromUserIds){
                $offlineBusinessCard = Carte::query()->whereIn('uid', $fromUserIds)->get(['id', 'uid', 'avatar','company_name']);
            }
        } else {
            Attention::setContactDefault($user->id, $carte->id);
        }
        return new CardSquareResource(compact('carte', 'offlineBusinessCard'));
    }

    public function getOfflineBusinessCard (Request $request) {
        /**
         * @var $user User
         */
        $user = $request->user();
        $uid = $request->input('uid');
        $cartes = collect();
        $receiveCartes = collect();
        if ($uid == $user->id) {
            // 三天内时间区间
            $threeDayAgo = $this->threeDaysAgo();

            $receiveCartes = $user->receiveCartes()->with(['fromUser' => function(Relation $relation){
                $relation->with(['carte' => function(Relation $relation){
                    $relation->select(['id', 'uid', 'cid', 'name', 'company_name', 'avatar', 'position']);
                }])->select(['id', 'nickname', 'avatar', 'type', 'enterprise_at']);

            }])->where('type', ReceiveCarte::TYPE_SCAN)->whereBetween('created_at', $threeDayAgo)->get();
            //$fromUserIds = $user->receiveCartes()->where('type', ReceiveCarte::TYPE_SCAN)->pluck('from_user_id')->toArray();
            $fromUserIds = $receiveCartes->map(function($item) use ($user){
                $item->tag = null;
                if($item->fromUser && $item->fromUser->carte){
                    $tag = Tag::query()->where([
                        'uid' => $user->id,
                        'other_uid' => $item->fromUser->id,
                        'info_id' => $item->fromUser->carte->id,
                        'type' => Tag::TYPE_OTHER_PERSON
                    ])->first();
                    $item->tag = $tag;
                }

                return $item->from_user_id;
            })->toArray();


            if($fromUserIds){
                $cartes = Carte::query()->whereIn('uid', $fromUserIds)->get(['id', 'uid', 'avatar','company_name']);
            }
        }

        return new CardSquareResource(compact('cartes', 'receiveCartes'));
    }

    // 查看对方名片夹
    public function checkCollectionCard(Request $request){
        $card_id = $request->get('card_id');
        $card = Carte::findOrFail($card_id);
        $user = $request->user('api');
        $responseData = [];
        $responseData['phone'] = $card->phone ?: $card->user->phone;
        $responseData['wechat'] = $card->wechat ?: $card->user->nickname;
        $responseData['email'] = $card->email;
        $responseData['address_title'] = $card->address_title;
        $responseData['is_open'] = true;

        // 不公开,(本人直接公开)
        if(intval($card->open) === Carte::OPEN_TWO || ($user && $user->id === $card->uid)){
            $userCollection = null;
            if($user && $user->carte){
                $userCollection = Attention::where([
                    'uid' => $card->uid,
                    'from_id' => $user->carte->id,
                    'status' => Attention::ATTENTION_STATUS_ONE
                ])->first();
            }
            if(!$userCollection){
                foreach($responseData as $key => $value){
                    $responseData[$key] = $this->hideStr($key, $value);
                }
                $responseData['is_open'] = false;
            }
        }
        return new CardSquareResource($responseData);
    }

    // 修改标签
    public function changeTags(Request $request){
        $tags = $request->get('tags');
        $user = $request->user();
        foreach ($tags as $tag){
            if($tag['uid'] == $user->id){
                // 检测输入文本是否合法
                $secMsg = $tag['title'];
                if (!msgSecCheck($secMsg)) {
                    abort(403, '输入内容又不合法的词汇，请修改后重新提交');
                }
                Tag::query()->updateOrCreate([
                    'uid' => $user->id,
                    'other_uid' => $tag['other_uid'],
                    'info_id' => $tag['info_id'],
                    'type' => Tag::TYPE_OTHER_PERSON
                ], [
                    'title' => $tag['title']
                ]);
            }
        }
        $status = 'ok';
        return new CardSquareResource(compact('status'));
    }

    protected function hideStr($key, $value){
        switch ($key) {
            case 'phone':
                return hideStr($value, 3) ?: '未填写';
            case 'email':
                return hideStr($value, 3, 8) ?: '未填写';
            case 'wechat':
                return hideStr($value, 0, 8) ?: '未填写';
            case 'name':
                return hideStr($value, 1, 8) ?: '未填写';
            default:
                return hideStr($value, 0, 10) ?: '未填写';
        }

    }

    // 获取3天前的时间区间
    protected function threeDaysAgo(){
        // 三天内时间区间
        $todayEndDateTime = Carbon::now()->setTime(23, 59, 59);
        $threeDayAgo = Carbon::now()->subDays(3)->setTime(0, 0);
        return [$threeDayAgo, $todayEndDateTime];
    }
}
