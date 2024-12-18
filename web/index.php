<?php

$params = require __DIR__ . '/../config/params.php';
$allowedIPs = $params['allowedIPs'];

// comment out the following two lines when deployed to production
if (in_array($_SERVER["REMOTE_ADDR"], $allowedIPs)) {
	defined('YII_DEBUG') or define('YII_DEBUG', true);
	defined('YII_ENV') or define('YII_ENV', 'dev');
} else {
	defined('YII_DEBUG') or define('YII_DEBUG', false);
	defined('YII_ENV') or define('YII_ENV', 'prod');
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
