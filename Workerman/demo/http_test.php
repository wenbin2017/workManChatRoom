<?php
/*
**像在如php的tp laravel框架中，直接namespace use就能引入类。 因为是全部自动加载的。都在一个命名空间下
如果是单文件加载，像这样，就要先use 再include或者require
*/

// use Workerman\Worker;
// require_once '../Autoloader.php';

// // 创建一个Worker监听2345端口，使用http协议通讯
// $http_worker = new Worker("http://0.0.0.0:2345");

// // 启动4个进程对外提供服务
// $http_worker->count = 1;

// // 接收到浏览器发送的数据时回复hello world给浏览器
// $http_worker->onMessage = function($connection, $data)
// {	

//     // 向浏览器发送hello world
//     $connection->send(json_encode($data));
// };

// // 运行worker
// Worker::runAll();

use Workerman\Worker;
require_once '../Autoloader.php';

$worker = new Worker('http://127.0.0.1:8888');
$worker->count =  1;
$worker->onMessage = function($connection,$data){
	$connection->send('我是http协议');
};
Worker::runAll();
