<?php

namespace App\Admin\Extensions;

use App\Http\Controllers\Controller;
use Encore\Admin\Admin;

class CheckRecommend extends Controller
{
    protected $id;
    protected $recommend;
    protected $actionUrl;
    protected $redirectUrl;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->recommend = $data['recommend'];
        $this->actionUrl = $data['actionUrl'];
        $this->redirectUrl = $data['redirectUrl'];
    }

    protected function script()
    {
        $actionUrl = $this->actionUrl;
        $redirectUrl = $this->redirectUrl;
        return <<<SCRIPT

$('.grid-check-row').on('click', function () {
    let _id = $(this).data('id');
    let _recommend = $(this).data('recommend');
    let _title = '你确定推荐此项内容吗？';
    if (_recommend == 1) {
        _title = '你确定取消推荐吗？';
    }
    swal({
        title: _title,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "确认",
        showLoaderOnConfirm: true,
        cancelButtonText: "取消",
        preConfirm: function() {
            return new Promise(function(resolve) {
                $.ajax({
                    method: 'post',
                    url: '{$actionUrl}',
                    data: {
                        id:_id,
                        _token:LA.token
                    },
                    success: function (data) {
                        $.pjax.reload('#pjax-container');
                        resolve(data);
                    }
                });
            });
        }
    }).then(function(result) {
        var data = result.value;
        if (typeof data === 'object') {
            if (data.status) {
                swal(data.message, '', 'success');
            } else {
                swal(data.message, '', 'error');
            }
        }
    });
    
});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return "<a class='grid-check-row' data-id='{$this->id}' data-recommend='{$this->recommend}'><i class='fa fa-paper-plane'></i></a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
