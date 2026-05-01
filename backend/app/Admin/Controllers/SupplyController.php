<?php

namespace App\Admin\Controllers;

use App\Models\Carte;
use App\Models\Configure;
use App\Models\SdType;
use App\Models\Supply;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SupplyController extends Controller
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
            ->header('供需')
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
            ->header('供需')
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
            ->header('供需')
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
            ->header('供需')
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
        $supply = new Supply();
        $sdType = new SdType();
        $typeArr = $sdType->getParentType('',true);
        $grid = new Grid($supply);
        $grid->filter(function($filter) use($typeArr){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('id','供需id');
            $filter->equal('type','活动类型')->select($typeArr);
        });
        $grid->model()->orderBy('created_at','desc');
        $grid->id('id');
        $grid->uid('名片姓名')->display(function () use ($supply) {
            $res = $supply->getCarte($this->uid);
            return $res->name ?? '/';
        });
        $grid->type('类型')->using($typeArr);
        $grid->content('供需内容')->display(function () {
            return str_limit($this->content, 20);
        });
//        $grid->images('Images');
        $grid->visits('浏览量');
        $grid->status('状态')->using($supply->getStatus());
        $grid->created_at('发布时间');
        // 目前隐藏新增按钮
//        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
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
        $supply = new Supply();
        $sdType = new SdType();
        $typeArr = $sdType->getParentType('',true);
        $show = new Show($supply::findOrFail($id));
        $show->id('id');
        $show->uid('名片姓名')->as(function () use ($supply) {
            $carte = $supply->getCarte($this->uid);
            return $carte->name ?? '/';
        });
        $show->type('Type')->using($typeArr);
        $show->content('供需内容');
//        $show->images('Images');
        $show->visits('浏览量');
        $show->status('状态')->using($supply->getStatus());
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
        $supply = new Supply();
        $sdType = new SdType();
        $carteModel = new Carte();
        $typeArr = $sdType->getParentType('',true);
        $form = new Form($supply);
        $id = isset(request()->route()->parameters()['supply']) ? request()->route()->parameters()['supply'] : null;
        if ($id) {
            $form->select('uid', '用户')->options($carteModel->getCarteAll())->readonly();
        } else {
            $form->select('uid', '用户')->options($carteModel->getCarteAll())->rules('required');
        }
        $form->select('type', '分类')->options($typeArr)->default(1)->rules('required');
        $form->textarea('content', '供需内容');
        $form->multipleImage('images', '图片')->uniqueName()->sortable()->removable();
        $form->select('status', '状态')->options($supply->getStatus())->default(1);
//        $configure = new Configure();
//        $reviewStatus = $configure->getConfigure('SUPPLY_DEMAND');
//        if ($reviewStatus == $configure::SUPPLY_DEMAND_YES) {
//            $form->select('status', '状态')->options($supply->getStatus());
//        } else {
//            $form->select('status', '状态')->options($supply->getStatus())->default(1)->readonly();
//        }
        return $form;
    }
}
