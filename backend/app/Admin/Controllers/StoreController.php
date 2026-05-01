<?php

namespace App\Admin\Controllers;

use App\Models\Area;
use App\Models\Store;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User\UserApplyAgent;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StoreController extends Controller
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
            ->header('门店管理')
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
            ->header('门店管理')
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
            ->header('门店管理')
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
            ->header('门店管理')
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
        $grid = new Grid(new Store);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', 'Id')->sortable();
        $grid->column('user.id', '用户编号');
        $grid->column('user.nickname', '用户昵称');
        $grid->column('user.type', '店铺类型')->display(function ($type){
            return User::userTypes($type);
        });
        $grid->column('name', '门店名称');
        $grid->column('contact_name', '联系人');
        $grid->column('contact_mobile', '联系电话');
        $grid->column('full_address', '详细地址');
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->equal('user_id', '申请人')->select(UserApplyAgent::getStoreSelectOptions());

        });
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
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
        $store = Store::findOrFail($id);
        $show = new Show($store);

        $show->user_id('用户昵称')->as(function () use ($store){
            return $store->user->nickname;
        });
        $show->name('门店名称');
        $show->contact_name('联系人');
        $show->contact_mobile('联系号码');
        $show->full_address('详细地址');
        $show->full_address('详细地址');
        $show->created_at('创建时间');
        $show->updated_at('编辑时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Store);
        $form->select('user_id', '所属代理商')->options(UserApplyAgent::getStoreSelectOptions())->help('审核通过的店中店和代理服务商')->rules('required');
        $form->text('name', '门店名称')->rules('required');
        $form->image('image', '封面图')->uniqueName()->rules('required|image');
        $form->multipleImage('images', '门店图片')->uniqueName()->sortable()->removable();
        $form->text('contact_name', '联系人')->rules('required');
        $form->mobile('contact_mobile', '联系号码')->rules('required');
        $form->distpicker(['province', 'city', 'district'])->attribute('data-value-type', 'code')->rules('required');
        $form->text('address', '详细地址')->rules('required')->append('<button type="button" id="search_btn">搜索</button>');
        $form->tencentMap('latitude', 'longitude', '经纬度')->fill(['latitude' => '22.6093910000', 'longitude' => '114.0293780000']);
        $form->hidden('full_address');


        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });

        $form->footer(function (Form\Footer $footer) {
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        });

        $form->saving(function (Form $form){
            $areaInfo = Area::query()->whereIn('code', [
                $form->province, $form->city, $form->district
            ])->pluck('name')->toArray();
            $areaInfo[] = $form->address;
            $form->full_address = implode('-', $areaInfo);
        });



        return $form;
    }

    private function getAreaInfo($province, $city, $district){
        return Area::whereIn('name', [$province, $city, $district])->get()->toArray();
    }

}
