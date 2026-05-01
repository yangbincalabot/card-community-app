<?php

namespace App\Admin\Controllers;

use App\Http\Requests\ConfigureRequest;
use App\Models\Association;
use App\Models\Configure;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Form;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

class ConfigureController extends Controller
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
        $content->header('协会管理');
        $content->description('网站设置');
        $this->showFormParameters($content);

        $tab = new Tab();
        $form_data = Configure::background();

        // 基本设置表单
        $base_form = new Form($form_data);
        $base_form->method('POST');
        $base_form->action(route('configure.store'));
//        $base_form->text('SOCIETY_NAME', '协会名称');
//        $base_form->image('SOCIETY_LOGO', '协会Logo');
        $base_form->rate('SETTLE_RATE', '结算比例');
        $base_form->number('SETTLE_TIME', '结算时间(天)')->min(0)->default(0)->help('为0表示活动结束后立即结算');
        $base_form->radio('ACTIVITY_VERIFY', '发布活动是否需要审核')->options(Configure::getActivity())->default(Configure::ACTIVITY_VERIFY_NO);
        $base_form->radio('SUPPLY_DEMAND', '发布供需是否需要审核')->options(Configure::getSupplyDemand())->default(Configure::SUPPLY_DEMAND_NO);
        $base_form->number('SCAN_NUMS', '一天扫描名片次数限制')->min(0)->default(10)->help('为0表示不限制次数');
        $base_form->number('SMS_NUMS', '一天发送短信次数限制')->min(0)->default(10)->help('为0表示不限制次数');
        $base_form->radio('SMS_SWITCH', '是否开启短信')->options(Configure::getSmsSwitch())->default(Configure::SMS_CLOSE);
        $base_form->currency('BUSINESS_COST', '开通企业会员费用')->default(0)->symbol('￥');

        $base_form->radio('IS_AUDIT', '小程序是否审核')->options(Configure::getAudits())->default(Configure::IS_AUDIT_NO);
        $tab->add('基本设置', $base_form);


        // 平台协会
        $platform = Association::getPlatform();
        if($platform && (is_array($platform) || $platform instanceof Arrayable)){
            foreach ($platform as $value){
                $keyName = sprintf('ASSOCIATION_NAME_%d', $value->id); // 协会名称
                $keyImage = sprintf('ASSOCIATION_IMAGE_%d', $value->id);  // 协会图片
                $keyDesc = sprintf('ASSOCIATION_DESC_%d', $value->id); // 协会介绍

                $form_data = array_merge($form_data, [$keyName => $value->name, $keyImage => $value->image, $keyDesc => $value->desc]);
            }
        }
        $assocForm = new Form($form_data);
        $assocForm->method('POST');
        $assocForm->action(route('configure.store'));


        if($platform instanceof Collection && $platform->count() > 0){
            $index = 0;
            foreach ($form_data as $key => $value){
                if (Str::contains($key, 'ASSOCIATION_NAME_')){
                    $imageKey = str_replace('_NAME', '_IMAGE', $key);
                    $descKey = str_replace('_NAME', '_DESC', $key);
                    if ($index === 0){
                        $assocForm->html('<h3 class="assoc-title">默认协会</h3>');
                        $assocForm->divide();
                        $assocForm->text($key, '协会名称');
                        $assocForm->image($imageKey, '协会Logo');
                        $assocForm->textarea($descKey, '协会介绍');
                        $index += 1;
                    }else{
                        if ($index === 1){
                            $assocForm->html('<br/>');
                            $assocForm->html('<br/>');
                        }

                        $assocForm->html(sprintf('<h3 class="assoc-title">其它协会%d</h3>', $index));
                        $assocForm->divide();
                        $assocForm->text($key, '协会名称');
                        $assocForm->image($imageKey, '协会Logo');
                        $assocForm->textarea($descKey, '协会介绍');
                        if ($index === 1){
                            $assocForm->html('<br/>');
                            $assocForm->html('<br/>');
                            $index += 1;
                        }
                    }

                }
            }
        }else{

            $assocForm->html('<h3 class="assoc-title">默认协会</h3>');
            $assocForm->divide();
            $assocForm->text('ASSOCIATION_NAME_DEFAULT', '协会名称');
            $assocForm->image('ASSOCIATION_IMAGE_DEFAULT', '协会Logo');
            $assocForm->textarea('ASSOCIATION_DESC_DEFAULT', '协会介绍');

            $assocForm->html('<br/>');
            $assocForm->html('<br/>');



            $assocForm->html('<h3 class="assoc-title">其它协会1</h3>');
            $assocForm->divide();
            $assocForm->text('ASSOCIATION_NAME_FIRST', '协会名称');
            $assocForm->image('ASSOCIATION_IMAGE_FIRST', '协会Logo');
            $assocForm->image('ASSOCIATION_DESC_FIRST', '协会介绍');



            $assocForm->html('<br/>');
            $assocForm->html('<br/>');

            $assocForm->html('<h3 class="assoc-title">其它协会2</h3>');
            $assocForm->divide();
            $assocForm->text('ASSOCIATION_NAME_SECOND', '协会名称');
            $assocForm->image('ASSOCIATION_IMAGE_SECOND', '协会Logo');
            $assocForm->image('ASSOCIATION_DESC_SECOND', '协会介绍');

        }


        $tab->add('平台协会', $assocForm);



        $content->row($tab);
        return $content;
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function store(ConfigureRequest $request)
    {
        $formData = $request->except('_token');
        DB::beginTransaction();
        try{
            foreach ($formData as $key => $value){
                // 图片处理
                if(in_array($key, ['SOCIETY_LOGO']) && $value){
                    $value = $request->file($key)->store('images', 'public');
                    $value = imageRealPath($value);
                }

                // 协会字段处理
                if (Str::contains($key, 'ASSOCIATION') && !Str::contains($key, ['_IMAGE', '_DESC']) && $value){
                    $imageKey = str_replace('_NAME', '_IMAGE', $key);
                    $descKey = str_replace('_NAME', '_DESC', $key);
                    $image = $formData[$imageKey] ?? '';
                    $desc = $formData[$descKey] ?? '';
                    if ($image instanceof UploadedFile){
                        $image = $this->uploadImage($request, $imageKey);
                    }


                    // 查看是否有数值
                    preg_match('/(\d+)/', $key, $id);
                    if ($id){
                        $updateData = ['name' => $value,  'desc' => $desc];
                        if ($image){
                            $updateData['image'] = $image;
                        }

                        Association::query()->where('id', $id)->update($updateData);

                    }else{
                        Association::query()->create([
                            'user_id' => 0,
                            'name' => $value,
                            'image' => $image,
                            'status' => Association::STATUS_SUCCESS,
                            'desc' => $desc,
                        ]);
                    }
                }

                if (Str::contains($key, 'ASSOCIATION')){
                    continue;
                }
                Configure::query()->updateOrCreate(['name' => $key], ['value' => $value !== null ? $value : '']);
            }

            DB::commit();


            $success = new MessageBag([
                'title' => '更新网站配置',
                'message' => '更新成功'
            ]);
            return back()->with(compact('success'));
        }catch (\Exception $exception){
            \Log::error($exception->getMessage());
            DB::rollBack();

            $error = new MessageBag([
                'title' => '更新网站配置',
                'message' => '更新失败，' . $exception->getMessage()
            ]);
            return back()->with(compact('error'));
        }
    }


    protected function showFormParameters(Content $content)
    {
        $parameters = request()->except(['_pjax', '_token']);
        if (!empty($parameters)) {
            ob_start();
            dump($parameters);
            $contents = ob_get_contents();
            ob_end_clean();
            $content->row(new Box('Form parameters', $contents));
        }
    }


    private function uploadImage(Request $request, $imageKey){
        $file = $request->file($imageKey);
        $image = '';
        if ($file){
            $image = $request->file($imageKey)->store('images', 'public');
        }
        return $image;
    }
}
