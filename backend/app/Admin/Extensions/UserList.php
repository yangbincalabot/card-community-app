<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/3
 * Time: 10:02
 */

namespace App\Admin\Extensions;
use Encore\Admin\Grid\Tools\AbstractTool;

class UserList extends AbstractTool
{
    public function render()
    {
        // TODO: Implement render() method.
        return view('admin.tools.userList');
    }
}