<?php
/**
 * 以下代码仅为调用示例
 * 请按照各自代码结构移入
 *
 */
$zipKin = ZipKin::getInstance();
//在一个请求的初试位置 开启一个链路追踪
$zipKin->startAction('uri路由','请求参数');
//执行业务代码A
$zipKin->addChild('A执行的sql/redis/http等语句','记录数据的tag名');
$zipKin->finishChild();
//执行业务代码B
$zipKin->addChild('B执行的sql/redis/http等语句','记录数据的tag名');
$zipKin->finishChild();
//代码执行结束
$traceId = $zipKin->getTraceId();//获取链路唯一标识  需要的时候将标识通过response/邮件或其他方式返回
//结束链路 上传链路信息
$zipKin->endAction();

