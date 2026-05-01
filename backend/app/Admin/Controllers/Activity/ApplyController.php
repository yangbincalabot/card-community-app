<?php

namespace App\Admin\Controllers\Activity;

use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Http\Controllers\Controller;
use App\Models\Activity\Relevance;
use App\Models\Order;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class ApplyController extends Controller
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
            ->header('报名管理')
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
            ->header('报名管理')
            ->description('详情')
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
            ->header('报名管理')
            ->description('更新')
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
            ->header('报名管理')
            ->description('创建')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $activity = new Activity();
        $activityApplyModel = new ActivityApply();
        $grid = new Grid($activityApplyModel);
        $grid->model()->where('status',$activityApplyModel::STATUS_COMPLETED)->whereHas('activity')->orderBy('created_at','desc');
        $grid->filter(function($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->equal('id','id');
            $filter->like('activity.title','活动标题');
            $filter->like('name','联系人');
            $filter->like('phone','联系电话');
        });
        $grid->column('id','报名id')->sortable();
        $grid->column('uid','用户id');
        $grid->column('activity.id','活动id');
        $grid->column('activity.title','活动标题');
        $grid->column('activity.type','活动类型')->using($activity->getType());
        $grid->column('name','联系人');
        $grid->column('phone','联系电话');
        $grid->column('company_name','工作单位');
        $grid->column('specification.title','规格名称');
        $grid->column('price','报名金额');
        $grid->column('order_no','订单编号');
        $grid->column('created_at','创建时间');
        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
//            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            // append一个操作
//            $rowInfo = $actions->row;
//            if(($rowInfo->price > 0) && ($rowInfo->refund_status == ActivityApply::REFUND_STATUS_REFUNDABLE)){
//                $actions->append('<a href="'.route('admin.apply_refund.index',['order'=>$actions->row->id]).'" class="btn btn-primary">退款</a>');
//            }
        });
        $grid->disableCreateButton();//去掉新增按钮
        return $grid;
    }



    static function getUser($user_id,$userModel) {
        $user = $userModel->where('id',$user_id)->first();
        $name = '';
        if($user){
            $name = $user->name ?? $user->nickname;
        }
        return $name;
    }

    static function getActivity($activity_id,$activityModel) {
        $title = $activityModel->where('id',$activity_id)->value('title');
        return $title;
    }

    static function getApplicantArr($apply_id,$relevanceModel) {
        $result = $relevanceModel->where('apply_id',$apply_id)->select('id','apply_id','applicant_id')->with(['applicant'])->get();
        $newData = [];
        if (!$result->isEmpty()) {
            foreach ($result as $key => $value) {
                $applicant = $value->applicant;
//                $newData[$key]['id'] = $applicant['id'];
                $newData[$key]['name'] = $applicant['name'];
                $newData[$key]['region_type'] = ($applicant['region_type'] == 1? '大陆':'其它');
                $newData[$key]['identity_number'] = $applicant['identity_number'];
                $newData[$key]['phone'] = $applicant['phone'];
                $newData[$key]['sex'] = ($applicant['sex'] == 1? '男':'女');
            }
        }
        return $newData;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ActivityApply::findOrFail($id));
        $activityModel = new Activity();
        $show->id('Id');
        $show->uid('用户id');
        $show->aid('活动标题')->as(function () use ($activityModel) {
            $activity_id = $this->aid;
            $title = $activityModel->where('id',$activity_id)->value('title');
            return $title;
        });
        $show->name('联系人');
        $show->phone('联系电话');
        $show->company_name('工作单位');
        $show->price('报名金额');
        $show->order_no('订单编号');
        $show->created_at('创建时间');
        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
            $tools->disableEdit();
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
        $form = new Form(new ActivityApply);

        $form->number('user_id', 'User id');
        $form->number('activity_id', 'Activity id');
        $form->text('urgent_contact', 'Urgent contact');
        $form->mobile('phone', 'Phone');
        $form->number('total_people', 'Total people');
        $form->decimal('total_price', 'Total price');
        $form->text('order_no', 'Order no');
        $form->switch('status', 'Status')->default(1);

        return $form;
    }
}
