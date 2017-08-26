<?php
/**
 *
 * 错误类
 *
 * @package   NiPHPCMS
 * @category  code
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Error.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/08/18
 */
class Error
{
    /**
     * 注册异常处理
     * @static
     * @access public
     * @param
     * @return void
     */
    public static function register()
    {
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    /**
     * 自定义异常处理
     * @static
     * @access public
     * @param mixed $e 异常对象
     * @return void
     */
    public static function appException($e)
    {
        $error = array();
        $error['message']   = $e->getMessage();
        $trace  =   $e->getTrace();
        if ('throw_exception'==$trace[0]['function']) {
            $error['file'] = $trace[0]['file'];
            $error['line'] = $trace[0]['line'];
        } else {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
        }
        Log::write($error['message'], 'ERR');
        echo 'ERROR:' . $error['message'];
        exit();
    }

    /**
     * 自定义错误处理
     * @static
     * @access public
     * @param  int    $errno   错误类型
     * @param  string $errstr  错误信息
     * @param  string $errfile 错误文件
     * @param  int    $errline 错误行数
     * @return void
     */
    public static function appError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                $errorStr = "$errstr " . $errfile . " 第 $errline 行.";
                Log::write("[$errno] " . $errorStr, 'ERR');
                echo 'ERROR:' . $errorStr;
                exit();
                break;

            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
                Log::write($errorStr, 'ERR');
                echo $errorStr;
                exit();
                break;
        }
    }

    /**
     * 致命错误捕获
     * @static
     * @access public
     * @param
     * @return void
     */
    public static function appShutdown()
    {
        // 保存日志记录
        Log::save();
        if ($e = error_get_last()) {
            switch($e['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();
                    $error = 'ERROR:' . $e['message'] . ' in <b>'.$e['file'] . '</b> on line <b>' . $e['line'] . '</b>';
                    Log::write($error, 'ERR');
                    echo $error;
                    exit();
                    break;
            }
        }
    }
}
