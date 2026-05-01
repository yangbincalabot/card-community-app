<?php

namespace App\Admin\Controllers;

use App\Models\Configure;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\MessageBag;

class IntroductionController extends Controller
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
        $tab = new Tab();
        $form_data =  Configure::where('name', 'SOCIETY_INTRODUCTION')->pluck('value', 'name')->toArray();
        $base_form = new Form($form_data);
        $base_form->method('POST');
        $base_form->action(route('introduction.store'));
        $base_form->UEditor('SOCIETY_INTRODUCTION', '协会介绍');
        $tab->add('协会介绍', $base_form);
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
    public function store(Request $request)
    {
        $formData = $request->except('_token');
        $value = $formData['SOCIETY_INTRODUCTION'];
        DB::beginTransaction();
        try{
            Configure::where('name', 'SOCIETY_INTRODUCTION')->update(['value' => $value]);
            DB::commit();
            $success = new MessageBag([
                'title' => '更新协会介绍',
                'message' => '更新成功'
            ]);
            return back()->with(compact('success'));
        } catch (\Exception $exception) {
            DB::rollBack();
            $error = new MessageBag([
                'title' => '更新协会介绍',
                'message' => '更新失败，' . $exception->getMessage()
            ]);
            return back()->with(compact('error'));
        }
    }

}
