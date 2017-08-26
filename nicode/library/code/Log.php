<?php
/**
 *
 * 日志类
 *
 * @package   NiPHPCMS
 * @category  code
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Log.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/08/18
 */
class Log
{
    protected static $log = array();

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param  string $message 日志信息
     * @param  string $level  日志级别
     * @return void
     */
    public static function write($message, $level) {
        File::createDir(LOG_PATH . date('Ym') . DIRECTORY_SEPARATOR);

        $destination = LOG_PATH . date('Ym') . DIRECTORY_SEPARATOR . date('ymd') . '.log';

        $now = date('Y-m-d H:i:s');

        error_log("{$now} {$level}: {$message}\r\n", 3, $destination);
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param
     * @return void
     */
    public static function save()
    {
        if (empty(self::$log)) {
            return ;
        }

        File::createDir(LOG_PATH . date('Ym') . DIRECTORY_SEPARATOR);

        $destination = LOG_PATH . date('Ym') . DIRECTORY_SEPARATOR . date('ymd') . '.log';

        $request = new Request;
        $error = date('Y-m-d H:i:s');
        $error .= ' ' . $request->ip(0, true);
        $error .= ' ' . $request->ip(true) . "\r\n";
        $error .= implode('', self::$log) . "\r\n";

        error_log($error, 3, $destination);

        self::$log = array();
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param  string $message 日志信息
     * @param  string $level   日志级别
     * @return void
     */
    public static function record($message, $level) {
        self::$log[] =   "{$level}: {$message}\r\n";
    }
}
