<?php

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function ngrok_url($routeName, $parameters = [])
{
    // 开发环境，并且配置了 NGROK_URL
    if(app()->environment('local') && $url = config('app.ngrok_url')) {
        // route() 函数第三个参数代表是否绝对路径
        return $url.route($routeName, $parameters, false);
    }

    return route($routeName, $parameters);
}

// 默认的精度为小数点后两位
function big_number($number, $scale = 2)
{
    return new \Moontoast\Math\BigNumber($number, $scale);
}

if(!function_exists('msubstr')){
    /*
	* 中文截取，支持gb2312,gbk,utf-8,big5
	* @param string $str 要截取的字串
	* @param int $start 截取起始位置
	* @param int $length 截取长度
	* @param string $charset utf-8|gb2312|gbk|big5 编码
	* @param $suffix 是否加尾缀
	*/
    function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true){
        if(function_exists("mb_substr")){
            if(mb_strlen($str, $charset) <= $length) return $str;
            $slice = mb_substr($str, $start, $length, $charset);
        }else{
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            if(count($match[0]) <= $length) return $str;
            $slice = join("", array_slice($match[0], $start, $length));
        }
        if($suffix) return $slice . "…";
        return $slice;
    }
}


if(!function_exists('moneyShow')){
    /**
     * 金额显示
     * @param $money
     * @param int $precision
     * @return string
     */
    function moneyShow($money, $precision = 2){
//        if((!is_numeric($precision)) || empty($precision) || ($precision < 0)){
//            $precision = 2;
//        }
//        return strval(sprintf('%01.'. $precision .'f', $money));
        $moneyParse = new \Moontoast\Math\BigNumber($money, $precision);
        return $moneyParse->getValue();
    }
}

if(!function_exists('createOrderNo')){
    /**
     * 创建订单编号
     */
    function createOrderNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);;
        return $no;
    }
}

if(!function_exists('hideStr')){
    /**
    +----------------------------------------------------------
     * 将一个字符串部分字符用*替代隐藏
    +----------------------------------------------------------
     * @param string    $string   待转换的字符串
     * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
     * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
     * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
     * @param string    $glue     分割符
    +----------------------------------------------------------
     * @return string   处理后的字符串
    +----------------------------------------------------------
     */
    function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
        if (empty($string))
            return false;
        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($type == 0) {
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", $array);
        } else if ($type == 1) {
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", array_reverse($array));
        } else if ($type == 2) {
            $array = explode($glue, $string);
            $array[0] = hideStr($array[0], $bengin, $len, 1);
            $string = implode($glue, $array);
        } else if ($type == 3) {
            $array = explode($glue, $string);
            $array[1] = hideStr($array[1], $bengin, $len, 0);
            $string = implode($glue, $array);
        } else if ($type == 4) {
            $left = $bengin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i]))
                    $tem[] = $i >= $left ? "*" : $array[$i];
            }
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                $tem[] = $array[$i];
            }
            $string = implode("", $tem);
        }
        return $string;
    }
}

if(!function_exists('match_mobile')){
    /**
     * 检查手机号码
     * @param $mobile
     * @return bool
     */
    function match_mobile($mobile){
        if (preg_match("/^1[3-9]{1}[0-9]{9}$/Uis", $mobile)) {
            return true;
        }else{
            return false;
        }
    }
}

if(!function_exists('match_phone')){
    /**
     * 检查座机号码
     * @param $phone
     * @return bool
     */
    function match_phone($phone){
        if (preg_match("/(^[0-9]{2,5}\-[0-9]{7,9})((\-\d{0,10})?)$/Uis", $phone)) {
            return true;
        }else{
            return false;
        }
    }
}

if(!function_exists('getInitial')){
    /**
     * 获取首字母
     * @param $name
     * @return string
     */
    function getInitial($name){
        return mb_substr(strtoupper(app('pinyin')->abbr($name ?: '#')), 0, 1);
    }
}

if(!function_exists('imageRealPath')){
    /**
     * 图片路径
     * @param $file
     * @return mixed
     */
    function imageRealPath($file){
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (\Illuminate\Support\Str::startsWith($file, ['http://', 'https://'])) {
            return $file;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->url($file);
    }
}

if(!function_exists('getSmsCode')){
    function getSmsCode($size = 6){
        $minNum = intval(1 . str_repeat('0', $size - 1));
        $maxNum = intval(str_repeat('9', $size));
        $code = random_int($minNum, $maxNum);
        return $code;
    }
}

if (!function_exists('getletterAvatar')) {
    /**
     * 字母头像 （按照名称首位生成默认头像)
     * @param $text
     * @return string
     */
    function getletterAvatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
//        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);
        list($r, $g, $b) = [43,140,249];

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="40" text-copy="kl" fill="' . $color . '" text-anchor="middle" text-rights="admin" alignment-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('msgSecCheck')) {
    function msgSecCheck($content) {
        if (empty($content)) {
            return false;
        }
        $ContentSecCheckService = new \App\Services\ContentSecCheckService();
        $res = $ContentSecCheckService->msgCheck($content);
        $data = json_decode($res, true);
        if (!empty($data) && $data['errcode'] == 0) {
            return true;
        } else {
            return false;
        }
    }

}
