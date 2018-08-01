<?php
use Workerman\Worker;
use Workerman\Lib\Timer;
require_once __DIR__.'/Workerman/Autoloader.php';

//定义消息格式
/*$content = [
	'send_user' => '' ,// 发送者的昵称
	'recive_user' => '',//接受的昵称
	'content' => '',//消息主体
	'time'
	'type' => '' //login 登录消息  say 聊天消息  sys 系统消息
	'code' => '004'// 001昵称为空  002消息类型不正确 003发送给所有人消息 004自己的消息
];*/

// 将屏幕打印输出到Worker::$stdoutFile指定的文件中
Worker::$stdoutFile = './stdout.log';

$ws = new Worker("websocket://0.0.0.0:2347");

$ws->count = 1;

$ws->uidConnections = []; //做一个映射。name->连接
$ws->userList = []; //已连接客户端 用于给前端展示所有连接的用户


// 当客户端发送消息过来时，转发给所有人
function handle_message($connection, $message)
{
    global $ws;
    global $userList;
    $message = str_replace(PHP_EOL, '<br/>', $message); //将换行替换为前端可以显示的换行
    $data = json_decode($message,true);
    if(!empty($data)){
		if(empty($data['name'])){
	    	//如果没有昵称
	    	$mycontent = array(
	    		'content' => '请输入昵称!', 
	    		'type' => 'sys',
	    		'code' => '001'
	    	);
	    	return $connection->send(json_encode($mycontent));
	    }
	    
	    if(empty($data['type'])){
	    	//如果没有消息类型
	    	$mycontent = array(
	    		'content' => '消息不合法!', 
	    		'type' => 'sys',
	    		'code' => '002'
	    	);
	    	return $connection->send(json_encode($mycontent));
	    }

	    $name = $data['name']; 

	    //处理消息
	    $content = '';//返回的消息

	    switch ($data['type']) {
	    	case 'say':
	    		//发送给所有人 组装发送的数据
	    		$recive_user = 'all';//接收者
				$send_user = $data['name'];//发送者

				//拼装返回的数据结构
				$back_data = array(
					// 'nick' => $recive_user, 
					'send_user' => $name ,// 发送者的昵称
					'recive_user' => 'all',//接受的昵称
					'content' => $data['content'],
					'time' => date('Y-m-d H:i'),
					'type' => 'say', //login 登录消息  say 聊天消息  sys 系统消息
					'code' => '003'// 001昵称为空  002消息类型不正确  003发送给所有人消息
				);

				$mycontent = array(
					'send_user' => '你说' ,// 发送者的昵称
					'recive_user' => $name,//接受的昵称
					'content' => $data['content'],
					'time' => date('Y-m-d H:i'),
					'type' => 'say', //login 登录消息  say 聊天消息  sys 系统消息
					'code' => '004'// 001昵称为空  002消息类型不正确 003发送给所有人消息 004自己的消息
				);
	    		break;
	    	case 'prisay':
	    		
				//拼装返回的数据结构
				$back_data = array(
					// 'nick' => $recive_user, 
					'send_user' => $name ,// 发送者的昵称
					'recive_user' => $data['to_client'],//接受的昵称
					'content' => $data['content'],
					'time' => date('Y-m-d H:i'),
					'type' => 'say', //login 登录消息  say 聊天消息  sys 系统消息
					'code' => '003'// 001昵称为空  002消息类型不正确  003发送给所有人消息
				);

				$mycontent = array(
					'send_user' => '你说' ,// 发送者的昵称
					'recive_user' => $name,//接受的昵称
					'content' => $data['content'],
					'time' => date('Y-m-d H:i'),
					'type' => 'say', //login 登录消息  say 聊天消息  sys 系统消息
					'code' => '004'// 001昵称为空  002消息类型不正确 003发送给所有人消息 004自己的消息
				);
	    		break;

	    	case 'login':
	    		$recive_user = 'all';//接收者
	    		//保存用户信息。用户给前端展示
	    		if(in_array($name, $ws->userList)){
					//如果没有消息类型
	    			$mycontent = array('content' => '用户名已经存在!', 'type' => 'sys','code'=>'001');
	    			return $connection->send(json_encode($mycontent));
	    		}
		    	//添加映射
			    if(!isset($ws->uidConnections[$name])){
			    	$connection->name = $name;
			    	$ws->uidConnections[$name] =  $connection;
			    }
		    	$ws->userList[$name] = $name; //保存用户

		    	//发送所有人有人上线
		    	$content = '欢迎 <i>'.$name.'</i> 加入聊天室！    ';
		
				//拼装返回的数据结构
				$back_data = array(
					'send_user' => '<b style="color:red">系统：</b>', 
					'recive_user' => 'all',
					'content' => $content,
					'time' => date('Y-m-d H:i'),
					'clients' => $ws->userList, 
					'type' => 'login', //login 登录消息  say 聊天消息  sys 系统消息
					'code' => '003'// 001昵称为空  002消息类型不正确  003发送给所有人消息
				);
	    		break;
	    	default:
	    			//如果没有消息类型
    				$mycontent = array('content' => '消息不合法!', 'type' => 'sys','code'=>'002');
	    		break;
	    }

	    //发消息
	    if(isset($mycontent)){
	    	$connection->send(json_encode($mycontent));
	    }

	    if(isset($back_data)){
	    	if($back_data['recive_user'] == 'all'  ){
	    		if($back_data['type'] == 'say'){
	    			return sendMessageToAll($back_data,$connection);
	    		}else{
	    			return sendMessageToAll($back_data);
	    		}
	    	}
	    	return sendMessageToOne($data['to_client'], $back_data);
	    }
    }

}

$ws->onMessage = 'handle_message';

$ws->onClose = function ($connection) {
	global $ws;
	//删除映射和删除用户
	if(isset($connection->name)){
		$name = $connection->name;
		// echo $name;
		unset($ws->uidConnections[$name]);
		unset($ws->userList[$name]);

		//拼装返回的数据结构
		$back_data = array(
			'content' => $name.'悄悄的走了',  //消息内容
			'send_user' => '<b style="color:red">系统：</b>',  //发送方
			'client_id' => $connection->id,  //连接id
			'client_name' => $name,  //下线人名称
			'type' => 'login',  //消息类型
			'clients' => $ws->userList,  //所有在线用户
			'time' => date('Y-m-d H:i')
		);


		sendMessageToAll($back_data);
	}
	
	
};

//向所有在线用户推送消息
function sendMessageToAll($message, $connectionSelf = ''){
	global $ws;
	$message = json_encode($message);
	foreach ($ws->connections as $connection)
    {	if(empty($connectionSelf)){
			$connection->send($message);
    	}else{
    		if($connectionSelf->id != $connection->id){
    			$connection->send($message);
    		}
    	}
    	
   		
    }
}

//想$name用户发消息
function sendMessageToOne($name, $message){
	global $ws;
	if(isset($ws->uidConnections[$name])){
		if(is_array($message)){
 			$message = json_encode($message);
		}
		$ws->uidConnections[$name]->send($message);
	}
}

Worker::runAll();
?>