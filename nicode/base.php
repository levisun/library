<?php
define('ROOT_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'nicode' . DIRECTORY_SEPARATOR);
define('LIB_PATH', ROOT_PATH . 'library' . DIRECTORY_SEPARATOR);
define('CORE_PATH', LIB_PATH . 'code' . DIRECTORY_SEPARATOR);
define('RUN_PATH', ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR);
define('LOG_PATH', RUN_PATH . 'log' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', RUN_PATH . 'cache' . DIRECTORY_SEPARATOR);
define('TEMP_PATH', RUN_PATH . 'temp' . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'application' . DIRECTORY_SEPARATOR);

require_once CORE_PATH . 'Loader.php';
Loader::register();
Error::register();

Build::run();
