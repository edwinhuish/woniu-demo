<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});




// 带子查询
Route::get('test1', function () {
    $filter = '{"type":"group","logical":"and","collapsed":false,"children":[{"type":"field","table":"customer","field":"amount","value":1000,"operator":">","selectedValue":["customer","amount"]},{"type":"group","logical":"or","collapsed":false,"children":[{"type":"field","table":"customer_product","field":"status","value":1,"operator":"=","selectedValue":["customer_product","status"]},{"type":"field","table":"customer_product","field":"times","value":2,"operator":">","selectedValue":["customer_product","times"]}]}]}';
    $exclude = '';
    $parser = new \App\Helpers\ParseCdpField();
    return $parser->filter(json_decode($filter, true))->exclude(json_decode($exclude, true))->getSql();
});

// 带排除规则
Route::get('test2', function () {
    $filter = '{"type":"group","logical":"and","collapsed":false,"children":[{"type":"field","table":"customer","field":"name","value":"\u5f20","operator":"like","selectedValue":["customer","name"]}]}';
    $exclude = '{"type":"group","logical":"and","collapsed":false,"children":[{"type":"field","table":"customer","field":"sex","value":1,"operator":"=","selectedValue":["customer","sex"]}]}';
    $parser = new \App\Helpers\ParseCdpField();
    return $parser->filter(json_decode($filter, true))->exclude(json_decode($exclude, true))->getSql();
});

// 连表
Route::get('test3', function () {
    $filter = '{"type":"group","logical":"and","collapsed":false,"children":[{"type":"field","table":"customer","field":"medium_id","value":[22],"operator":"=","selectedValue":["customer","medium_id"]},{"type":"field","table":"customer_product","field":"status","value":1,"operator":"=","selectedValue":["customer_product","status"]}]}';
    $exclude = '';
    $parser = new \App\Helpers\ParseCdpField();
    return $parser->filter(json_decode($filter, true))->exclude(json_decode($exclude, true))->getSql();
});
