<?php
use Workerman\Worker;
require_once __DIR__.'/Workerman/Autoloader.php';
foreach (glob(__DIR__.'/start_*.php') as $key => $path) {
	require_once $path;
}
Worker::runAll();
?>