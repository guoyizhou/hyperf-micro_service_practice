# hyperf框架之php微服务实战教程

写这篇教程的原因，php微服务教程少之又少。编写教程不易、通宵了2天晚上终于折腾php实现微服务。希望大家分享时保留版权。

第一次写文档教程、表达的不是很好希望大家理解或给我更正。

## 一、php微服务技术选型：

服务网关：spring cloud gateway

服务治理：consul 或 nacos

开发环境：win10+docker

php框架：swoole4.7、hyperfy2.2

RPC通信：hyperf/json-rpc  通信方式http协议 方便后续研究调用java微服务



## 二、基础搭建

### 1.win10安装docker运行环境

这里根据大家喜好来选、可以用虚拟机liunx配合宝塔、主要是了运行swoole

参考安装教程：https://yeasy.gitbook.io/docker_practice/install/windows

具体安装细节不做太多的说明，自行百度。都是下一步，下一步。

<font color=red>注意有坑1：win10系统版本原因、我说一下安装完之后注意事项。</font>
<font color=red>有些系统可能不支持win10 liux 子系统wsl2.0</font>

![image-20210801123133505](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801123133505.png)

<font color=red>解决一些配置的坑：</font>

![image-20210801124022911](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801124022911.png)

### 2.docker安装hyperfy2.2开发环境

#### 1.检查docker是否正常

首先通过cmd 运行docker --version 是否安装成功docker 没有请回到第一步

![image-20210801125422425](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801125422425.png)

#### 2.搭建开发环境

**创建用户服务环境** --- 服务消费

```sh
docker run -v /e/php_code/hyperf-skeleton/user:/hyperf-skeleton -p 9501:9501 -p 9502:9502  -it --entrypoint /bin/sh hyperf/hyperf:7.4-alpine-v3.12-swoole
```

**创建消息服务环境** -- 服务提供

```sh
docker run -v /e/php_code/hyperf-skeleton/msg:/hyperf-skeleton -p 9503:9503 -p 9504:9504  -it --entrypoint /bin/sh hyperf/hyperf:7.4-alpine-v3.12-swoole
```

执行成功后会进入容器如下图 

![image-20210801130113859](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801130113859.png)

参数说明

-v 表示将本地目录挂载共享到docker目录  **注意要存在这个目录不然会报错**  /e/php_code/hyperf-skeleton/demo1 这里e是电脑E盘  主要用于win10 ide 编写代码

-p 表示本地端口对应docker端口号、主要用于win10访问docker容器端口号

-it 用于exit命令时不会关闭容器

其他参数请参考docker详细教程

https://yeasy.gitbook.io/docker_practice/

## 三、创建项目代码

### 1.进入容器

找到我们创建的docker 容器 进入容器里面构建代码

![image-20210801130933304](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801130933304.png)

### 2.创建msg项目、user项目

参考官方文档：https://hyperf.wiki/2.2/#/zh-cn/quick-start/install?id=docker-%e4%b8%8b%e5%bc%80%e5%8f%91

用户项目和消息项目操作一致

```shell
cd hyperf-skeleton/
composer create-project hyperf/hyperf-skeleton
```



![image-20210801132702978](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801132702978.png)

其他都默认，唯独这里我们选择 **1** json rpc 带有服务治理 如果没选要自己单独composer 引入

![image-20210801132821636](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801132821636.png)



这里注意一下：通过create-project hyperf/hyperf-skeleton 会多创建一个/hyperf-skeleton 需要我们手动调整位置、能接受可以忽略

### 3.启动框架

```sh
php bin/hyperf.php start
```

![image-20210801134056035](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801134056035.png)

```
http://localhost:9501/
```



![image-20210801134235546](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801134235546.png)

## 四、phpstrom插件配置

主要方便后续写代码支持注解、不是必须、锦上添花

![image-20210801145307074](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801145307074.png)

## 五、创建Msg项目---服务提供者

参考官方文档：https://hyperf.wiki/2.2/#/zh-cn/json-rpc

### 1.定义服务提供者

```
在app目录下创建Rpc目录并创建MsgService和MsgServiceInterface文件
```

```php
<?php
namespace App\Rpc;
/**
 * 定义一个消息服务
 * @\Hyperf\RpcServer\Annotation\RpcService(name="MsgService")
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
```

```php
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
```



### 2.定义 JSON RPC Server

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Swoole\Constant;

return [
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9503, //我们docker配置http端口号
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
        //上面是http服务
        //下面定义 JSON RPC Server
        [
            'name' => 'jsonrpc-http', //需要修改
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9504,//我们docker配置服务提供的端口号是9503
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                //这里需要用JsonRpc
                Event::ON_REQUEST => [Hyperf\JsonRpc\HttpServer::class, 'onRequest'],
            ],
        ],
    ],
    'settings' => [
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_WORKER_NUM => swoole_cpu_num(),
        Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/hyperf.pid',
        Constant::OPTION_OPEN_TCP_NODELAY => true,
        Constant::OPTION_MAX_COROUTINE => 100000,
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
        Constant::OPTION_MAX_REQUEST => 100000,
        Constant::OPTION_SOCKET_BUFFER_SIZE => 2 * 1024 * 1024,
        Constant::OPTION_BUFFER_OUTPUT_SIZE => 2 * 1024 * 1024,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
    ],
];

```

### 3.启动和测试访问服务

```sh
php bin/hyperf.php start
```

```
http://localhost:9504/
```



![image-20210801143520403](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801143520403.png)

## 六、编写User项目 --- 服务消费者

参考官方文档：https://hyperf.wiki/2.2/#/zh-cn/json-rpc?id=%e5%ae%9a%e4%b9%89%e6%9c%8d%e5%8a%a1%e6%b6%88%e8%b4%b9%e8%80%85

### 1.定义服务消费者

自动创建代理消费者类

```
创建配置文件
config/autoload/services.php
```

```php
<?php
    #这里是user项目消费者配置
return [
    // 此处省略了其它同层级的配置
    'consumers' => [
        [
            // name 需与服务提供者的 name 属性相同
            'name'          => 'MsgService',
            //服务接口名，可选，默认值等于 name 配置的值，如果 name 直接定义为接口类则可忽略此行配置，如 name 为字符串则需要配置 service 对应到接口类
            #注意这里需要 写msg服务接口
            'service'       => \App\Rpc\MsgServiceInterface::class,
            // 对应容器对象 ID，可选，默认值等于 service 配置的值，用来定义依赖注入的 key
            #注意这里需要 写msg服务接口
            'id'            => \App\Rpc\MsgServiceInterface::class,
            // 如果没有指定上面的 registry 配置，即为直接对指定的节点进行消费，通过下面的 nodes 参数来配置服务提供者的节点信息
            'nodes'         => [
                //这里的ip是服务提供者的ip 因为docker环境我们填写局域网ip 后面住服务治理会去掉这个配置
                #不知道写什么ip 通过docker容器命令行 ipaddr 查看 ip
                ['host' => '172.17.0.2', 'port' => 9504],
            ],
        ],
    ]
];
```

### 2.定义Msg服务接口

```
创建msg服务接口文件
app\Rpc\MsgServiceInterface.php
```

```php
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
```

### 3.调用msg服务接口

```
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Rpc\MsgServiceInterface;
use Hyperf\Di\Annotation\Inject;

class IndexController extends AbstractController
{

    /**
     * Di 容器注入 详情参考官方文档
     * @Inject()
     * @var MsgServiceInterface
     */
    private $msgService;

    public function index()
    {
        $msg = $this->msgService;
        //调用msg服务中的send方法
        $str = $msg->send("用户发送一条消息".date('Y-m-d H:i:s'));
        return [
            "code"=>0,
            "msg"=>$str
        ];
    }
}
```

### 4.测试服务调用

```
http://localhost:9501/
```



![image-20210801151513332](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801151513332.png)



## 七、服务治理

### 1.安装注册中心consul

为什么这里要用docker安装注册中心？

原因：是因为docker有4种网络模式、注册中心拿到的ip是docker内外ip 172.x.x* win10 注册中心无法访问、可自行谷歌折腾这么配置访问

如果你是虚拟机可忽略

#### 1) 创建docker consul

```sh
docker run -d -p 8500:8500 -it --entrypoint /bin/sh consul
```

#### 2) 启动consul服务

这里我们选单机开发环境

```sh
consul agent -dev -bind=0.0.0.0 -client 0.0.0.0 -ui
```

#### 3) win10访问consul ui

![image-20210801135753071](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801135753071.png)

### 2.Msg服务发布到服务中心

参考官方文档：https://hyperf.wiki/2.2/#/zh-cn/json-rpc?id=%e5%8f%91%e5%b8%83%e5%88%b0%e6%9c%8d%e5%8a%a1%e4%b8%ad%e5%bf%83

#### 1) 引入consul客户端和适配器

```
#服务提供者需要做的事---------------------我们这里是msg项目是服务提供者
#注意这里需要引入consul协程客户端 不然不能注册服务和服务发现 官方网文档没写
composer require hyperf/consul

#选择安装对应的适配器
composer require hyperf/service-governance-consul

#生成consul客户端配置文件
php bin/hyperf.php vendor:publish hyperf/consul
```

#### 2) 修改consul客户端配置

```
配置文件路径
msg\config\autoload\consul.php
```

```php
<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    #不知道写什么ip 通过docker容器命令行 ipaddr 查看 ip
    #这里需要写consul局域网的ip
    'uri' => 'http://172.17.0.4:8500',
    'token' => '',
];

```



#### 3) 修改MsgServeice服务文件

**需要再注解上面加参数 publishTo="consul" 表示注册到consul注册中心**

```php
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
```

```php
<?php

namespace App\Rpc;


/**
 * @\Hyperf\RpcServer\Annotation\RpcService(name="MsgService",publishTo="consul")
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
```



#### 4) 开启服务治理

```
修改配置文件
config/autoload/services.php
```

```php
<?php
#这里是msg项目的配置
return [
    'enable'    => [
        //开启服务发现
        'discovery' => TRUE,
        //开启服务注册
        'register'  => TRUE,
    ],
    'consumers' => [],//服务消费端配置
    'providers' => [],
    //注册中心配置
    'drivers'   => [
        'consul' => [
            #不知道写什么ip 通过docker容器命令行 ipaddr 查看 ip
            #这里需要写consul局域网的ip
            'uri'   => 'http://172.17.0.4:8500',
            'token' => '',
        ],
    ]
];
```

- **验证服务注册和服务发现**

![image-20210801165729375](https://github.com/guoyizhou/hyperf-microservice-practice/raw/master/img/image-20210801165729375.png)

- **测试服务调用**
