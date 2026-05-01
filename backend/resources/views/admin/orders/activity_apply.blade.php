<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">订单流水号：{{ $order->order_no }}</h3>
    <div class="box-tools">
      <div class="btn-group float-right" style="margin-right: 10px">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> 列表</a>
      </div>
    </div>
  </div>
  <div class="box-body">
    <table class="table table-bordered">
      <tbody>
      <tr>
        <td>买家：</td>
        <td>{{ $order->user->name }}</td>
        <td>通知时间：</td>
        <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
      </tr>
      <tr>
        <td>支付方式：</td>
        <td>{{ $order->payment_method }}</td>
        <td>支付渠道单号：</td>
        <td>{{ $order->payment_no }}</td>
      </tr>



      <tr>
        <td>订单金额：</td>
        <td>￥{{ $order->total_price }}</td>
        <td>订单状态：</td>
        <td>{{ $order->status_cn }}</td>
      </tr>




      <!-- 订单如果已经支付，且活动未开始，则可以申请退款 -->
      @if( ($order->status == \App\Models\Activity\ActivityApply::STATUS_ONE) &&  ($order->total_price > 0) && ($order->paid_at) )
        <tr>
          <td>退款审核：</td>
          <td>
            <!-- 如果订单退款状态是已申请，则展示处理按钮 -->
            @if($order->refund_status === \App\Models\Activity\ActivityApply::REFUND_STATUS_APPLIED)
              <button class="btn btn-sm btn-success" id="btn-refund-agree">同意</button>
              <button class="btn btn-sm btn-danger" id="btn-refund-disagree">不同意</button>
            @endif
          </td>
        </tr>

      @endif
      </tbody>
    </table>
  </div>
</div>

<script>
  $(document).ready(function() {
    // 不同意 按钮的点击事件
    $('#btn-refund-disagree').click(function() {
      // Laravel-Admin 使用的 SweetAlert 版本与我们在前台使用的版本不一样，因此参数也不太一样
      swal({
        title: '输入拒绝退款理由',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: "确认",
        cancelButtonText: "取消",
        showLoaderOnConfirm: true,
        preConfirm: function(inputValue) {
          if (!inputValue) {
            swal('理由不能为空', '', 'error')
            return false;
          }
          // Laravel-Admin 没有 axios，使用 jQuery 的 ajax 方法来请求
          return $.ajax({
            url: '{{ route('admin.apply_refund.handle_refund', [$order->id]) }}',
            type: 'POST',
            data: JSON.stringify({   // 将请求变成 JSON 字符串
              agree: false,  // 拒绝申请
              reason: inputValue,
              // 带上 CSRF Token
              // Laravel-Admin 页面里可以通过 LA.token 获得 CSRF Token
              _token: LA.token,
            }),
            contentType: 'application/json',  // 请求的数据格式为 JSON
          });
        },
        allowOutsideClick: false
      }).then(function (ret) {
        // 如果用户点击了『取消』按钮，则不做任何操作
        if (ret.dismiss === 'cancel') {
          return;
        }
        swal({
          title: '操作成功',
          type: 'success'
        }).then(function() {
          // 用户点击 swal 上的按钮时刷新页面
          location.reload();
        });
      });
    });

    // 同意 按钮的点击事件
    $('#btn-refund-agree').click(function() {
      swal({
        title: '确认要将款项退还给用户？',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: "确认",
        cancelButtonText: "取消",
        showLoaderOnConfirm: true,
        preConfirm: function() {
          return $.ajax({
            url: '{{ route('admin.apply_refund.handle_refund', [$order->id]) }}',
            type: 'POST',
            data: JSON.stringify({
              agree: true, // 代表同意退款
              _token: LA.token,
            }),
            contentType: 'application/json',
          });
        },
        allowOutsideClick: false
      }).then(function (ret) {
        // 如果用户点击了『取消』按钮，则不做任何操作
        if (ret.dismiss === 'cancel') {
          return;
        }
        swal({
          title: '操作成功',
          type: 'success'
        }).then(function() {
          // 用户点击 swal 上的按钮时刷新页面
          location.reload();
        });
      });
    });

  });
</script>
