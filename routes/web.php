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
    $filter = file_get_contents(__DIR__ . '/json/filter1.json');
    $exclude = '';
    $parser = new \App\Helpers\ParseCdpField();
    return $parser->filter(json_decode($filter, true))->exclude(json_decode($exclude, true))->getSql();
});

// 带排除规则
Route::get('test2', function () {
    $filter = file_get_contents(__DIR__ . '/json/filter2.json');
    $exclude = file_get_contents(__DIR__ . '/json/exclude2.json');
    $parser = new \App\Helpers\ParseCdpField();
    return $parser->filter(json_decode($filter, true))->exclude(json_decode($exclude, true))->getSql();
});

// 连表
Route::get('test3', function () {
    $filter = file_get_contents(__DIR__ . '/json/filter3.json');
    $exclude = '';
    $parser = new \App\Helpers\ParseCdpField();
    return $parser->filter(json_decode($filter, true))->exclude(json_decode($exclude, true))->getSql();
});
