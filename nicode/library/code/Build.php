<?php
/**
 *
 */
class Build
{
    protected static $map = [];

    /**
     * 运行程序
     * @static
     * @access public
     * @param
     * @return void
     */
    public static function run()
    {
        date_default_timezone_set('PRC');
        header('Content-Type:text/html; charset=utf-8');

        self::sysConst();

        self::import(APP_PATH . NI_MODULE . DIRECTORY_SEPARATOR . 'common.php');

        self::controller(); // 执行控制器方法
    }

    /**
     * 系统常量
     * @static
     * @access protected
     * @param
     * @return void
     */
    protected static function sysConst()
    {
        $request = new Request;

        define('NI_MODULE', $request->module());
        define('NI_CONTROLLER', $request->controller());
        define('NI_ACTION', $request->action());

        // 环境常量
        define('NI_IS_CLI', $request->isCli());
        define('NI_IS_WIN', strpos(PHP_OS, 'WIN') !== false);
    }

    /**
     * 实例化控制器
     * @static
     * @access protected
     * @param
     * @return void
     */
    protected static function controller()
    {
        $controller = ucfirst(NI_CONTROLLER);
        $action = NI_ACTION;

        if (!class_exists($controller)) {
            halt('Controller application\\' . NI_MODULE . '\\' . $controller . '.php' . ' not found. ');
        }
        $object = new $controller;

        if (!method_exists($object, $action)) {
            halt('Action application\\' . NI_MODULE . '\\' . $controller . '.php ' . $controller . '->' . $action . '() undefined');
        }
        $object->$action();
    }

    /**
     * 载入文件
     * @static
     * @access public
     * @param  string  $file
     * @return boolean
     */
    public static function import($file)
    {
        if (isset(self::$map[md5($file)])) {
            return true;
        }

        if (is_file($file)) {
            include_once($file);
            self::$map[md5($file)] = true;
        }
    }
}
