<?php
namespace Protocols;
/*
协议定义
首部固定10个字节长度用来保存整个数据包长度，位数不够补0
数据格式为xml

如：
0000000121<?xml version="1.0" encoding="ISO-8859-1"?>
<request>
    <module>user</module>
    <action>getInfo</action>
</request>
*/

class XmlProtocols {
	public static function input($value)
	{
		if(strlen($value) < 10){
//不够十个继续等待
			return 0;
		}

		//返回包长
		$totle_length = base_convert(substr($value, 0,10), 10, 10) ;
		return $totle_length;
	}

	//接受消息时，自动调用方法解码
	public static function decode($recv_buffer){
		// 请求包体
        $body = substr($recv_buffer, 10);
        return simplexml_load_string($body); //转换为对象
	}

	//发送消息时，打包
	public static function encode($recv_buffer){
		$total_length = strlen($xml_string)+10;
        // 长度部分凑足10字节，位数不够补0
        $total_length_str = str_pad($total_length, 10, '0', STR_PAD_LEFT);
        // 返回数据
        return $total_length_str . $xml_string;
	}
}