<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Service\ProcessCompany;
use ReflectionClass;

class ProcessCompanyTest extends TestCase
{
    public function testInitCompanyDetails()
    {
        $pc = new ProcessCompany;
        $reflector = new ReflectionClass($pc);
        $method = $reflector->getMethod('initCompanyDetails');
        $method->setAccessible(true);
        $method->invoke($pc);

        $reflData = $reflector->getProperty('data');
        $reflData->setAccessible(true);
        $is_empty = true;
        foreach($reflData->getValue($pc) as $value){
            if(!empty($value)){
                $is_empty = false;
            }
        }
        $this->assertTrue($is_empty);
    }

    // public function 

    
}
?>