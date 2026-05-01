<?php

namespace App\Admin\Controllers\Activity;

use App\Admin\Extensions\CheckRecommend;
use App\Models\Activity\Activity;
use App\Http\Controllers\Controller;
use App\Models\Carte;
use App\Models\Configure;
use App\Models\Undertake;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class MeetingController extends Controller
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
            ->header('会务管理')
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
            ->header('会务管理')
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
            ->header('会务管理')
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
            ->header('会务管理')
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
        $activityModel = new Activity();
        $grid = new Grid($activityModel);
        $statusArr = $activityModel->getStatus();
        $recommendStatusArr = $activityModel->getRecommendStatus();
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('id','会务id');
            $filter->like('title','会务标题');
        });
        $grid->model()->where('type',$activityModel::TYPE_TWO)->orderBy('created_at','desc');
        $grid->column('id','Id')->sortable();
        $grid->column('uid','用户id');
        $grid->column('cover_image','封面图')->image(100,100);
        $grid->column('title','标题');
        $grid->column('activity_time','会务时间');
        $grid->column('apply_end_time','报名截止时间');
        $grid->column('status','状态')->using($statusArr);
        $recommend_one = $activityModel::RECOMMEND_STATUS_ONE;
        $grid->column('recommend','推荐状态')->display(function ($recommend) use ($recommend_one, $recommendStatusArr){
            if ($recommend == $recommend_one) {
                return "<span style='color:red'>$recommendStatusArr[$recommend]</span>";
            }
            return $recommendStatusArr[$recommend];
        });
        $grid->column('created_at','创建时间');
//        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) use ($activityModel) {
            $data = $activityModel->getRecommendData($actions->row);
            $actions->prepend(new CheckRecommend($data));
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
        $show = new Show(Activity::findOrFail($id));
        $show->id('Id');
        $show->uid('用户id');
        $show->cover_image('封面图片')->image();
        $show->title('标题');
        $show->activity_time('会务时间');
        $show->apply_end_time('报名截止时间');
        $show->content('会务内容');
        $show->longitude('经度');
        $show->latitude('纬度');
        $show->address_title('地址标题');
        $show->address_name('地址简称');
        $show->created_at('创建时间');
        $show->updated_at('更新时间');
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableDelete();
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
        $activityModel = new Activity();
        $carteModel = new Carte();
        $form = new Form($activityModel);
        $id = isset(request()->route()->parameters()['meeting']) ? request()->route()->parameters()['meeting'] : null;
        if ($id) {
            $form->select('uid', '用户')->options($carteModel->getCarteAll())->readonly();
        } else {
            $form->select('uid', '用户')->options($carteModel->getCarteAll())->rules('required');
        }
        $form->image('cover_image', '封面图')->uniqueName()->rules(['image'])->removable();
        $form->hidden('type')->default($activityModel::TYPE_TWO);
        $form->text('title', '会务标题')->rules('required');
        $form->datetime('activity_time', '会务时间')->default(date('Y-m-d H:i:s'))->rules('required|date|after:apply_end_time',[
            'date' => '会务时间必须是日期格式',
            'after'   => '会务时间必须晚于报名截止时间',
        ]);
        $form->datetime('apply_end_time', '报名截止时间')->default(date('Y-m-d H:i:s'))->rules('required|date|after:tomorrow',[
            'date' => '报名截止时间必须是日期格式',
            'after'   => '报名截止时间必须在今天之后',
        ]);
        $presenterArr = $this->getPresenterCarteArr();
        $form->hasMany('agenda', '会务议程', function (Form\NestedForm $form) use ($id, $presenterArr) {
            $form->hidden('presenter')->addElementClass('presenter_hidden');
            $form->select('pid', '主讲人')->options($presenterArr)->rules('required')->addElementClass('presenter_selected');
            $form->text('title', '议题')->rules('required|max:30');
            $form->time('start_time', '开始时间')->format('HH:mm')->rules('required');
            $form->time('end_time', '结束时间')->format('HH:mm')->rules('required');
        });
        $form->textarea('content', '会务内容')->rules('required');
        $form->multipleImage('images', '详情图片')->uniqueName()->sortable()->removable();
        $form->hidden('address_title');
        $form->hidden('address_name');
        if (!$id) {
            $form->distpicker(['province', 'city', 'district'])->rules('required');
            $form->text('address', '详细地址')->rules('required')->append('<button type="button" id="search_btn">搜索</button>');
        } else {
            $form->text('address', '详细地址')->rules('required')->append('<button type="button" id="search_btn_nation">搜索</button>')->default(function (Form $form) use ($id,$activityModel){
                $result = $activityModel->where('id',$id)->select('id','address_title')->first();
                return $result->address_title;
            });
        }
        $form->tencentMap('latitude', 'longitude', '经纬度')->fill(['latitude' => '22.6093910000', 'longitude' => '114.0293780000']);
        $form->hasMany('specification', '会务规格', function (Form\NestedForm $form) use ($id) {
            $form->text('title', '规格名称')->rules('required');
            $form->text('stint', '限制人数')->rules('required|integer|min:1');
            $form->text('price', '价格')->rules('required|numeric|min:0');
        });
        $undartakeArr = $this->getImportantCarteArr();
        $underDefaultData = [];
        if ($id) {
            $underDefaultData = $this->getDefaultUndertakeArr($id);
        }
        $form->multipleSelect('undertake_select', '承办')->options($undartakeArr)->default($underDefaultData);
        $form->select('status', '审核状态')->options($activityModel->getStatus())->default(1);
//        $configure = new Configure();
//        $reviewStatus = $configure->getConfigure('ACTIVITY_VERIFY');
//        if ($reviewStatus == $configure::SUPPLY_DEMAND_YES) {
//            $form->select('status', '审核状态')->options($activityModel->getStatus());
//        } else {
//            $form->select('status', '审核状态')->options($activityModel->getStatus())->default(1)->readonly();
//        }
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });
        $form->ignore(['province','city','district','address', 'undertake_select'])->saving(function (Form $form) use ($id, $activityModel){
            if ($id) {
                $undertakeArr =  request()->get('undertake_select');
                $bool = $this->addUndertake($id, $undertakeArr);
                if (!$bool) {
                    $error = new MessageBag([
                        'title'   => '错误提示',
                        'message' => '添加承办人出错',
                    ]);
                    return back()->with(compact('error'))->withInput(request()->all());
                }
            }

            $agendaArr = request()->get('agenda');
            $checkAgendaStatus = $activityModel->checkAdminData($agendaArr);
            if (!$checkAgendaStatus) {
                $error = new MessageBag([
                    'title'   => '错误提示',
                    'message' => '请最少添加一个会务议程',
                ]);
                return back()->with(compact('error'))->withInput(request()->all());
            }
            $specArr = request()->get('specification');
            $checkSpeStatus = $activityModel->checkAdminData($specArr);
            if (!$checkSpeStatus) {
                $error = new MessageBag([
                    'title'   => '错误提示',
                    'message' => '请最少添加一个规格',
                ]);
                return back()->with(compact('error'))->withInput(request()->all());
            }
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
        });
        $form->saved(function (Form $form) use ($activityModel, $id) {
            $aid = $form->model()->id;
            $uid = $form->model()->uid;
            $activityModel->chengeSpe($aid, $uid);
            $activityModel->chengeAgenda($aid, $uid);
            if (!$id) {
                $undertakeArr =  request()->get('undertake_select');
                $this->addUndertake($aid, $undertakeArr);
            }
        });
        $script = <<<script
        $("body").on('change', '.presenter_selected', function(els){
            let name = $(this).find("option:selected").text();
            $(this).closest('.has-many-agenda-form').find('.presenter_hidden').val(name);
        })
script;

        Admin::script($script);

        return $form;
    }


    public function addUndertake($aid,$undertakeArr) {
        DB::beginTransaction();
        try{
            $undertakeModel = new Undertake();
            $carteModel = new Carte();
            // 先清空旧的承办单位数据
            $undertakeModel->where('aid', $aid)->delete();
            if (empty($undertakeArr)) {
                return true;
            }
            foreach ($undertakeArr as $v) {
                if (empty($v)) {
                    continue;
                }
                $oldRes = $carteModel->where('id',$v)->select('id', 'name', 'company_name')->first();
                if (empty($oldRes)) {
                    continue;
                }
                $createData['aid'] = $aid;
                $createData['cid'] = $v;
                $createData['name'] = $oldRes->name;
                $createData['company'] = $oldRes->company_name;
                $undertakeModel->create($createData);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getTrace());
            return false;
        }
        return true;
    }

    public function getDefaultUndertakeArr($id) {
        $undertakeModel = new Undertake();
        $res = $undertakeModel->where('aid',$id)->select('id', 'cid')->get();
        $newData = [];
        if (!empty($res)) {
            foreach ($res as $value) {
                $newData[] = $value->cid;
            }
        }
        return $newData;
    }

    // 获取主讲人
    public function getPresenterCarteArr() {
        $result = Carte::where('cid', '<>', 0)->select('id', 'uid', 'name', 'phone','company_name')->orderBy('id','asc')->get();
        $data = [];
        if (!$result->isEmpty()) {
            foreach ($result  as $value) {
                $data[$value->id] = $value->name;
            }
        }
        return $data;
    }

    // 获取关联公司的名片列表
    public function getImportantCarteArr() {
        $result = Carte::where('cid', '<>', 0)->select('id', 'uid', 'name', 'phone','company_name')->orderBy('id','asc')->get();
        $data = [];
        if (!$result->isEmpty()) {
            foreach ($result  as $value) {
                $data[$value->id] = $value->name.' ('.$value->company_name.')';
            }
        }
        return $data;
    }
}
