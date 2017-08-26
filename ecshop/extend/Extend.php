<?php
/**
 * 扩展类
 * 尽量不在EC的程序中修改添加代码
 *
 * 在init.php初始数据库下插入
 * include('./includes/extend/Extend.php');
 * $_extend = new Extend($db_host, $db_user, $db_pass, $db_name, $prefix);
 */
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath($_SERVER['SCRIPT_FILENAME'])) . DS);
// 核心代码库
defined('CODE_PATH') or define('CODE_PATH', dirname(__FILE__) . DS . 'library' . DS . 'code' . DS);
// 扩展代码库（EC基本操作）
defined('LIB_PATH') or define('LIB_PATH', dirname(__FILE__) . DS . 'library' . DS);
// 扩展根目录（EC扩展功能全部写在此目录）
defined('EXT_PATH') or define('EXT_PATH', dirname(__FILE__) . DS);

class Extend
{
    protected $extend_file = array(
        'common',
        'Build',
        'Model',
        'Request',
        );

    public $view;

    public function __construct($db_host, $db_user, $db_pass, $db_name, $prefix)
    {
        // 声明数据库常量
        // 用于Model类
        defined('_DB_HOST_') or define('_DB_HOST_', $db_host);
        defined('_DB_USER_') or define('_DB_USER_', $db_user);
        defined('_DB_PASS_') or define('_DB_PASS_', $db_pass);
        defined('_DB_NAME_') or define('_DB_NAME_', $db_name);
        defined('_PREFIX_')  or define('_PREFIX_',  $prefix);

        // 加载核心类
        $this->import($this->extend_file);
    }

    public function hook($name)
    {
        $this->import($name);
        return new $name($this->model);
    }

    /**
     * 视图
     * @access public
     * @param  object $smarty
     * @return void
     */
    public function view(&$smarty)
    {
        $this->view = $smarty;
    }

    /**
     * 加载类
     * @access public
     * @param  string|array $file 文件名
     * @param  string       $ext  文件名后缀
     * @return boole
     */
    public function import($file, $ext = '.php')
    {
        if (empty($file)) {
            return false;
        }

        if (is_array($file)) {
            foreach ($file as $key => $value) {
                $this->import($value, $ext);
            }
        } elseif (file_exists(CODE_PATH . $file . $ext)) {
            require_once(CODE_PATH . $file . $ext);
        } elseif (file_exists(LIB_PATH . $file . $ext)) {
            require_once(LIB_PATH . $file . $ext);
        } elseif (file_exists(EXT_PATH . $file . $ext)) {
            require_once(EXT_PATH . $file . $ext);
        }
    }
}