<?php
namespace Service;

use Aws\Sns\SnsClient;

class SNS
{
    private $sns;

    public function __construct()
    {
        $this->sns = new SnsClient([
            'credentials' => [
                    'key'    => $_ENV['SNS']['KEY'],
                    'secret' => $_ENV['SNS']['SECRET'],
                ],
            'version' => $_ENV['SNS']['VERSION'],
            'region'  => $_ENV['SNS']['REGION'],
            'scheme' => $_ENV['SNS']['SCHEME']
        ]);

    }

    public function publish($message)
    {
        if(is_array($message)){
            $message = json_encode($message);
        }
        $result =$this->sns->publish([
            'Message' => $message, // REQUIRED
            'MessageStructure' => 'raw',
            'Subject' => 'Test Sub',
            // 'TargetArn' => '<string>',
            'TopicArn' => 'arn:aws:sns:us-east-2:733584512176:testsns',
        ]);
        print_r($result);
        echo 'done';
    }
}
?>