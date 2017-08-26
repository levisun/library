<?php
/**
 *
 * 加载类
 *
 * @package   NiPHPCMS
 * @category  code
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Loader.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/08/18
 */
class Loader
{
    protected static $map = [];
    protected static $ext = '.php';

    protected static $namespace = '';
    protected static $class = '';

    /**
     * 注册系统自动加载
     * @access public
     * @param
     * @return void
     */
    public static function register()
    {
        // 载入核心函数
        self::func();

        // 注册系统自动加载
        spl_autoload_register('Loader::autoload', true, true);
    }

    /**
     * 载入核心函数
     * @static
     * @access protected
     * @param
     * @return void
     */
    protected static function func()
    {
        $file = array(
            LIB_PATH . 'common.php',
            );
        foreach ($file as $key => $value) {
            require_once($value);
        }
    }

    /**
     * 自动加载类
     * @static
     * @access public
     * @param  string $class 类名
     * @return void
     */
    public static function autoload($class)
    {
        self::namespaceAndClass($class);

        if (self::has()) {
            return true;
        }

        if ($original = self::hasFile()) {
            require_once($original);
            self::$map[md5(self::$namespace . self::$class)] = true;
        }
    }

    /**
     * 检查文件目录
     * @access protected
     * @param
     * @return string
     */
    protected static function hasFile()
    {
        $dir = array(
            CORE_PATH,
            LIB_PATH,
            LIB_PATH . 'net' . DIRECTORY_SEPARATOR,
            LIB_PATH . 'util' . DIRECTORY_SEPARATOR,
            );

        if (defined('NI_MODULE')) {
            $dir[] = APP_PATH . NI_MODULE . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR;
            $dir[] = APP_PATH . NI_MODULE . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR;
            $dir[] = APP_PATH . NI_MODULE . DIRECTORY_SEPARATOR . 'logic' . DIRECTORY_SEPARATOR;
        }



        foreach ($dir as $key => $value) {
            if (is_file($value . self::$namespace . self::$class)) {
                return $value . self::$namespace . self::$class;
            }
        }
    }

    /**
     * 检查是否已加载
     * @access public
     * @param
     * @return booelan
     */
    protected static function has()
    {
        if (isset(self::$map[md5(self::$namespace . self::$class)])) {
            if (class_exists(self::$class)) {
                return true;
            } else {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获得加载文件目录（命名空间）和类名
     * @access protected
     * @param  string
     * @return void
     */
    protected static function namespaceAndClass($class)
    {
        self::$namespace = dirname($class);

        $strtr = array(
            '/'  => DIRECTORY_SEPARATOR,
            '\\' => DIRECTORY_SEPARATOR,
            '.'  => ''
            );

        self::$namespace = strtr(self::$namespace, $strtr);
        if (self::$namespace) {
            self::$namespace . DIRECTORY_SEPARATOR;
        }

        self::$class = basename($class) . self::$ext;
    }
}
