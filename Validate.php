<?php
/**
 *
 * 校验
 *
 * @package
 * @category  library
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Validate.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/05/08
 */
// namespace library;

class Validate
{

    /**
     * 验证数据
     * @access protected
     * @param  array  $data 二维数组
     * @return mixed
     */
    protected function checkdate($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!empty($value[3])) {
                    if (!$this->is($value[0], $value[1], $value[3])) {
                        return $value[2];
                    }
                } else {
                    if (!$this->is($value[0], $value[1])) {
                        return $value[2];
                    }
                }
            }
        }

        return true;
    }

    /**
     * 验证字段值是否为有效格式
     * @access protected
     * @param mixed     $value  字段值
     * @param string    $rule  验证规则
     * @param array     $data  验证数据
     * @return bool
     */
    protected function is($value, $rule, $data='')
    {
        if (strpos($rule, ':')) {
            list($rule, $mixed) = explode(':', $rule, 2);
        }

        switch ($rule) {
            case 'require':
                // 必须
                $result = !empty($value) || '0' == $value;
                break;
            case 'accepted':
                // 接受
                $result = in_array($value, array('1', 'on', 'yes'));
                break;
            case 'date':
                // 是否是一个有效日期
                $result = false !== strtotime($value);
                break;
            case 'alpha':
                // 只允许字母
                $result = $this->regex($value, '/^[A-Za-z]+$/');
                break;
            case 'alphaNum':
                // 只允许字母和数字
                $result = $this->regex($value, '/^[A-Za-z0-9]+$/');
                break;
            case 'alphaDash':
                // 只允许字母、数字和下划线 破折号
                $result = $this->regex($value, '/^[A-Za-z0-9\-\_]+$/');
                break;
            case 'chs':
                // 只允许汉字
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}]+$/u');
                break;
            case 'chsAlpha':
                // 只允许汉字、字母
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u');
                break;
            case 'chsAlphaNum':
                // 只允许汉字、字母和数字
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u');
                break;
            case 'chsDash':
                // 只允许汉字、字母、数字和下划线_及破折号-
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u');
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'ip':
                // 是否为IP地址
                $result = $this->filter($value, array(FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6));
                break;
            case 'url':
                // 是否为一个URL地址
                $result = $this->filter($value, FILTER_VALIDATE_URL);
                break;
            case 'float':
                // 是否为float
                $result = $this->filter($value, FILTER_VALIDATE_FLOAT);
                break;
            case 'number':
                $result = is_numeric($value);
                break;
            case 'integer':
                // 是否为整型
                $result = $this->filter($value, FILTER_VALIDATE_INT);
                break;
            case 'email':
                // 是否为邮箱地址
                $result = $this->filter($value, FILTER_VALIDATE_EMAIL);
                break;
            case 'boolean':
                // 是否为布尔值
                $result = in_array($value, array(true, false, 0, 1, '0', '1'), true);
                break;
            case 'array':
                // 是否为数组
                $result = is_array($value);
                break;
            case 'function':
                $result = $data($value);
                break;
            case 'length':
                $result = mb_strlen((string) $value) == $mixed;
                break;
            case 'max':
                $result = mb_strlen((string) $value) <= $mixed;
                break;
            case 'min':
                $result = mb_strlen((string) $value) >= $mixed;
                break;
            case 'unique':
                $result = $this->unique($value, $data);
                break;
            default:
                // 正则验证
                $result = $this->regex($value, $rule);
                break;
        }
        return $result;
    }

    /**
     * 唯一
     * @access protected
     * @param  string $value
     * @param  string $rule
     * @return boolean
     */
    protected function unique($value, $rule)
    {
        if (strpos($rule, ':')) {
            list($table, $field) = explode(':', $rule, 2);
        }

        $map = '`' . $field . '`=\'' . $value . '\'';

        $model = new Model;

        // 查询表主键
        $PRI =
        $model->table($table)
        ->getPRI();

        // ID存在 排除此主键ID的信息
        $id = Base::input('post.id');
        $map .= $id ? ' AND `' . $PRI . '` <> \'' . $id . '\'' : '';

        $result =
        $model->table($table)
        ->field($field)
        ->where($map)
        ->one();

        return $result ? false : true;
    }

    /**
     * 使用正则验证数据
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $rule  验证规则 正则规则或者预定义正则名
     * @return mixed
     */
    protected function regex($value, $rule)
    {
        if (isset($this->regex[$rule])) {
            $rule = $this->regex[$rule];
        }
        if (0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }
        return 1 === preg_match($rule, (string) $value);
    }

    /**
     * 使用filter_var方式验证
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $rule  验证规则
     * @return bool
     */
    protected function filter($value, $rule)
    {
        if (is_string($rule) && strpos($rule, ',')) {
            list($rule, $param) = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = isset($rule[1]) ? $rule[1] : null;
            $rule  = $rule[0];
        } else {
            $param = null;
        }
        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }
}
