<?php
namespace Service;

use Aws\Sns\SnsClient;
use Dotenv\Dotenv;

class SNS
{
    private $sns;

    public function __construct()
    {
        $this->sns = new SnsClient([
            'credentials' => [
                    'key'    => getenv('SNS_KEY'),
                    'secret' => getenv('SNS_SECRET'),
                ],
            'version' => getenv('SNS_VERSION'),
            'region'  => getenv('SNS_REGION'),
            'scheme' => getenv('SNS_SCHEME')
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