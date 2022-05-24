<?php
class ZipKin {
    private static $tracer;
    private static $rootSpan;
    private static $appName = '';
    private static $instance = null;

    private static $span = null;
    private static $childSpan = null;

    private function __construct(){

    }


    private function __clone(){

    }

    /**
     * @return ZipKin|null
     * 初始化创建一个链路追踪  并创建根节点(rootSpan)
     */
    public static function getInstance(){
        if (self::$tracer === null ) {
            self::$appName = 'default';
            $tracing = createTracing(self::$appName, '127.0.0.1');
            self::$tracer = $tracing->getTracer();
            self::$rootSpan = self::$tracer->newTrace();
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $uri /请求的路由
     * @param $params /请求的入参
     * 在请求入口处使用
     * 开始一个根span
     * 并标记各种请求的数据
     */
    public  function startAction($uri,$params){
        self::$rootSpan->setName($uri);
        self::$rootSpan->start();
        /*通过tag 标记当前请求节点的各种数据*/
        self::$rootSpan->tag('data',json_encode($params));
        $origin = 'web.com';
        self::$rootSpan->tag('site',$origin);
        $host = 'api.com';
        self::$rootSpan->tag('host',$host);
    }

    /**
     *在请求结束处调用
     *结束整个程序
     */
    public  function endAction(){
        self::$rootSpan->finish();
        $tracers = self::$tracer;
        register_shutdown_function(function () use ($tracers) {
            $tracers->flush();
        });
    }

    /**
     * @param $executeStr /执行的语句
     * @param string $type /执行的类型 可自定义
     * 在代码运行的各种重要子节点开始时中引用  如:mysql/redis/http等
     * 新增一个子span
     */
    public function addChild($executeStr,$type = 'mysql-select'){
        if(self::$span===null){
            self::$span = self::$rootSpan;
        }
        $childSpan = self::$tracer->newChild(self::$span->getContext());
        self::$childSpan = $childSpan;
        $childSpan->start();
        $tag = 'data';
        if(in_array($type,['mysql-select','mysql-execute'])){//采用阿里云的链路追踪时特殊tag标签  会自动生成sql的统计数据 可查看sql的执行效率排行
            $tag = 'db.statement';
        }
        $childSpan->tag($tag,$executeStr);
        $childSpan->setName($type);

    }

    /**
     *在代码运行的各种重要子节点开始时中引用
     *结束子span
     *
     */
    public function finishChild(){
        self::$childSpan->finish();
    }

    /**
     * @return mixed
     * 获取链路的唯一标识
     * 用于请求的返回  或者 在异常捕捉类中抓取 通过邮件等形式发送给开发者
     */
    public function getTraceId(){
        return self::$rootSpan->getContext()->getTraceId();
    }
}