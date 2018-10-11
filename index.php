<?php
include_once('vendor/autoload.php');
// include('config.php');
// use Service\RedisWrapper;
// echo $_ENV['DB_HOST'];
// $r = new RedisWrapper;
// $r->test();
// use Service\ProcessCompanyWrapper;

// $pcw = new ProcessCompanyWrapper;
// $pcw->test();

// use Model\RedisTest;
// $rt = new RedisTest;
// $rt->redisPush();

// use Service\SNS;
// $sns = new SNS;
// $sns->test();


use Service\ProcessCompany;
$pc = new ProcessCompany;
echo '<pre>';
print_r($pc->run('919873832455'));




// echo ENV;
// print_r($GLOBALS['ENV']);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
echo $_ENV['DB_HOST'];
echo getenv('DB_HOST');
// $dotenv->required('DATABASE_DSN');


?>