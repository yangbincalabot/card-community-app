<?php

namespace App\Admin\Controllers\Activity;

use App\Models\Activity\Activity;
use App\Models\Activity\ActivityApply;
use App\Models\Activity\Applicant;
use App\Models\Activity\Relevance;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Admin\Extensions\ApplicantExporter;

class RelevanceController extends Controller
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
            ->header('报名人管理')
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
            ->header('报名人管理')
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
            ->header('报名人管理')
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
            ->header('报名人管理')
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
        $relevanceModel = new Relevance();
        $grid = new Grid($relevanceModel);
        $applicantModel = new Applicant();
        $activityModel = new Activity();
        $regionArr = $applicantModel->getRegionType();
        $sexArr = $applicantModel->getSex();
        $normal_status = ActivityApply::STATUS_ONE;
        $grid->model()->where(function ($query) use ($normal_status){
            $query->whereHas('apply', function ($query) use ($normal_status){
                $query->where('status',$normal_status);
            });
        })->orderBy('created_at','desc');
        $grid->filter(function($filter) use($regionArr,$sexArr){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->equal('id','id');
            $filter->equal('apply_id','报名id')->placeholder('根据报名id精确搜索');
            $filter->equal('activity_id','活动id')->placeholder('根据活动id精确搜索');
            $filter->like('activity.title','活动标题');
            $filter->equal('applicant.region_type','户籍')->select($regionArr);
            $filter->equal('applicant.sex','性别')->select($sexArr);
        });
        $grid->column('id')->sortable();
        $grid->column('apply_id','报名id');
        $grid->column('activity_id','活动id');
        $grid->column('activity.title','活动标题');
        $grid->column('apply.urgent_contact','紧急联系人');
        $grid->column('apply.phone','联系人手机号');
        $grid->column('applicant.region_type','户籍')->using($regionArr);
        $grid->column('applicant.name','姓名');
        $grid->column('applicant.sex','性别')->using($sexArr);
//        $grid->column('applicant.identity_number','身份证号');
//        $grid->column('applicant.phone','手机号');
        $grid->column('group_id','活动组别')->using($activityModel->getGroupType())->sortable();
        $grid->column('applicant.created_at','报名时间')->sortable();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });
        $grid->disableCreateButton();//去掉新增按钮
        $excel = new ApplicantExporter();
        $grid->exporter($excel);
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
        $show = new Show(Relevance::findOrFail($id));
        $applicantModel = new Applicant();
        $activityModel = new Activity();
        $regionArr = $applicantModel->getRegionType();
        $sexArr = $applicantModel->getSex();
        $carbonModel = new Carbon();
        $show->id('Id');
        $show->apply_id('报名id');
        $show->activity_id('活动标题')->as(function () use ($activityModel) {
            $activity_id = $this->activity_id;
            $title = $activityModel->where('id',$activity_id)->value('title');
            return $title;
        });
        $show->group_id('活动组别')->using($activityModel->getGroupType());
        $show->applicant('报名身份信息', function ($applicant) use ($regionArr,$sexArr,$carbonModel) {
            $applicant->setResource('/admin/applicants');
            $applicant->region_type('户籍')->using($regionArr);
            $applicant->name('姓名');
            $applicant->sex('性别')->using($sexArr);
            $applicant->identity_number('身份证号');
            $applicant->phone('电话');
            $applicant->birthday('出生年月')->as(function () use ($carbonModel) {
                $birthday = $this->birthday;
                if ($birthday) {
                    $new_birthday = $carbonModel->parse($birthday)->format('Y-m-d');
                    return $new_birthday;
                }
            });
            $applicant->created_at('报名时间');
            $applicant->panel()->tools(function ($tools) {
                $tools->disableDelete();
                $tools->disableEdit();
                $tools->disableList();
            });
        });
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
        $form = new Form(new Relevance);

        $form->number('activity_id', 'Activity id');
        $form->number('apply_id', 'Apply id');
        $form->number('applicant_id', 'Applicant id');
        $form->number('group_id', 'Group id');
        $form->switch('status', 'Status')->default(1);

        return $form;
    }
}
