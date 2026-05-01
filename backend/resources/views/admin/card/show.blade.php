<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">名片信息：</h3>
        <div class="box-tools">
            <div class="btn-group float-right" style="margin-right: 10px">
                <a href="{{ route('user.index') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> 列表</a>
            </div>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>真实姓名：</td>
                <td>{{ $user->carte->name ?? '未填写' }}</td>
                <td>公司名称：</td>
                <td>{{ $user->carte->company_name ?? '未填写' }}</td>
            </tr>
            <tr>
                <td>手机号码：</td>
                <td>{{ $user->carte->phone ?? '未填写' }}</td>
                <td>微信号：</td>
                <td>{{ $user->carte->wechat ?? '未填写' }}</td>
            </tr>
            <tr>
                <td>邮箱地址：</td>
                <td>{{ $user->carte->email ?? '未填写' }}</td>
                <td>职位：</td>
                <td>{{ $user->carte->position ?? '未填写' }}</td>
            </tr>
            <tr>
                <td>所属行业：</td>
                <td>{{ ($user->carte && $user->carte->industry) ? $user->carte->industry->name : '未填写' }}</td>
                <td>名片访问量：</td>
                <td>{{ $user->carte->visits ?? 0 }}</td>
            </tr>
            <tr>
                <td>公司地址</td>
                <td colspan="3">{{ $user->carte->address_title ?? '未填写' }}</td>
            </tr>
            <tr>
                <td>公司简介</td>
                <td colspan="3">{{ $user->carte->introduction ?? '未填写' }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    @if($user->companyCardStatus)
        <br />
        <div class="box-header with-border">
            <h3 class="box-title">企业会员信息：</h3>
        </div>
        <div class="box-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td>企业名称：</td>
                        <td>{{ $user->companyCard->company_name ?? '未填写' }}</td>
                        <td>联系电话：</td>
                        <td>{{ $user->companyCard->contact_number ?? '未填写' }}</td>
                    </tr>
                    <tr>
                        <td>所属行业：</td>
                        <td>{{ ($user->companyCard->industry && $user->companyCard->industry->name) ? $user->companyCard->industry->name : '未填写' }}</td>
                        <td>企业官网：</td>
                        <td>{{ $user->companyCard->website ?? '未填写' }}</td>
                    </tr>
                    <tr>
                        <td>企业地址</td>
                        <td colspan="3">{{ $user->companyCard->address_title ?? '未填写' }}</td>
                    </tr>
                    <tr>
                        <td>企业简介</td>
                        <td colspan="3">{{ $user->companyCard->introduction ?? '未填写' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
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
                        url: '',
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
                        url: '',
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
