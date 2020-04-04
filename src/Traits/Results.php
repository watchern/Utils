<?php
/**
 * Created by PhpStorm.
 * @author  charles <watchern@126.com>
 */

namespace Charles\Utils\Traits;

use Closure;


trait Results
{
    // 为了规范，我们应当参考HTTP请求返回的状态码。
    // 0**    code区间    类型          含义
    // 1**    100-199     信息          服务器接收到请求，需要请求者继续执行操作或者有错误
    // 2**    200-299     成功          请求被成功接收并处理
    // 3**    300-399     重定向        需要进一步的操作以完成请求
    // 4**    400-499     客户端错误    请求包含语法错误或无法完成请求
    // 5**    500-599     服务器错误    服务器在处理的时候发生错误

    static $CODE_SUCCESS = 0;                   // 成功
    // 2000 - 2999 服务器的业务交互的友好提示
    // 请求被成功接收并处理
    static $CODE_ERROR_SERVER = 2000;           // 服务器返回友好提示
    static $CODE_ERROR_UNKNOWN = 2001;          // 未知错误
    static $CODE_ERROR_NOMORE = 2002;           // 没有更多数据了
    static $CODE_ERROR_NOEXIST = 2003;          // 暂无数据

    // 4001 - 4999 客户端引起的错误
    static $CODE_ERROR_INVALID = 4002;          // 非法请求
    static $CODE_ERROR_PARAMS = 4003;           // 参数错误
    static $CODE_ERROR_SIGN = 4004;             // 签名无效
    static $CODE_ERROR_AUTH = 4005;             // 认证错误
    static $CODE_ERROR_TIME = 4006;             // 时间校验失败

    // 5000 - 5999 服务器异常，以及错误处理 产生的错误

    static $CODE_ERROR_SERVICE = 5500;               // 服务器内部错误
    static $CODE_ERROR_NOT_IMPLEMENTED = 5501;       // 服务器不具备完成请求的功能
    static $CODE_ERROR_BAD_GATEWAY = 5502;           // 服务器网关异常
    static $CODE_ERROR_SERVICE_UNAVAILABLE = 5503;   // 服务器目前无法使用
    static $CODE_ERROR_GATEWAY_TIMEOUT = 5504;       // 服务器网关超时
    static $CODE_ERROR_NOT_SUPPORTED = 5505;         // 服务器不支持请求
    static $CODE_ERROR_FORBIDDEN = 5403;             // 服务器处理异常
    static $CODE_ERROR_NOT_FOUND = 5404;             // 页面不存在
    static $CODE_ERROR_ENTITY_TOO_LARGE = 5413;      // 请求实体过大
    static $CODE_ERROR_URI_TOO_LONG = 5414;          // 请求的URI过长

    // 默认返回的消息
    protected static $defaultMsgList = [

        // 成功
        0 => '操作成功',

        // 2000 - 2999 服务器的业务交互的友好提示
        2000 => '服务器繁忙',    // 服务器返回友好提示
        2001 => '未知错误',
        2002 => '没有更多数据了', // （针对列表式加载更多）
        2003 => '暂无数据', // 数据不存在

        // 4001 - 4999 客户端引起的错误
        4002 => '非法请求', // （post & get 请求不正确）
        4003 => '参数错误', // 具体是什么参数错误，可以在返回的时候输入msg参数
        4004 => '签名无效', // --基类
        4005 => '认证错误', // token 无效--基类
        4006 => '请求无效', // （时间校验失败）--基类

        // 5001 - 5999 服务器错误（用户自定义的错误，都应该在这个段）
        5500 => '服务器内部错误',
        5501 => '服务器不具备完成请求的功能',
        5502 => '服务器网关异常',
        5503 => '服务器目前无法使用',
        5504 => '服务器网关超时',
        5505 => '服务器不支持请求',
        5403 => '服务器处理异常',
        5404 => '页面不存在',
        5413 => '请求实体过大',
        5414 => '请求的 URI 过长',
    ];

    /**
     * 定义返回json的数据
     *
     * @param int $code
     * @param string $msg
     * @param array $data 必须是个数组对象， 即： key => value 的方式
     * @param int $time 时间戳
     */
    protected $arrJson = [
        'code' => 0,
        'msg' => '',
        'data' => [],
        'time' => 0
    ];

    /**
     * 响应 ajax 返回
     * @param null $array
     * @param Closure|null $callback 执行匿名函数，比如设置 header 头信息
     * @return array
     */
    public function returnJson($array = null, Closure $callback = null)
    {
        // 判断是否覆盖之前的值
        if ($array) {
            $this->arrJson = array_merge($this->arrJson, $array);
        }
        $code = $this->arrJson['code'];
        // 没有错误信息，就匹配默认的 code对应的 msg
        if (empty($this->arrJson['msg']) && isset(self::$defaultMsgList[$code])) {
            $this->arrJson['msg'] = self::$defaultMsgList[$code];
        }
        if (!$this->arrJson['time']) {
            $this->arrJson['time'] = time();
        }
        if ($callback && $callback instanceof Closure) {
            $callback();
        }
        return $this->arrJson;
    }

    /**
     * 处理成功返回
     * @param array $data
     * @param string $msg
     * @param array $params
     * @param Closure|null $callback
     * @return array
     */
    public function success($data = [], $msg = '', $params = [], Closure $callback = null)
    {
        $arr = array_merge([
            'code' => self::$CODE_SUCCESS,
            'msg' => $msg,
            'data' => $data
        ], $params);
        return $this->returnJson($arr, $callback);
    }

    /**
     * 处理错误返回,参数错误
     * @param string $msg
     * @param array $params
     * @param Closure|null $callback
     * @return array
     */
    public function paramsError($msg = '', $params = [], Closure $callback = null)
    {
        $code = self::$CODE_ERROR_PARAMS;
        $arr = array_merge([
            'code' => $code,
            'msg' => $msg,
        ], $params);
        return $this->returnJson($arr, $callback);
    }

    /**
     * 处理错误返回
     * @param string $msg
     * @param int $code
     * @param array $params
     * @param Closure|null $callback
     * @return array
     */
    public function error($msg = '', $code = 2000, $params = [], Closure $callback = null)
    {
        $arr = array_merge([
            'code' => $code,
            'msg' => $msg,
        ], $params);
        return $this->returnJson($arr, $callback);
    }

    /**
     * 认证错误
     * @param string $msg
     * @param array $params
     * @param Closure|null $callback
     * @return array
     */
    public function authError($msg = '', $params = [], Closure $callback = null)
    {
        $arr = array_merge([
            'code' => self::$CODE_ERROR_AUTH,
            'msg' => $msg
        ], $params);
        return $this->returnJson($arr, $callback);
    }

    /**
     * 设置错误码
     *
     * @param int $code
     */
    public function setCode($code)
    {
        $this->arrJson['code'] = $code;
    }

    /**
     * 设置错误信息
     *
     * @param string $msg
     */
    public function setMsg($msg = '')
    {
        $this->arrJson['msg'] = $msg;
    }
}
