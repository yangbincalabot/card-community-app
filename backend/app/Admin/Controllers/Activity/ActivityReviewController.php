<?php

namespace App\Admin\Controllers\Activity;

use App\Models\Activity\Activity;
use App\Models\Activity\ActivityReview;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ActivityReviewController extends Controller
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
            ->header('活动回顾')
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
            ->header('活动回顾')
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
            ->header('活动回顾')
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
            ->header('活动回顾')
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
        $grid = new Grid(new ActivityReview);
        $reviewModel = new ActivityReview();
        $grid->model()->where('type',$reviewModel::TYPE_TWO)->orderBy('id','desc');
        $grid->id('Id');
        $grid->user_id('用户id');
        $grid->cover_image('封面图片')->image(config('app.url'),50,50);
        $grid->title('标题');
        $grid->status('状态')->using($reviewModel->getStatus());
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
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
        $show = new Show(ActivityReview::findOrFail($id));
        $reviewModel = new ActivityReview();
        $show->id('Id');
        $show->user_id('用户id');
        $show->cover_image('封面图片')->image(config('app.url'),200,200);
        $show->title('标题');
        $show->content('内容');
        $show->status('状态')->using($reviewModel->getStatus());
        $show->created_at('创建时间');
        $show->updated_at('更新时间');
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $reviewModel = new ActivityReview();
        $form = new Form($reviewModel);
        $id = isset(request()->route()->parameters()['activity_review']) ? request()->route()->parameters()['activity_review'] : null;
        $activityModel = new Activity();
        if ($id) {
            $form->select('user_id', '服务商')->options($activityModel->highUserSelect())->readonly();
        } else {
            $form->select('user_id', '服务商')->options($activityModel->highUserSelect())->rules('required');
        }
        $directory = 'uploads/'.date('Y').'/'.date('m').'/'.date('d');
        $form->image('cover_image', '封面图')->move($directory)->uniqueName()->rules('image');
        $form->text('title', '标题');
        $form->UEditor('content', '内容');
        $form->select('status', '状态')->options($reviewModel->getStatus());
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
