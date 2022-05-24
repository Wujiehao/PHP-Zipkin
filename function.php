<?php
use Zipkin\Endpoint;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;

/**
 * @param $localServiceName /应用名称
 * @param $localServiceIPv4 /请求的ip
 * @param null $localServicePort /应用服务端口
 * @return \Zipkin\DefaultTracing|\Zipkin\Tracing
 * 创建一个链路追踪
 */
function createTracing($localServiceName, $localServiceIPv4, $localServicePort = null){

    $httpReporterURL = '数据接受接口(可为自己开发的接口/阿里云等相关服务接口)';

    $endpoint = Endpoint::create($localServiceName, $localServiceIPv4, null, $localServicePort);
    $reporter = new \Zipkin\Reporters\Http(['endpoint_url' => $httpReporterURL]);
    $sampler = BinarySampler::createAsAlwaysSample();
    $tracing = TracingBuilder::create()
        ->havingLocalEndpoint($endpoint)
        ->havingSampler($sampler)
        ->havingReporter($reporter)
        ->build();
    return $tracing;
}














