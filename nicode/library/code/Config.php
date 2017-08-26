<?php
/**
 *
 */
class Config
{
    protected static $map = [];
    protected static $config = [];

    /**
     * 获得配置
     * @static
     * @access public
     * @param  string $name 为空获得所有配置
     * @return string|array
     */
    public static function get($name = '')
    {
        self::loader();
        if (empty($name)) {
            return self::$config;
        } elseif (!empty(self::$config[$name])) {
            return self::$config[$name];
        } else {
            return null;
        }
    }

    /**
     * 设置新配置
     * @static
     * @access public
     * @param  string $name
     * @param  string $value
     * @return void
     */
    public static function set($name, $value)
    {
        self::loader();
        self::$config[$name] = $value;
    }

    /**
     * 载入核心文件
     * @static
     * @access protected
     * @param
     * @return void
     */
    protected static function loader()
    {
        if (!empty(self::$config)) {
            return true;
        }

        $file = array(
            LIB_PATH . 'config.php',
            APP_PATH . NI_MODULE . DIRECTORY_SEPARATOR . 'config.php',
            );
        foreach ($file as $key => $value) {
            self::import($value);
        }
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
            $config = include($file);
            if (!empty(self::$config)) {
                self::$config = array_merge(self::$config, $config);
            } else {
                self::$config = $config;
            }

            self::$map[md5($file)] = true;
        }
    }
}
