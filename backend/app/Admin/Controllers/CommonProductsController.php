<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SyncOneProductToES;
use App\Models\Product;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use App\Models\Category;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Models\Product\ProductSkuCategory;

abstract class CommonProductsController extends Controller
{
    use HasResourceActions;

    // 定义一个抽象方法，返回当前管理的商品类型
    abstract public function getProductType();

    public function index(Content $content)
    {
        return $content
            ->header(Product::$typeMap[$this->getProductType()].'列表')
            ->body($this->grid());
    }

    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑'.Product::$typeMap[$this->getProductType()])
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->header('创建'.Product::$typeMap[$this->getProductType()])
            ->body($this->form());
    }

    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('title', '商品名称');
            $category = Category::where('parent_id',1)->pluck('name','id')->toArray();
            $filter->equal('category_id','商品类型')->select($category);

            $filter->equal('on_sale','商品状态')->select([
                0 => '未上架',
                1 => '已上架',
            ]);
        });

        // 筛选出当前类型的商品，默认 ID 倒序排序
        $grid->model()->where('type', $this->getProductType())->orderBy('id', 'desc');
        // 调用自定义方法
        $this->customGrid($grid);

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {

            });
        });

        return $grid;
    }

    // 定义一个抽象方法，各个类型的控制器将实现本方法来定义列表应该展示哪些字段
    abstract protected function customGrid(Grid $grid);

    protected function form()
    {
        $form = new Form(new Product());
        // 在表单页面中添加一个名为 type 的隐藏字段，值为当前商品类型
        $form->hidden('type')->value($this->getProductType());
        $form->text('title', '商品名称')->rules('required');

        $form->text('price', '单价')->rules('required|numeric|min:0.01');
        $form->text('original_price', '原价')->rules('required|numeric|min:0.01|gte:price');
        $form->text('cost_price', '成本价')->rules('required|numeric|min:0.01|lte:qyfws_price');
        $form->text('yjdl_price', '店中店价格')->rules('required|numeric|min:0.01|lte:price');
        $form->text('qydl_price', '区域代理商价格')->rules('required|numeric|min:0.01|lte:yjdl_price');
        $form->text('qyfws_price', '区域服务商价格')->rules('required|numeric|min:0.01|lte:qydl_price');

        // 放在商品名称后面
        $form->text('long_title', '商品长标题')->rules('required');
        $category = Category::where('parent_id',1)->pluck('name','id')->toArray();
        $form->select('category_id', '类目')->options($category);

        $form->image('image', '封面图片')->uniqueName()->rules('required|image');

        // 添加删除按钮
        $form->multipleImage('images', '商品图片')->uniqueName()->sortable()->removable()->rules('required');
        $form->UEditor('description', '商品描述')->rules('required');
        $form->radio('on_sale', '上架')->options(['1' => '是', '0' => '否'])->default('0');

        // 调用自定义方法
        $this->customForm($form);

        $productSkuCategory = ProductSkuCategory::pluck('title','id')->toArray();
        $form->hasMany('skus', '商品规格', function (Form\NestedForm $form) use($productSkuCategory) {
            $form->select('sku_category_id', '所属商品属性类目')->options($productSkuCategory)->rules('required');
            $form->text('title', '规格名称')->rules('required');
            $form->text('description', '规格描述');
            $form->text('stock', '剩余库存')->rules('required|integer|min:0');
        });
        // 放在 SKU 下面
        /*$form->hasMany('properties', '商品属性', function (Form\NestedForm $form) {
            $form->text('name', '属性名')->rules('required');
            $form->text('value', '属性值')->rules('required');
        });*/

        return $form;
    }

    // 定义一个抽象方法，各个类型的控制器将实现本方法来定义表单应该有哪些额外的字段
    abstract protected function customForm(Form $form);
}
