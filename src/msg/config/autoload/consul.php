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
