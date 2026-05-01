<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\Goods::class, function (Faker $faker) {
    $goods = [
        [
            'title' => '北欧风格小户型实木架客厅L型布艺沙发组合',
            'price' => 3480.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01XA5VR61ezwJQTzVyq_%21%212208156273943.jpg_430x430q90.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01XA5VR61ezwJQTzVyq_%21%212208156273943.jpg_430x430q90.jpg'],
            'content' => '北欧风格小户型实木架客厅L型布艺沙发组合',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '凯哲家具欧式真皮沙发 头层牛皮雕花实木客厅整装转角沙发组合',
            'price' => 7400.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01qlhc911CoCpoN0RC4_%21%212137460127.jpg_400x400.jpg_.webp',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01qlhc911CoCpoN0RC4_%21%212137460127.jpg_400x400.jpg_.webp'],
            'content' => '凯哲家具欧式真皮沙发 头层牛皮雕花实木客厅整装转角沙发组合',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '雅居汇 欧式真皮转角沙发实木雕花客厅家具组合美式真皮转角沙发',
            'price' => 18800.00	,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01kCLR1n23ff7G1lPJy_%21%211696287283.jpg_430x430q90.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01kCLR1n23ff7G1lPJy_%21%211696287283.jpg_430x430q90.jpg'],
            'content' => '凯哲家具欧式真皮沙发 头层牛皮雕花实木客厅整装转角沙发组合	',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '斯帝罗兰简约现代真皮沙发组合整装客厅转角头层牛皮三人沙发家具',
            'price' => 18800.00	,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1.jpg'],
            'content' => '斯帝罗兰简约现代真皮沙发组合整装客厅转角头层牛皮三人沙发家具',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '【新款】现顾家kuka北欧布艺沙发客厅大小户型现代简约组合2037',
            'price' => 2999.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O2.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O2.jpg'],
            'content' => '【新款】现顾家kuka北欧布艺沙发客厅大小户型现代简约组合2037',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '现顾家kuka布艺沙发客厅整装北欧家具现代简约布沙发2033',
            'price' => 4399.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/2.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/2.jpg'],
            'content' => '现顾家kuka布艺沙发客厅整装北欧家具现代简约布沙发2033',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '现顾家布艺沙发客厅整装现代简约可拆洗小户型皮布沙发B001-1',
            'price' => 4399.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/3.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/3.jpg'],
            'content' => '现顾家布艺沙发客厅整装现代简约可拆洗小户型皮布沙发B001-1',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '轩亿北欧布艺沙发大小户型组合客厅现代简约转角三人沙发整装家具',
            'price' => 2688.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/4.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/4.jpg'],
            'content' => '轩亿北欧布艺沙发大小户型组合客厅现代简约转角三人沙发整装家具',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '布艺沙发客厅整装现代简约123组合家具套装经济型沙发小户型一田',
            'price' => 1280.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/5.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/5.jpg'],
            'content' => '布艺沙发客厅整装现代简约123组合家具套装经济型沙发小户型一田',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '北欧沙发小户型双人简约日式布艺实木小型休闲简易卧室迷你单人椅',
            'price' => 127.80,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/6.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/6.jpg'],
            'content' => '北欧沙发小户型双人简约日式布艺实木小型休闲简易卧室迷你单人椅',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '洽谈桌椅组合简约休闲甜品奶茶店西餐咖啡厅双人卡座办公室布沙发',
            'price' => 200.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/7.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/7.jpg'],
            'content' => '洽谈桌椅组合简约休闲甜品奶茶店西餐咖啡厅双人卡座办公室布沙发',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '真皮办公室沙发茶几组合现代简约高档三人位商务家具接待会客特价',
            'price' => 1100.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/8.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/8.jpg'],
            'content' => '洽谈桌椅组合简约休闲甜品奶茶店西餐咖啡厅双人卡座办公室布沙发	',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '布艺沙发组合可拆洗简约现代客厅转角贵妃大小户L型沙发整装家具',
            'price' => 1280.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01kCLR1n23ff7G1lPJy_%21%211696287283.jpg_430x430q90.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01kCLR1n23ff7G1lPJy_%21%211696287283.jpg_430x430q90.jpg'],
            'content' => '布艺沙发组合可拆洗简约现代客厅转角贵妃大小户L型沙发整装家具	',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '办公沙发茶几组合商务接待小型沙发现代简约会客三人位办公室沙发',
            'price' => 497.80,
            'image' => 'https://yf.youfun.shop/storage/uploads/2020/05/09/517oCwEHJaZTghCQNoD2sJxvAGVGT845yP7Ev4wV.jpg',
            'images' => ['https://yf.youfun.shop/storage/uploads/2020/05/09/517oCwEHJaZTghCQNoD2sJxvAGVGT845yP7Ev4wV.jpg'],
            'content' => '办公沙发茶几组合商务接待小型沙发现代简约会客三人位办公室沙发',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
        [
            'title' => '可拆洗简约现代棉麻布沙发时尚大小户型客厅转角布艺沙发组合整装',
            'price' => 1468.00,
            'image' => 'https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01XA5VR61ezwJQTzVyq_%21%212208156273943.jpg_430x430q90.jpg',
            'images' => ['https://codecodify.oss-cn-shenzhen.aliyuncs.com/goods/O1CN01XA5VR61ezwJQTzVyq_%21%212208156273943.jpg_430x430q90.jpg'],
            'content' => '可拆洗简约现代棉麻布沙发时尚大小户型客厅转角布艺沙发组合整装',
            'sales' => $faker->randomDigit,
            'views' => $faker->randomDigit,
        ],
    ];
    $randGoods = $faker->randomElement($goods);
    $companies = \App\Models\CompanyCard::query()->where('company_name', '<>', '')->whereNotNull('uid')->get(['id', 'uid', 'company_name'])->toArray();
    $randCompany = $faker->randomElement($companies);
    $randGoods['user_id'] = $randCompany['uid'];
    $randGoods['cid'] = $randCompany['id'];
    return $randGoods;
});

