<?php

namespace App\Rpc;


/**
 *
 * @\Hyperf\RpcServer\Annotation\RpcService(name="MsgService")
 * Class MsgService
 */
interface MsgServiceInterface
{
    /**
     * 定义一个发送消息方法
     * @param string $msg
     * @return string
     */
    public function send(string $msg): string;
}