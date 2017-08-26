<?php
/**
 *
 */
class View extends Template
{

    protected $templateDir = '';
    protected $templateFile = '';

    public function __construct()
    {
        $config = array(
            'view_path' => ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR,
            );
        parent::__construct($config);


        $conf = new Config;
        $this->templateDir = NI_MODULE . DIRECTORY_SEPARATOR . $conf->get('default_theme') . DIRECTORY_SEPARATOR;

        $this->templateFile = NI_CONTROLLER . '_' . NI_ACTION;
    }

    public function fetch($template = '', $vars = array(), $config = array())
    {
        if ($template == '') {
            $template = $this->templateFile;
        }
        parent::fetch($this->templateDir . $template, $vars, $config);
        exit();
    }
}
