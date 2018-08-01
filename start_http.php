<?php
use Workerman\Worker;
use Workerman\WebServer;
require_once './Workerman/Autoloader.php';

// 创建一个Worker监听2345端口，使用http协议通讯
$http_worker = new WebServer("http://0.0.0.0:2348");

// 启动4个进程对外提供服务
$http_worker->count = 1;
// 设置站点根目录
$http_worker->addRoot('www.ssss.com', __DIR__.'/');


// 运行worker
Worker::runAll();