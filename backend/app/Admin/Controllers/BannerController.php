<?php

namespace App\Admin\Controllers;

use App\Models\Activity\Activity;
use App\Models\Banner;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supply;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use HasResourceActions;
    const ACTIVITY_URL = '/pages/discover/detail/index?id='; // 活动详情地址
    const SUPPLY_URL = '/pages/supply/detail/index?id='; // 供需详情地址


    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Banner管理')
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
            ->header('Banner管理')
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
            ->header('Banner管理')
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
            ->header('Banner管理')
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
        $grid = new Grid(new Banner);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', 'ID')->sortable();
        $grid->column('title', '标题');
        $grid->column('type', '类型')->display(function ($type){
            return Banner::getBannerTypes()[$type] ?? $type;
        });
        $grid->column('url', '链接');
        $grid->column('image', '图片')->image();
        $grid->column('sort', '排序')->editable();
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
        });

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->like('title', '标题');
            $filter->equal('type', '类型')->select(Banner::getBannerTypes());
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
        $show = new Show(Banner::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->images('image');
        $show->sort('Sort');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        $show->type('Type');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Banner);

        $form->text('title', '标题')->rules('required');;
        $form->image('image', '图片')->uniqueName()->rules('required|image');
        $form->select('type', '位置')->options(Banner::getBannerTypes());
        $form->select('url_type', '链接类型')->options(Banner::getUrlTypes())->default(Banner::URL_TYPE_ACTIVITY)->load('url', route('banner.url.get'))->attribute(['id' => 'url_type']);

        // 解决编辑时没有默认选中链接的问题
        $id = request()->route()->parameters['banner'] ?? 0;
        if($id > 0){
            $form->select('url', '链接')->options(function () use ($id){
                $banner = Banner::find($id);
                $options = [];
                if($banner->url_type === Banner::URL_TYPE_ACTIVITY){
                    $options = self::getActivity(false);
                }elseif($banner->url_type === Banner::URL_TYPE_SUPPLY_DEMAND){
                    $options = self::getSupplyDemand(false);
                }
                return $options;
            });
        }else{
            $form->select('url', '链接');
        }
        $form->number('sort', '排序')->default(0)->help('倒序排列');



        return $form;
    }

    public function getUrl(Request $request){
        $url_type = (int) $request->get('q');
        $data = [];
        if($url_type === Banner::URL_TYPE_ACTIVITY){
            // 活动
            $data = self::getActivity();
        }elseif($url_type === Banner::URL_TYPE_SUPPLY_DEMAND){
            $data = self::getSupplyDemand();
        }
        return $data;
    }


    // 获取小程序活动地址
    protected static function getActivity($is_ajax = true){
        $activitys = Activity::query()->where('status', Activity::STATUS_PASSED)->latest()->get(['id', 'title']);
        $data = [];
        if($is_ajax){
            foreach ($activitys as $activity){
                $data[] = [
                    'id' => self::ACTIVITY_URL . $activity->id,
                    'text' => $activity->title
                ];
            }
        }else{
            foreach ($activitys as $activity){
                $data[self::ACTIVITY_URL . $activity->id] = $activity->title;
            }
        }
        return $data;

    }

    // 获取小程序供需地址
    protected static function getSupplyDemand($is_ajax = true){
        $supplies = Supply::query()->where('status', Supply::STATUS_PASSED)->latest()->get(['id', 'content']);
        $data = [];
        if($is_ajax){
            foreach ($supplies as $supply){
                $data[] = [
                    'id' => self::SUPPLY_URL . $supply->id,
                    'text' => msubstr($supply->content, 0, 30)
                ];
            }
        }else{
            foreach ($supplies as $supply){
                $data[self::SUPPLY_URL . $supply->id] = msubstr($supply->content, 0, 30);
            }
        }
        return $data;

    }
}
