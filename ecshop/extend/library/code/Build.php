<?php
/**
 *
 * 构造类
 *
 * @package   extend
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Build.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/06/23
 */

class Build
{
    protected $model;
    protected $request;

    public function __construct()
    {
        $this->model   = new Model;
        $this->request = new Request;
    }

    public function view()
    {
        $extend = new Extend;
        return $extend->view;
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
        $extend = new Extend;
        $extend->import($file, $ext);
    }
}