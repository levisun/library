<?php
/**
 * 控制器
 *
 * @package   NiPHPCMS
 * @category  code
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Controller.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/08/18
 */
class Controller extends View
{
    protected $request;
    protected $method;

    public function __construct()
    {
        $this->request = new Request;
        $this->method = $this->request->param('method', 'list');

        parent::__construct();

        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    protected function added()
    {
        # code...
    }

    protected function delete()
    {
        # code...
    }

    protected function update()
    {
        # code...
    }

    protected function select()
    {
        # code...
    }

    public function success($success, $url)
    {
        # code...
    }

    protected function error($error, $url)
    {
        # code...
    }

    protected function validate($name)
    {
        list($class, $action) = explode('.', $name);

        $validate = new $class;
        return $validate->$action();
    }

    protected function _initialize()
    {
        # code...
    }
}
