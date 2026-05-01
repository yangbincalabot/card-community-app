<?php

namespace App\Admin\Extensions;

use App\Http\Controllers\Controller;
use Encore\Admin\Admin;

class Refund extends Controller
{
    protected $id;
    protected $actionUrl;
    protected $redirectUrl;

    public function __construct($data)
    {
        $this->id = $data['id'];
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
    let _title = '你确定给当前订单退款吗？';
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

        return "<a class='grid-check-row btn btn-primary' data-id='{$this->id}' >退款</a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
