<?php
/**
 *
 * 基本操作类
 *
 * @package
 * @category  library
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Model.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/05/08
 */
// namespace library;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
defined('LIB_PATH') or define('LIB_PATH', APP_PATH . 'library' . DS);

// 加载全局函数
Base::load('common', LIB_PATH);

defined('MODULE_NAME') or define('MODULE_NAME', Base::input('get.m', 'index'));
defined('CONT_PAHT') or define('CONT_PAHT', APP_PATH . MODULE_NAME . DS . 'controller'. DS);
defined('MODE_PAHT') or define('MODE_PAHT', APP_PATH . MODULE_NAME . DS . 'model'. DS);
defined('VALI_PAHT') or define('VALI_PAHT', APP_PATH . MODULE_NAME . DS . 'validate'. DS);
defined('VIEW_PATH') or define('VIEW_PATH', APP_PATH . MODULE_NAME . DS . 'view' . DS);
defined('VIEW_CHARSET') or define('VIEW_CHARSET', 'utf-8');

date_default_timezone_set('Asia/Shanghai');
if (!session_id()) session_start();
header('Cache-control: private');
header('Content-type: text/html; charset=' . VIEW_CHARSET);

class Base
{

    /**
     * 分页
     * @static
     * @param  int   $total 总数据数
     * @param  int   $list_rows 每页显示数
     * @return array
     */
    public static function paginate($total, $list_rows=10)
    {
        Base::load('Page', LIB_PATH);

        $paginate = new Page;
        $paginate->paginate($total, $list_rows);

        return array('limit' => $paginate->limit, 'page' => $paginate->render());
    }

    /**
     * 上传文件
     * @static
     * @param  string $name FIELS[key]名
     * @return mixed
     */
    public static function uploadFile($name='')
    {
        $dir = date('Ym');
        $FileUploader = new FileUploader($name, array(
            'limit' => 9,
            'maxSize' => 5,
            'fileMaxSize' => 5,
            'extensions' => array('jpg', 'png', 'gif', 'JPG', 'PNG', 'GIF'),
            'required' => false,
            'uploadDir' => ROOT_PATH . './uploads/' . $dir . '/',
            'title' => array('auto', 24),
            'replace' => false,
            'listInput' => true,
            'files' => null
        ));

        if (!is_dir(ROOT_PATH. './uploads/' . $dir . '/')) {
            mkdir(ROOT_PATH . './uploads/' . $dir . '/', 0755);
            chmod(ROOT_PATH. './uploads/' . $dir . '/', 0755);
        }

        $data = $FileUploader->upload();


        if($data['isSuccess'] && count($data['files']) > 0) {
            $uploadedFiles = $data['files'];
        }
        if($data['hasWarnings']) {
            return $data['warnings'][0];
        }

        return $FileUploader->getFileList();
    }

    /**
     * 获取客户端IP地址
     * @static
     * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean   $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public static function ip($type = 0, $adv = false)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * 跳转
     * @static
     * @param  string $name
     * @return
     */
    public static function redirect($url)
    {
        header('Location:' . $url);
    }

    /**
     * POST请求
     * @static
     * @param
     * @return boolean
     */
    public static function isPost()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return $_SERVER['REQUEST_METHOD'] === 'POST';
        } else {
            return !empty($_POST);
        }
    }

    /**
     * AJAX请求
     * @static
     * @param
     * @return boolean
     */
    public static function isAjax()
    {
        $value = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
        return ('xmlhttprequest' == $value) ? true : false;
    }

    /**
     * SESSION操作
     * @static
     * @param  string $name
     * @param  mixed  $value
     * @return mixed
     */
    public static function session($name, $value='')
    {
        session_start();
        if ('' === $value) {
            if (isset($_SESSION[$name])) {
                return $_SESSION[$name];
            } else {
                return null;
            }
        } else {
            if (is_null($value)) {
                unset($_SESSION[$name]);
            } else {
                $_SESSION[$name] = $value;
            }
        }
    }

    /**
     * COOKIE操作
     * @static
     * @param  string $name
     * @param  mixed  $value
     * @param  intval $expire
     * @param  string $path
     * @param  string $domain
     * @return mixed
     */
    public static function cookie($name, $value='', $expire=0, $path='', $domain='')
    {
        if ('' === $value) {
            if (isset($_COOKIE[$name])) {
                return $_COOKIE[$name];
            } else {
                return null;
            }
        } else {
            if (is_null($value)) {
                setcookie($name, '', time() - 3600, $path, $domain);
                unset($_COOKIE[$name]);
            } else {
                setcookie($name, $value, $expire, $path, $domain);
                $_COOKIE[$name] = $value;
            }
        }
    }

    /**
     * 请求变量安全过虑
     * @static
     * @param  string $data
     * @param  mixed  $default
     * @param  string $filter
     * @return mixed
     */
    public static function input($data, $default='', $filter='strip_tags,escape_xss')
    {
        static $_input = array();

        $guid = $data;
        if (isset($_input[$guid])) {
            return $_input[$guid];
        }

        $input = explode('.', $data);
        if (count($input) == 1) {
            $type = 'GET';
            $name = $input[0];
        } else {
            $type = strtoupper($input[0]);
            $name = $input[1];
        }

        switch ($type) {
            case 'GET':
                $_GET[$name] = !empty($_GET[$name]) ? $_GET[$name] : $default;
                $_GET[$name] = self::filter($_GET[$name], $filter);
                $data = $_GET[$name];
                break;

            case 'POST':
                $_POST[$name] = !empty($_POST[$name]) ? $_POST[$name] : $default;
                $_POST[$name] = self::filter($_POST[$name], $filter);
                $data = $_POST[$name];
                break;

            case 'FILES':
                $_FILES[$name] = !empty($_FILES[$name]) ? $_FILES[$name] : $default;
                $_FILES[$name] = self::filter($_FILES[$name], $filter);
                $data = $_FILES[$name];
                break;

            default:
                $data = null;
                break;
        }

        $_input[$guid] = $data;
        return $_input[$guid];
    }

    private static function filter($data, $filter)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = self::filter($value, $filter);
                } else {
                    $data[$key] = self::filter_function($value, $filter);
                }
            }
        } else {
            $data = self::filter_function($data, $filter);
        }

        return $data;
    }

    private static function filter_function($data, $filter)
    {
        $data = trim($data);
        $filter = explode(',', $filter);
        foreach ($filter as $func) {
            $data = $func($data);
        }
        return $data;
    }

    /**
     * 实例化控制器
     * @static
     * @param  string $name
     * @param  string $dir
     * @return object
     */
    public static function action($name, $dir='')
    {
        static $_action = array();

        $name = ucfirst($name) . 'Controller';

        $guid = $dir . $name;
        if (!isset($_action[$guid])) {
            require_once(CONT_PAHT . $dir . $name . '.php');
            $_action[$guid] = new $name;
        }

        return $_action[$guid];
    }

    /**
     * 实例化模型
     * @static
     * @param  string $name
     * @param  string $dir
     * @return object
     */
    public static function model($name, $dir='')
    {
        static $_model = array();

        $name = ucfirst($name) . 'Model';

        $guid = $dir . $name;
        if (!isset($_model[$guid])) {
            require_once(MODE_PAHT . $dir . $name . '.php');
            $_model[$guid] = new $name;
        }

        return $_model[$guid];
    }

    /**
     * 实例化验证
     * @static
     * @param  string $name
     * @param  string $dir
     * @return object
     */
    public static function validate($name, $dir='')
    {
        static $_vali = array();

        $name = ucfirst($name) . 'Validate';

        $guid = $dir . $name;
        if (!isset($_vali[$guid])) {
            require_once(VALI_PAHT . $dir . $name . '.php');
            $_vali[$guid] = new $name;
        }

        return $_vali[$guid];
    }

    /**
     * 加载文件
     * @static
     * @param  string $name
     * @param  string $dir
     * @param  string $ext
     * @return void
     */
    public static function load($name, $dir, $ext='.php')
    {
        if (!file_exists($dir . $name. $ext)) {
            exit('File error:' . $dir . $name . $ext);
        }
        require_once($dir . $name . $ext);
    }
}
