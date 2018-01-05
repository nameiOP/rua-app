<?php


namespace rsk\event\connect;


class readEvent extends connectEvent{



    //socket_read
    const SOCKET_READ = 'CONNECT_SOCKET_READ';


    //socke_receive
    const SOCKET_RECEIVE = 'CONNECT_SOCKET_RECEIVE';







    //按 长度 读取, SOCKET_READ 有效
    const SOCKET_READ_PARAM_BINARY = PHP_BINARY_READ;



    //按 长度 | PHP_EOL 读取, SOCKET_READ 有效
    const SOCKET_READ_PARAM_NORMAL = PHP_NORMAL_READ;



    //阻塞读,必须满足读取的数据满足长度才返回
    const SOCKET_RECEIVE_PARAM_WAITALL = MSG_WAITALL;


    //非阻塞读,按最大读取,有没有数据都返回
    const SOCKET_RECEIVE_PARAM_DONTWAIT = MSG_DONTWAIT;






    //读取方式 SOCKET_READ SOCKET_RECEIVE
    public $read_type = self::SOCKET_READ;




    //读取参数
    public $read_param = self::SOCKET_READ_PARAM_BINARY;





}