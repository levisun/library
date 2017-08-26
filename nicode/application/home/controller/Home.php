<?php
/**
*
*/
class Home extends Controller
{
    public function index()
    {
        /*if ($this->request->isPost()) {
            $file = $this->request->param('image');
            $class = new Upload($file['tmp_name']);
            $class->setUploadInfo($file);
            $result = $class->move(PATH . 'public/upload/');
            $save_name = $result->getSaveName();
            halt($save_name);
        }*/
        // new Upload();
        $this->assign('f', 123);
        $this->fetch();
    }

    public function test()
    {
        echo "string";
    }
}