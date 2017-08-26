<?php
/**
 *
 * 请求类
 *
 * @package   extend
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Request.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/06/23
 */

class Request
{

    /**
     * 请求变量安全过虑
     * @static
     * @param  string $data
     * @param  mixed  $default
     * @param  string $filter
     * @return mixed
     */
    public function input($data, $default = '', $filter = 'strip_tags,escape_xss')
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
                $_GET[$name] = $this->filter($_GET[$name], $filter);
                $data = $_GET[$name];
                break;

            case 'POST':
                $_POST[$name] = !empty($_POST[$name]) ? $_POST[$name] : $default;
                $_POST[$name] = $this->filter($_POST[$name], $filter);
                $data = $_POST[$name];
                break;

            case 'FILES':
                $_FILES[$name] = !empty($_FILES[$name]) ? $_FILES[$name] : $default;
                $_FILES[$name] = $this->filter($_FILES[$name], $filter);
                $data = $_FILES[$name];
                break;

            default:
                $data = null;
                break;
        }

        $_input[$guid] = $data;
        return $_input[$guid];
    }

    private function filter($data, $filter)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->filter($value, $filter);
                } else {
                    $data[$key] = $this->filter_function($value, $filter);
                }
            }
        } else {
            $data = $this->filter_function($data, $filter);
        }

        return $data;
    }

    private function filter_function($data, $filter)
    {
        $data = trim($data);
        $filter = explode(',', $filter);
        foreach ($filter as $func) {
            $data = $func($data);
        }
        return $data;
    }

    /**
     * 是否微信端访问
     * @access public
     * @param
     * @return boolean
     */
    public function isWechat()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            return true;
        } else {
            return false;
        }
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
     * 获取客户端IP地址
     * @static
     * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean   $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public function ip($type = 0, $adv = false)
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
}
