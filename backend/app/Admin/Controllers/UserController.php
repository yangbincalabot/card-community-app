<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\UserList;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\User\UserRelation;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use DB;
use Illuminate\Support\MessageBag;
use App\Models\User\UserBalanceLog;
use App\Models\User\UserBalance;
class UserController extends Controller
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
            ->header('用户管理')
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
    public function show(User $user, Content $content)
    {
        return $content
            ->header('用户管理')
            ->description('详细')
            ->body(view('admin.card.show', ['user' => $user]));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('用户管理')
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
            ->header('用户管理')
            ->description('新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', 'Id')->sortable();
        $grid->column('nickname', '昵称')->display(function ($nickname){
            return sprintf("<a href='%s'>%s</a>", route('user.show', ['id' => $this->id]), $nickname);
        });
        $grid->column('carte.name', '真实姓名');
        $grid->column('phone', '手机号码');
        $grid->column('balance.money', '可用余额');
        $grid->column('balance.total_revenue', '总收益');

        $grid->column('type', '是否企业会员')->display(function ($type){
            return $type > 1 ? '是' : '否';
        });
        $grid->column('is_admin', '是否设置协会管理员')->switch();
        $grid->column('enterprise_at', '企业会员到期时间')->display(function ($enterprise_at){
            return $enterprise_at ?: '-';
        });
        $grid->column('created_at', '注册时间');

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->tools(function (Grid\Tools $tools){
            $tools->disableBatchActions();
        });
        $grid->disableRowSelector();
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            // 充值
            $actions->prepend('<a href="'. route('user.recharge', ['id' => $actions->row->id]) .'" style="margin-right: 8px;"><i class="fa fa-money"></i></a>');
            $actions->append('<a href="'. route('user.balance.logs', ['user' => $actions->row->id]) .'"><i class="fa fa-usd"></i></a>&nbsp;');
        });

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('nickname', '微信昵称');
            $filter->like('carte.name', '真实姓名');
            $filter->equal('type', '用户类型')->select(User::userTypes('all'));
        });



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
        $show = new Show(User::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->nickname('Nickname');
        $show->email('Email');
        $show->email_verified_at('Email verified at');
        $show->avatar('Avatar');
        $show->type('Type');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        $show->agent_id('Agent id');
        $show->qrcode('Qrcode');
        $show->agent_time('Agent time');

        return $show;
    }


    protected function form()
    {
        $form = new Form(new User);
        $form->switch('is_admin');

        return $form;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
//    protected function form()
//    {
//        $form = new Form(new User);
//        $user = User::find(request()->route()->parameters['user']);
//
//        //TODO  测试阶段，后续可删除
//        $user_auth = User\UserAuth::where('user_id', $user->id)->first();
//        $default = $identifier = '';
//        if($user_auth){
//            preg_match('/([\w\-]+)?/', $user_auth->identifier, $test_field);
//            $identifier = $test_field[0];
//            $default = str_replace($identifier, '', $user_auth->identifier);
//        }
//
//
//
//        $form->text('test_field', '测试字段')->help('模拟多个用户，开头输入中文')->default($default)->rules(function (){
//            $test_field = request()->get('test_field');
//            if($test_field){
//                return 'regex:/^[\x{4e00}-\x{9fa5}]+/u';
//            }
//            return [];
//        }, ['regex' => '开头需要输入中文']);
//
//        $form->ignore('test_field')->saving(function (Form $form) use ($user_auth, $identifier){
//
//            //  编辑
//            if($user_auth && $identifier){
//                $test_field = request()->get('test_field');
//                $user_auth->identifier = $identifier . $test_field;
//                $user_auth->save();
//            }
//        });
//
//
//
//        $form->tools(function (Form\Tools $tools) use ($user) {
//            // 去掉`删除`按钮
//            $tools->disableDelete();
//
//            // 去掉`查看`按钮
//            $tools->disableView();
//        });
//
//        $form->footer(function (Form\Footer $footer) {
//
//
//
//            // 去掉`查看`checkbox
//            $footer->disableViewCheck();
//
//            // 去掉`继续编辑`checkbox
//            $footer->disableEditingCheck();
//
//            // 去掉`继续创建`checkbox
//            $footer->disableCreatingCheck();
//
//
//        });
//
//        return $form;
//    }

    protected function rechargeForm(){
        $form = new Form(new User);
        $form->currency('money', '充值金额')->rules('required');
        $form->radio('log_type', '类型')->options([
            '1' => '增加',
            '2' => '扣除'
        ])->default(1);

        $form->saving(function (Form $form){
            $user = $form->model();

            // 验证密钥
            $userBalance = $user->balance;
            if(!$userBalance){
                $userBalance = UserBalance::addDefaultData($user->id);
            }
            if((!is_numeric($form->money)) || ($form->money <= 0)){
                throw new \Exception('请输入合法金额');
            }
            if(!UserBalance::checkKey($userBalance->key, $user->id)){
                throw new \Exception('非法操作');
            }

            try{
                DB::beginTransaction();
                $money_prefix = '+';
                if($form->log_type == 1){
                    // 处理充值
                    $user->balance->increment('money', $form->money);
                    $remark = '后台充值' . $form->money . '元';

                }else{
                    $user->balance->decrement('money', $form->money);
                    $remark = '后台扣除' . $form->money . '元';
                    $money_prefix = '-';
                }

                $user->balanceLogs()->create([
                    'log_type' => $form->log_type,
                    'type' => User\UserBalanceLog::TYPE_BACKEND_RECHARGE,
                    'money' => $money_prefix . moneyShow($form->money),
                    'remark' => $remark
                ]);

                // 充值完成需要更新密钥
                $userBalance->key = UserBalance::encryptKey($userBalance->money, $userBalance->frozen_money, $userBalance->total_revenue);
                $userBalance->save();

                DB::commit();

                $success = new MessageBag([
                    'title'   => '操作成功',
                ]);

                return back()->with(compact('success'));

            }catch (\Exception $exception){
                DB::rollBack();
                $error = new MessageBag([
                    'title'   => '操作失败',
                    'message' => $exception->getMessage(),
                ]);

                return back()->with(compact('error'));
            }


        });

        $form->footer(function (Form\Footer $footer) {



            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();


        });


        $form->tools(function (Form\Tools $tools) {

            // 去掉`列表`按钮
            $tools->disableList();

            // 去掉`删除`按钮
            $tools->disableDelete();

            // 去掉`查看`按钮
            $tools->disableView();

            // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
            $tools->prepend('<a class="btn btn-sm" href="'. route('user.index') .'"><i class="fa fa-list"></i>&nbsp;&nbsp;列表</a>');
        });
        return $form;
    }


    // 充值
    public function recharge($id, Content $content){
         return $content->header('用户管理')
             ->description('充值')
             ->body($this->rechargeForm()->setAction(route('user.recharge.update', ['id' => $id])));
    }

    public function rechargeUpdate($id)
    {
        return $this->rechargeForm()->update($id);
    }

    public function deleteRelation(Request $request){
        DB::transaction(function () use ($request){
            $user = User::find($request->get('user_id'));
            $user_relation = $user->userParent;
            if($user_relation){
                $user_relation->delete();
            }
        });

        return 'ok';

    }


    public function lanceLogs($id, Content $content){
        $user = User::find($id);
        return $content
            ->header($user->nickname)
            ->description('流水日志')
            ->body($this->lanceLogsGrid($id));
    }
    public function lanceLogsGrid($id){
        $grid = new Grid(new UserBalanceLog);
        $grid->model()->where('user_id', $id)->orderBy('id', 'desc');
        $grid->column('id', 'Id');
        $grid->column('user.nickname', '昵称');
        $grid->column('log_type', '日志操作类型')->display(function ($log_type){
            return UserBalanceLog::getLogType($log_type);
        });
        $grid->column('type', '具体类型')->display(function ($type){
            return UserBalanceLog::getTypeText($type);
        });
        $grid->column('money', '金额');
        $grid->column('remark', '操作日志');
        $grid->column('updated_at', '时间');
//
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->tools(function (Grid\Tools $tools){
            $tools->disableBatchActions();
            $tools->append(new UserList());
        });
        $grid->disableRowSelector();
        $grid->disableActions();

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->equal('log_type', '日志操作类型')->select(UserBalanceLog::getLogType());
            $filter->equal('type', '具体类型')->select(UserBalanceLog::getTypeText());
        });


        return $grid;
    }


}
