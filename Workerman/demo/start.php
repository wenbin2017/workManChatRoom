<?php
/**
即时通信例子
*/
use Workerman\Worker;
require '../Autoloader.php';

$worker = new Worker('text://0.0.0.0:2222');
$global_uid = 0;
// use ($result)
//当有人连接时，分配一个 uid
$worker->onConnect = function ($connection){
    global $worker, $global_uid;
    // 为这个连接分配一个uid
    $connection->uid = ++$global_uid;
    //遍历所有用户发送消息
    foreach ($worker->connections as $conn) {
       $conn->send('id-'.$connection->uid.'login');
    }
};

//收到消息时的处理函数
$worker->onMessage = function ($connection, $data)
{
    global $worker;
    foreach($worker->connections as $conn)
    {
        $conn->send("user[{$connection->uid}] said: $data");
    }
};

$worker->onClose = function ($connection){
    global $worker;
    foreach($worker->connections as $conn)
    {
        $conn->send("user[{$connection->uid}] logout");
    }
};

Worker::runAll();