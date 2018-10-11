<?php
namespace Service;

use Model\Database1;

class RedisWrapper
{
    public function test()
    {
        echo 'test';
        echo $_ENV['DB_HOST'];
        $db = new Database1;

        echo $db->test(); 

    }

}