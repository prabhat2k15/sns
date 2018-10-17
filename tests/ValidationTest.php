<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Service\Validation;

class ValidationTest extends TestCase
{
    public function testValidateConfigForTrue()
    {
        $validation = new Validation;
        $response = $validation->validateConfig();
        $this->assertTrue($response['status']);
    }

    public function testValidateKeysForEmptyKeysReturnTrue()
    {
        $validation = new Validation;
        $response = $validation->validateKeys([]);
        $this->assertTrue($response);
    }

    public function testValidateKeysForValidKeysReturnTrue()
    {
        $validation = new Validation;
        $response = $validation->validateKeys(['companies'=>[]]);
        $this->assertTrue($response);
    }

    public function testValidateKeysForInvalidKeysReturnFalse()
    {
        $validation = new Validation;
        $response = $validation->validateKeys(['companiessssss'=>[]]);
        $this->assertFalse($response);
    }
    
}
?>