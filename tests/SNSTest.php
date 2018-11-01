<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Service\SNS;
use Aws\Sns\SnsClient;
use Service\Validation;

class SNSTest extends TestCase
{
    public function testSNSConnection()
    {
        $validation = new Validation;
        $response = $validation->validateConfig();
        $sns = new SNS;
        $refl = new ReflectionClass($sns);
        $refSNS = $refl->getProperty('sns');
        $refSNS->setAccessible(true);
        $snsobj = $refSNS->getValue($sns);
        $this->assertSame('Aws\Sns\SnsClient', get_class($snsobj));
    }
    
}
?>