<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Service\ProcessCompany;
use Controller\ProcessCompanyController;
use ReflectionClass;

class ProcessCompanyTest extends TestCase
{
    public function testForInvalidDisplayNumber()
    {
        $pcc = new ProcessCompanyController;
        $display_number = '12313123';//wrong display no
        $response = $pcc->run($display_number);

        $pc = new ProcessCompany;
        $reflector = new ReflectionClass($pc);
        // $method = $reflector->getMethod('initCompanyDetails');
        // $method->setAccessible(true);
        // $method->invoke($pc);

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

    public function testForValidDisplayNumber()
    {
        $pcc = new ProcessCompanyController;
        $display_number = '919873832455';//valid display no
        $response = $pcc->run($display_number);

        $this->assertTrue($response['status']);
    }

    public function testForLoadingCompanyUsersDetails()
    {
        $pcc = new ProcessCompanyController;
        $display_number = '919873832455';//valid display no
        $keys = [
            'company_users'=>[
                    '542c021d6745d657'=>['is_enabled','timing_manager'],
                    '57c5dd207e859871'=>['is_enabled'],
            ]
        ];
        $response = $pcc->run($display_number, $keys);

        // Ensuring array's 1st key must be display_numer:users
        $this->assertEquals($display_number.':users', key($pcc->process_company->data));
    }

    
}
?>