<?php
/**
 *
 * 公共（函数）文件
 *
 * @package   extend
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: common.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/05/08
 */

/**
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
if (!function_exists('msubstr')) {
    function msubstr($str, $start=0, $length, $suffix=true, $charset='utf-8')
    {
        $ext = mb_strlen($str) > $length && $suffix ? '...' : '';
        return mb_substr($str, $start, $length, $charset) . $ext;
    }
}

/**
 * 调试变量并且中断输出
 * @param  mixed $data
 * @return mixed
 */
if (!function_exists('halt')) {
    function halt($data)
    {
        var_dump($data);
        exit();
    }
}

/**
 * 过滤XSS
 * @param  string $data
 * @return string
 */
if (!function_exists('escape_xss')) {
    function escape_xss($data)
    {
        $search = array(
            // 过滤PHP
            '/<\?php(.*?)\?>/si',
            '/<\?(.*?)\?>/si',
            '/<%(.*?)%>/si',
            '/<\?php|<\?|\?>|<%|%>/si',

            '/on([a-z].*?)["|\'](.*?)["|\']/si',
            '/(javascript:)(.*?)(\))/si',
            '/<\!--.*?-->/s',
            '/<(\!.*?)>/si',

            '/<(javascript.*?)>(.*?)<(\/javascript.*?)>/si',
            '/<(\/?javascript.*?)>/si',

            '/<(vbscript.*?)>(.*?)<(\/vbscript.*?)>/si',
            '/<(\/?vbscript.*?)>/si',

            '/<(expression.*?)>(.*?)<(\/expression.*?)>/si',
            '/<(\/?expression.*?)>/si',

            '/<(applet.*?)>(.*?)<(\/applet.*?)>/si',
            '/<(\/?applet.*?)>/si',

            '/<(xml.*?)>(.*?)<(\/xml.*?)>/si',
            '/<(\/?xml.*?)>/si',

            '/<(blink.*?)>(.*?)<(\/blink.*?)>/si',
            '/<(\/?blink.*?)>/si',

            '/<(link.*?)>(.*?)<(\/link.*?)>/si',
            '/<(\/?link.*?)>/si',

            '/<(script.*?)>(.*?)<(\/script.*?)>/si',
            '/<(\/?script.*?)>/si',

            '/<(embed.*?)>(.*?)<(\/embed.*?)>/si',
            '/<(\/?embed.*?)>/si',

            '/<(object.*?)>(.*?)<(\/object.*?)>/si',
            '/<(\/?object.*?)>/si',

            '/<(iframe.*?)>(.*?)<(\/iframe.*?)>/si',
            '/<(\/?iframe.*?)>/si',

            '/<(frame.*?)>(.*?)<(\/frame.*?)>/si',
            '/<(\/?frame.*?)>/si',

            '/<(frameset.*?)>(.*?)<(\/frameset.*?)>/si',
            '/<(\/?frameset.*?)>/si',

            '/<(ilayer.*?)>(.*?)<(\/ilayer.*?)>/si',
            '/<(\/?ilayer.*?)>/si',

            '/<(layer.*?)>(.*?)<(\/layer.*?)>/si',
            '/<(\/?layer.*?)>/si',

            '/<(bgsound.*?)>(.*?)<(\/bgsound.*?)>/si',
            '/<(\/?bgsound.*?)>/si',

            '/<(title.*?)>(.*?)<(\/title.*?)>/si',
            '/<(\/?title.*?)>/si',

            '/<(base.*?)>(.*?)<(\/base.*?)>/si',
            '/<(\/?base.*?)>/si',

            '/<(meta.*?)>(.*?)<(\/meta.*?)>/si',
            '/<(\/?meta.*?)>/si',

            '/<(style.*?)>(.*?)<(\/style.*?)>/si',
            '/<(\/?style.*?)>/si',

            '/<(html.*?)>(.*?)<(\/html.*?)>/si',
            '/<(\/?html.*?)>/si',

            '/<(head.*?)>(.*?)<(\/head.*?)>/si',
            '/<(\/?head.*?)>/si',

            '/<(body.*?)>(.*?)<(\/body.*?)>/si',
            '/<(\/?body.*?)>/si',
        );

        $data = preg_replace($search, '', $data);
        $data = preg_replace('/[  ]+/si', ' ', $data);      // 多余空格
        $data = preg_replace('/[.\s]+</si', '<', $data);    // 多余回车
        $data = preg_replace('/>[.\s]+/si', '>', $data);    // 多余回车

        // 转义特殊字符
        $strtr = array(
            '*' => '&lowast;', '`' => '&acute;',
            '￥' => '&yen;', '™' => '&trade;', '®' => '&reg;', '©' => '&copy;',
            // '\'' => '&#39;', '"' => '&quot;', '<' => '&lt;', '>' => '&gt;',
            '０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5',
            '６' => '6', '７' => '7', '８' => '8', '９' => '9',

            'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E', 'Ｆ' => 'F',
            'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L',
            'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R',
            'Ｓ' => 'S', 'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X',
            'Ｙ' => 'Y', 'Ｚ' => 'Z',

            'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e', 'ｆ' => 'f',
            'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l',
            'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r',
            'ｓ' => 's', 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
            'ｙ' => 'y', 'ｚ' => 'z',

            '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[', '】' => ']',
            '〖' => '[', '〗' => ']', '｛' => '{', '｝' => '}', '％' => '%', '＋' => '+',
            '—' => '-', '－' => '-', '～' => '-', '：' => ':', '？' => '?', '！' => '!',
            '…' => '...', '‖' => '|', '｜' => '|',
            '〃' => '&quot;', '”' => '&quot;', '“' => '&quot;',  '’' => '&acute;',
            '‘' => '&acute;',
            '×' => '&times;', '÷' => '&divide;',
            );
        $data = strtr($data, $strtr);

        return $data;
    }
}

/**
 * 字符串加密
 * @param  mixed  $data    加密前的字符串
 * @param  string $authkey 密钥
 * @return mixed  加密后的字符串
 */
if (!function_exists('encrypt')) {
    function encrypt($data, $authkey='0af4769d381ece7b4fddd59dcf048da6') {
        if (is_array($data)) {
            $coded = array();
            foreach ($data as $key => $value) {
                $coded[$key] = encrypt($value, $authkey);
            }
            return $coded;
        } else {
            $coded = '';
            $keylength = strlen($authkey);
            for ($i = 0, $count = strlen($data); $i < $count; $i += $keylength) {
                $coded .= substr($data, $i, $keylength) ^ $authkey;
            }
            return str_replace('=', '', base64_encode($coded));
        }
    }
}

/**
 * 字符串解密
 * @param  mixed  $data    加密后的字符串
 * @param  string $authkey 密钥
 * @return mixed  加密前的字符串
 */
if (!function_exists('decrypt')) {
    function decrypt($data, $authkey='0af4769d381ece7b4fddd59dcf048da6') {
        if (is_array($data)) {
            $coded = array();
            foreach ($data as $key => $value) {
                $coded[$key] = decrypt($value, $authkey);
            }
            return $coded;
        } else {
            $coded = '';
            $keylength = strlen($authkey);
            $data = base64_decode($data);
            for ($i = 0, $count = strlen($data); $i < $count; $i += $keylength) {
                $coded .= substr($data, $i, $keylength) ^ $authkey;
            }
            return $coded;
        }
    }
}
