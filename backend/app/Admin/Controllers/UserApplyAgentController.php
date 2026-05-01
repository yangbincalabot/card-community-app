<?php

namespace App\Admin\Controllers;

use App\Http\Requests\Request;
use App\Models\Agent;
use App\Models\Store;
use App\Models\User;
use App\Models\User\UserApplyAgent;
use App\Http\Controllers\Controller;
use App\Services\RecommendRewardService;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Encore\ChinaDistpicker\DistpickerFilter;
use Illuminate\Support\Facades\Log;

class UserApplyAgentController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('代理审核管理')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('代理审核管理')
            ->description('description')
            ->body($this->detail($id));
    }

    protected $edit_id;

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $this->edit_id = $id;
        return $content
            ->header('代理审核管理')
            ->description('编辑')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('代理审核管理')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserApplyAgent);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', 'Id')->sortable();
        $grid->column('user.nickname', '用户昵称');
        $grid->column('agent.name', '代理等级');
        $grid->column('name', '申请人姓名');
        $grid->column('mobile', '申请人手机号码');
        $grid->column('id_card', '身份证号码');
        $grid->column('full_address', '详细地址');
        $grid->column('status', '审核状态')->display(function ($status){
            return UserApplyAgent::getStatus()[$status];
        });

        $grid->column('created_at', '申请时间');
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->disableCreateButton();

        $grid->actions(function (Grid\Displayers\Actions $actions){
            // 待审核和审核通过状态时可以修改，待审核时只能修改审核状态，审核通过时只能修改代理等级
            if(!in_array($actions->row->status ,[UserApplyAgent::APPLY_STATUS_STAY,UserApplyAgent::APPLY_STATUS_SUCCESS])){
                $actions->disableEdit();
            }

        });

        $grid->filter(function (Grid\Filter $filter){
            Grid\Filter::extend('distpicker', DistpickerFilter::class);
            $filter->disableIdFilter();
            $filter->equal('agent_id', '代理等级')->select(Agent::getSelectOptions());
            $filter->like('user.nickname', '用户昵称');
            $filter->like('name', '申请人姓名');
            $filter->like('mobile', '申请人手机号码');
            $filter->like('id_card', '身份证号码');
            $filter->equal('status', '状态')->select(UserApplyAgent::getStatus());
            $filter->distpicker('province', 'city', 'district', '地区');
        });

        // 解决加载地区筛选时，默认打开面板问题
        $js = <<<EOT
$("#filter-box").addClass('hide');
EOT;
        \Admin::script($js);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(UserApplyAgent::findOrFail($id));

        $show->id('Id');
        $show->user_id('User id');
        $show->agent_id('Agent id');
        $show->name('Name');
        $show->mobile('Mobile');
        $show->id_card('Id card');
        $show->province('Province');
        $show->city('City');
        $show->district('District');
        $show->address('Address');
        $show->status('Status');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    protected $oldStatus;

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserApplyAgent);

        $edit_id = $this->edit_id;
        $oldInfo = (new UserApplyAgent)->where('id',$edit_id)->with('agent')->first();

        $form->display('user.nickname', '用户昵称');

        $form->display('name', '申请人姓名');
        $form->display('mobile', '申请人手机号码');
        $form->text('id_card', '身份证');
        $form->display('full_address', '详细地址');

        if($oldInfo){
            Log::info('$oldInfo');
            if($oldInfo->status == UserApplyAgent::APPLY_STATUS_STAY){

                $form->display('agent.name', '代理等级');
                $form->select('status', '状态')->options(UserApplyAgent::getStatus())->default(UserApplyAgent::APPLY_STATUS_STAY);
                $form->text('remark', '备注说明');

            }elseif ($oldInfo->status == UserApplyAgent::APPLY_STATUS_SUCCESS){
                $agentType = Agent::pluck('name','type')->toArray();

                $form->display('status', '状态')->with(function ($value) use ($oldInfo) {
                    return UserApplyAgent::getStatus($oldInfo->status);
                });
//                $form->display('remark', '备注说明');
                $form->text('remark', '备注说明');
                $form->select('agent_id', '代理等级')->options(array_merge([1 => ''],$agentType));

            }
        }else{
            $form->select('status', '状态')->options(UserApplyAgent::getStatus())->default(UserApplyAgent::APPLY_STATUS_STAY);
            $agentType = Agent::pluck('name','type')->toArray();
            $form->text('remark', '备注说明');
            $form->select('agent_id', '代理等级')->options(array_merge([1 => ''],$agentType));
        }


        $form->disableCreatingCheck();
        $form->disableEditingCheck();

        //保存后回调
        $form->saved(function (Form $form) {
            Log::info('=================== $form->saved START===================');
            $applyAgentInfo = $form->model();
            Log::info('$applyAgentInfo',$applyAgentInfo->toArray());
            $oldStatus = $this->oldStatus;
            Log::info('$oldStatus='.$oldStatus);
            if(($oldStatus == UserApplyAgent::APPLY_STATUS_STAY) && ($applyAgentInfo->status == UserApplyAgent::APPLY_STATUS_SUCCESS)){
                // 如果原状态是待审核，说明是审核申请，并且审核已通过
                Log::info('如果原状态是待审核，说明是审核申请，并且审核已通过');
                $user = $form->model()->user;
                try{
                    DB::beginTransaction();
                    // 如果审核通过时，修改用户信息
                    if($applyAgentInfo->status == UserApplyAgent::APPLY_STATUS_SUCCESS){
                        $user->agent_id = $form->model()->agent_id;
                        $user->type = $form->model()->agent->type;
                        $user->agent_time = Carbon::now(); // 成为代理商时间
                        $user->name = $form->model()->name;
                        $user->save();

                        // 推荐提成
                        app('App\Services\RecommendRewardService')->computeReward($user, $form->model());
                        // 添加默认门店信息
                        // 店中店或者区域服务商
                        if(in_array($user->type, [User::USER_TYPE_TWO, User::USER_TYPE_FOUR])){
                            $default_image = 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/dule/store.png'; // 默认图片地址
                            Store::addDefaultStore([
                                'user_id' => $user->id,
                                'name' => $form->model()->name . '的店铺',
                                'province' => $form->model()->province,
                                'city' => $form->model()->city,
                                'district' => $form->model()->district,
                                'address' => $form->model()->address,
                                'contact_name' => $form->model()->name,
                                'contact_mobile' => $form->model()->mobile,
                                'image' => $default_image,
                                'images' => [$default_image]
                            ]);
                        }

                    }
                    DB::commit();
                }catch (\Exception $exception){
                    Log::info($exception->getMessage());
                    DB::rollBack();
                }

            }elseif ($oldStatus == UserApplyAgent::APPLY_STATUS_SUCCESS){
                Log::info('如果原状态为审核通过，则说明可能是修改代理等级');
                // 如果原状态为审核通过，则说明可能是修改代理等级，
                $this->oldStatus = UserApplyAgent::APPLY_STATUS_SUCCESS;
                // 将会员表中的用户类型也修改掉
                $user = $form->model()->user;
                $user->agent_id = $form->model()->agent_id;
                $user->type = $form->model()->agent->type;
                $user->save();
            }
            Log::info('=================== $form->saved END===================');
        });


        $form->saving(function ($form){
            $oldInfo = $form->model();
            Log::info('=================== $form->saving START===================');
            Log::info('$oldInfo = $form->model();',$form->model()->toArray());

            if($oldInfo){
                Log::info('$oldInfo->status='.$oldInfo->status);
                if($oldInfo->status == UserApplyAgent::APPLY_STATUS_STAY){
                    // 如果原状态是待审核，说明是审核申请，这个时候需要忽略掉 代理等级 这个字段
                    $this->oldStatus = UserApplyAgent::APPLY_STATUS_STAY;
                }elseif ($oldInfo->status == UserApplyAgent::APPLY_STATUS_SUCCESS){
                    // 如果原状态为审核通过，则说明可能是修改代理等级，这个时候需要忽略掉 审核状态 这个字段
                    $this->oldStatus = UserApplyAgent::APPLY_STATUS_SUCCESS;
                }
                Log::info('$this->oldStatus='.$this->oldStatus);
            }
            Log::info('=================== $form->saving END===================');
        });

        return $form;
    }

}
