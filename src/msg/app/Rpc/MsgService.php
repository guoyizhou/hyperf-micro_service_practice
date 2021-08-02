<?php
namespace App\Rpc;
/**
 * 定义一个消息服务
 * @\Hyperf\RpcServer\Annotation\RpcService(name="MsgService",publishTo="consul")
 * Class MsgService
 */
class MsgService implements MsgServiceInterface
{

    /**
     * 定义一个发送消息方法
     * @param string $msg
     * @return string
     */
    public function send(string $msg):string {
        return "msg服务收到请求：".$msg;
    }
}