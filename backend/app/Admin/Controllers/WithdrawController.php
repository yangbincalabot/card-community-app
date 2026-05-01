<?php

namespace App\Admin\Controllers;

use App\Events\UserMoneyEvent;
use App\Models\User\UserBalance;
use App\Models\User\UserBalanceLog;
use App\Models\Withdraw;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use DB;

class WithdrawController extends Controller
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
            ->header('提现管理')
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
            ->header('提现管理')
            ->description('详细')
            ->body($this->detail($id));
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
            ->header('提现管理')
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
            ->header('提现管理')
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
        $grid = new Grid(new Withdraw);
        $grid->model()->with(['userBank' => function($query){
            $query->with('bank');
        }])->orderBy('id', 'desc');
        $grid->column('id', 'ID')->sortable();
        $grid->column('user.nickname', '用户昵称');
        $grid->column('bank_name', '银行名称')->display(function (){
            return $this->userBank ? $this->userBank->bank->name : '';
        });
        $grid->column('card_name', '持卡人姓名')->display(function (){
            return $this->userBank ? $this->userBank->card_name: '';
        });
        $grid->column('card_number', '卡号')->display(function(){
            return $this->userBank ? $this->userBank->card_number : '';
        });
        $grid->column('money', '提现金额');
        $grid->column('status', '状态')->display(function ($status){
            return Withdraw::getStatus()[$status];
        });
        $grid->column('remark', '备注');
        $grid->column('created_at', '申请时间');
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->disableCreateButton();

        $grid->tools(function(Grid\Tools $tools){
            $tools->batch(function(Grid\Tools\BatchActions $actions){
                $actions->disableDelete();
            });
        });

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('user.nickname', '用户昵称');
            $filter->like('userBank.card_name', '持卡人姓名');
            $filter->like('userBank.card_number', '银行卡号');
            $filter->equal('status', '状态')->select(Withdraw::getStatus());
        });
        $grid->actions(function (Grid\Displayers\Actions $actions){
            // 审核后不再进行编辑操作
            if($actions->row->status !== Withdraw::WITHDRAW_STATUS_STAY){
                $actions->disableEdit();
            }
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
        $withdraw = Withdraw::findOrFail($id);
        $show = new Show($withdraw);
        $show->id('Id');
        $show->user_id('用户昵称')->as(function () use($withdraw){
            return $withdraw->user ? $withdraw->user->nickname : '';
        });
        $show->bank_id('银行卡名称')->as(function () use($withdraw){
            return ($withdraw->userBank && $withdraw->userBank->bank) ? $withdraw->userBank->bank->name : '';
        });
        $show->card_name('此卡人姓名')->as(function() use ($withdraw){
            return $withdraw->userBank ? $withdraw->userBank->card_name : '';
        });
        $show->card_number('卡号')->as(function() use ($withdraw){
            return $withdraw->userBank ? $withdraw->userBank->card_number : '';
        });;
        $show->money('提现金额');
        $show->status('状态')->as(function ($status){
            return Withdraw::getStatus()[$status] ?? '';
        });
        $show->remark('备注');
        $show->created_at('申请时间');
        $show->updated_at('编辑时间');

        $show->panel()
            ->tools(function ($tools) use($withdraw){
                // 未审核状态才能编辑
                if($withdraw->status !== Withdraw::WITHDRAW_STATUS_STAY){
                    $tools->disableEdit();
                }
            });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Withdraw);
        $bank_name = $card_name = $card_number = '';
        $params = request()->route()->parameters();
        if(!empty($params) && isset($params['withdraw'])){
            $id = $params['withdraw'];
            $withdraw = Withdraw::findOrFail($id);
            $bank_name = ($withdraw->userBank && $withdraw->userBank->bank) ? $withdraw->userBank->bank->name : '';
            $card_name = $withdraw->userBank ? $withdraw->userBank->card_name : '';
            $card_number = $withdraw->userBank ? $withdraw->userBank->card_number : '';
        }
        $form->display('user.nickname', '用户昵称');
        $form->display('bank_name', '银行名称')->default($bank_name);
        $form->display('card_name', '持卡人姓名')->default($card_name);
        $form->display('card_number', '卡号')->default($card_number);
        $form->display('money', '提现金额');
        $form->radio('status', '状态')->options([Withdraw::WITHDRAW_STATUS_SUCCESS => '通过', Withdraw::WITHDRAW_STATUS_FAIL=> '不通过']);

        $form->text('remark', '备注说明')->rules('max:190');

        $form->disableCreatingCheck();
        $form->disableEditingCheck();

        $form->saving(function ($form){
            $status = intval($form->status);
            $withdrawStatus = intval($form->model()->status);
            if($withdrawStatus === Withdraw::WITHDRAW_STATUS_STAY && $status !== Withdraw::WITHDRAW_STATUS_STAY && in_array($status, [Withdraw::WITHDRAW_STATUS_SUCCESS, Withdraw::WITHDRAW_STATUS_FAIL])){
                $user = $form->model()->user;

                try{
                    DB::beginTransaction();
                    $userBalance = $user->balance;
                    if(empty($userBalance)){
                        $userBalance = UserBalance::addDefaultData($form->model()->user_id);
                    }
                    // 验证冻结金额是否合法
                    if($userBalance->frozen_money < $form->model()->money){
                        throw new \Exception('非法操作，此用户冻结金额小于提现金额');
                    }
                    $userBalance->decrement('frozen_money', $form->model()->money); // 减去冻结资金

                    if($status === Withdraw::WITHDRAW_STATUS_SUCCESS){
//                        $user->balanceLogs()->create([
//                            'money' => -$form->model()->money,
//                            'log_type' => UserBalanceLog::LOG_TYPE_PAY,
//                            'type' => UserBalanceLog::TYPE_WITHDRAW,
//                            'remark' => '提现审核通过'
//                        ]);
                    }else{
                        // 审核未通过时，增加用户可用金额
                        $user->balance->increment('money', $form->model()->money);
                        // 修改用户密钥key
                        event(new UserMoneyEvent($user));
                        // 如果审核不通过时，增加流水记录
                        $user->balanceLogs()->create([
                            'money' => $form->model()->money,
                            'log_type' => UserBalanceLog::LOG_TYPE_INCOME,
                            'type' => UserBalanceLog::TYPE_WITHDRAW,
                            'remark' => '提现审核不通过'
                        ]);
                    }

                    DB::commit();
                }catch (\Exception $exception){
                    \Log::info($exception->getMessage());
                    DB::rollBack();
                }
            }
        });


        return $form;
    }
}
