<?php
/**
 * Proocess Company SNS API
 *
 * @category Class
 * @package  ProcessCompanyAPI
 * @author   Prabhat Kumar <prabhat.kumar@myoperator.co>
 * @license  Proprietary http://myoperator.co
 */
namespace Service;

use Aws\Sns\SnsClient;
use Dotenv\Dotenv;
use Aws\Common\Credentials\Credentials;

class SNS
{
    private $sns;

    public function __construct()
    {

        $credentials = new Credentials( getenv('SNS_KEY'),  getenv('SNS_SECRET'));

        $this->sns = SnsClient::factory(array(
            'credentials' => $credentials,
            'version' => getenv('SNS_VERSION'),
            'region'  => getenv('SNS_REGION'),
            'scheme' => getenv('SNS_SCHEME')
            )
        );
        //     'credentials' => [
        //             'key'    => getenv('SNS_KEY'),
        //             'secret' => getenv('SNS_SECRET'),
        //         ],
        //     'version' => 'latest',getenv('SNS_VERSION'),
        //     'region'  => 'us-east-2',//getenv('SNS_REGION'),
        //     'scheme' => 'http' //getenv('SNS_SCHEME')
        // ]);

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
            'TopicArn' => getenv('SNS_TOPIC'),
        ]);
        return $result;
    }
}
?>