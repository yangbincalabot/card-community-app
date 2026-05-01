<?php

namespace App\Admin\Controllers;

use App\Models\Advert;
use App\Http\Controllers\Controller;
use App\Models\AdvPosition;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Models\Activity\Activity;
use App\Models\Product;
use Illuminate\Http\Request;


class AdvertController extends Controller
{
    use HasResourceActions;

    const ACTIVITY_URL = '/pages/discover/detail/index?id='; // 活动详情地址
    const SUPPLY_URL = '/pages/product/detail/index?id='; // 商品详情地址

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('广告管理')
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
            ->header('广告管理')
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
            ->header('广告管理')
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
            ->header('广告管理')
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
        $grid = new Grid(new Advert);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', 'ID')->sortable();
        $grid->column('position.name', '广告位置');
        $grid->column('title', '标题');
        $grid->column('image', '图片')->image();
        $grid->column('url', '链接');
        $grid->column('sort', '排序')->editable();
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '编辑时间');

        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
        });

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->equal('adv_positions_id', '广告位置')->select(AdvPosition::selectOptions());
            $filter->like('title', '广告标题');
        });
        $grid->disableCreateButton();

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
        $show = new Show(Advert::findOrFail($id));

        $show->id('Id');
        $show->adv_positions_id('Adv positions id');
        $show->title('Title');
        $show->image('image');
        $show->url('Url');
        $show->sort('Sort');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Advert);

        $form->select('adv_positions_id', '广告位置')->options(AdvPosition::selectOptions())->rules('required');
        $form->text('title', '广告标题')->rules('required');
        $form->image('image', '图片')->uniqueName()->rules('required|image');
        $form->select('url_type', '链接类型')->options(Advert::getUrlTypes())->default(Advert::URL_TYPE_ACTIVITY)->load('url', route('advert.url.get'))->attribute(['id' => 'url_type']);

        // 解决编辑时没有默认选中链接的问题
        $id = request()->route()->parameters['advert'] ?? 0;
        if($id > 0){
            $form->select('url', '链接')->options(function () use ($id){
                $banner = Advert::find($id);
                $options = [];
                if($banner->url_type === Advert::URL_TYPE_ACTIVITY){
                    $options = self::getActivity(false);
                }elseif($banner->url_type === Advert::URL_TYPE_PRODUCT){
                    $options = self::getProduct(false);
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
        if($url_type === Advert::URL_TYPE_ACTIVITY){
            // 活动
            $data = self::getActivity();
        }elseif($url_type === Advert::URL_TYPE_PRODUCT){
            // 商品
            $data = self::getProduct();
        }
        return $data;
    }


    protected static function getActivity($is_ajax = true){
        $activitys = Activity::query()->where('edit_type', Activity::EDIT_TYPE_TWO)->latest()->get(['id', 'title']);
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

    protected static function getProduct($is_ajax = true){
        $products = Product::query()->where('on_sale', true)->latest()->get(['id', 'title']);
        $data = [];
        if($is_ajax){
            foreach ($products as $product){
                $data[] = [
                    'id' => self::SUPPLY_URL . $product->id,
                    'text' => $product->title
                ];
            }
        }else{
            foreach ($products as $product){
                $data[self::SUPPLY_URL . $product->id] = $product->title;
            }
        }
        return $data;

    }
}
