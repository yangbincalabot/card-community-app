<?php

namespace App\Admin\Controllers;

use App\Models\Carte;
use App\Models\CompanyCard;
use App\Http\Controllers\Controller;
use App\Models\Industry;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;

class CompanyCardController extends Controller
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
            ->header('企业名片管理')
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
            ->header('企业名片管理')
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
            ->header('企业名片管理')
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
            ->header('企业名片管理')
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
        $grid = new Grid(new CompanyCard);
        $industryModel = new Industry();
        $grid->model()->where('company_name', '!=', '')->orderBy('id', 'desc');
        $grid->column('id','id')->sortable();
        $grid->column('uid','用户uid');
        $grid->column('company_name', '企业名称')->modal('关联用户', function ($model) {
            $comments = $model->carte()->get()->map(function ($comment) {
                return $comment->only(['id', 'name', 'phone']);
            });
            return new Table(['ID', '姓名', '电话'], $comments->toArray());
        });
        $grid->column('logo', '企业logo')->image(100,100);
        $grid->column('contact_number', '联系电话');
        $grid->column('industry_id', '行业类型')->display(function () use ($industryModel) {
            $value = $industryModel->where('id', $this->industry_id)->value('name');
            return $value ?? '';
        });
        $grid->column('address_title', '企业地址');
//        $grid->column('status', '状态');
        $grid->column('created_at');

        $grid->disableExport();
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
        $show = new Show(CompanyCard::findOrFail($id));
        $industryModel = new Industry();
        $show->id('Id');
        $show->uid('用户uid');
        $show->company_name('企业名称');
        $show->logo('企业logo')->image();
        $show->contact_number('联系电话');
        $show->industry_id('行业类型')->as(function () use ($industryModel) {
            $value = $industryModel->where('id', $this->industry_id)->value('name');
            return $value ?? '';
        });
        $show->introduction('企业简介');
        $show->website('企业官网');
//        $show->images('Images');
//        $show->longitude('Longitude');
//        $show->latitude('Latitude');
        $show->address_title('企业地址');
//        $show->address_name('Address name');
//        $show->status('状态');
        $show->created_at('创建时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $carteModel = new Carte();
        $companyCardModel = new  CompanyCard();
        $industryModel = new Industry();
        $form = new Form($companyCardModel);
        $id = isset(request()->route()->parameters()['company']) ? request()->route()->parameters()['company'] : null;
        if (!$id) {
            $form->select('uid', '用户')->options($carteModel->getCarteData())->rules('required');
        } else {
            $detail = $companyCardModel->where('id',$id)->select('id','address_title','industry_id')->first();
            $form->select('uid', '用户')->options($carteModel->getCarteAll())->readOnly();
        }
        $form->text('company_name', '企业名称')->rules('required');
        $form->image('logo', '企业logo')->uniqueName()->rules(['image'])->removable();
        $form->text('contact_number', '联系电话')->rules('required');
        if (!$id) {
            $form->select('industry_one', '行业一级分类')->options($companyCardModel->getIndustryArr())->load('industry_id', route('admin.industry.get'))->rules('required');
            $form->select('industry_id', '行业二级分类');
        } else {
            $oldIndustry = $industryModel::where('id', $detail->industry_id)->first();
            if (empty($oldIndustry)) {
                $form->select('industry_one', '行业一级分类')->options($companyCardModel->getIndustryArr())->load('industry_id', route('admin.industry.get'))->rules('required');
                $form->select('industry_id', '行业二级分类');
            } else {
                $one_id = $oldIndustry->parent_id ?:$oldIndustry->id;
                $form->select('industry_one', '行业一级分类')->options($companyCardModel->getIndustryArr())->default($one_id)->load('industry_id', route('admin.industry.get'))->rules('required');
                if ($oldIndustry->parent_id == 0) {
                    $form->select('industry_id', '行业二级分类');
                } else {
                    $parent_id = $oldIndustry->parent_id;
                    $form->select('industry_id', '行业二级分类')->options(function () use ($parent_id, $companyCardModel) {
                        $arr = $companyCardModel->getIndustryArr(2, $parent_id);
                        return $arr;
                    });
                }
            }

        }
        $form->textarea('introduction', '企业简介');
        $form->text('website', '企业官网');
        $form->multipleImage('images', '企业相册')->uniqueName()->sortable()->removable();

        $form->hidden('address_title');
        $form->hidden('address_name');
        $form->hidden('initial');
        if (!$id) {
            $form->distpicker(['province', 'city', 'district'])->rules('required');
            $form->text('address', '详细地址')->rules('required')->append('<button type="button" id="search_btn">搜索</button>');
        } else {
            $form->text('address', '详细地址')->rules('required')->append('<button type="button" id="search_btn_nation">搜索</button>')->default(function (Form $form) use ($id,$detail){
                return $detail->address_title;
            });
        }
        $form->ignore(['province','city','district','address', 'industry_one'])->saving(function (Form $form) use ($id, $companyCardModel){
            $address = request()->get('address');
            $province = request()->get('province');
            $city = request()->get('city');
            $district = request()->get('district');
            if ($id) {
                $pos =  mb_strripos($address,'县');
                if (!$pos) {
                    $pos =  mb_strripos($address,'区');
                }
                if (!$pos) {
                    $pos =  mb_strripos($address,'市');
                }
                $strpos = intval($pos+1);
                $form->address_name = mb_substr($address,$strpos);
                $form->address_title = $address;
            } else {
                $address_title = $province.$city.$district;
                $form->address_title = $address_title.$address;
                $form->address_name = $address;
            }
            $form->initial = getInitial(request()->get('company_name')); // 公司名首字母
            if (!$form->industry_id) {
                $form->industry_id = request()->get('industry_one');
            }
        });
        $form->tencentMap('latitude', 'longitude', '经纬度')->fill(['latitude' => '22.6093910000', 'longitude' => '114.0293780000']);
        return $form;
    }

    public function getIndustry(Request $request){
        $parent_id = (int) $request->get('q');
        $result = Industry::where('parent_id', $parent_id)->select('id', 'name')->get();
        $data = [];
        if (!$result->isEmpty()) {
            foreach ($result as $key => $value) {
                $data[$key]['id'] = $value->id;
                $data[$key]['text'] = $value->name;
            }
        }
        return $data;
    }
}
