<?php
function run($param)
{
    if (isset($param['m'])) $_GET['m'] = $param['m'];

    if (isset($param['c'])) $_GET['c'] = $param['c'];

    if (isset($param['a'])) $_GET['a'] = $param['a'];

    require_once 'nicode\base.php';
}
