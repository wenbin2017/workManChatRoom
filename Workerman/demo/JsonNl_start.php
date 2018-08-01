<?php
use Workerman\Worker;
require_once '../Autoloader.php';
$json_worker = new Worker('JsonNL://0.0.0.0:1234');
$json_worker->onMessage = function($connection, $data) {

    // $data就是客户端传来的数据，数据已经经过JsonNL::decode处理过
    echo $data;

    // $data = serialize($data);
    // $connection->send的数据会自动调用JsonNL::encode方法打包，然后发往客户端
    $connection->send(array('code'=>0, 'msg'=>'ok'));

};
Worker::runAll();