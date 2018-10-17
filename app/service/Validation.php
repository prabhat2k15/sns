<?php
/**
 * Proocess Company Validation Class
 *
 * @category Class
 * @package  ProcessCompanyAPI
 * @author   Prabhat Kumar <prabhat.kumar@myoperator.co>
 * @license  Proprietary http://myoperator.co
 */
namespace Service;

use Logger;
use Dotenv\Dotenv;

/**
 * Validation class : deals with validation of different enviroment files
 */
class Validation
{
    public $response=[];
    public $log;
    
    public function __construct()
    {
        Logger::configure(__DIR__.'/../../logconf.php');
        $this->log = Logger::getLogger('company');

        $this->response = array(
            'status'=>true,
            'message'=>''
        );
    }
    public function validateConfig()
    {
        if(!file_exists(__DIR__.'/../../.env') && !file_exists(__DIR__.'/../../logconf.php')){
            $this->log->warn(date('Y-m-d H:i:s').'!!!!!! .env file not found !!!!!!!');
            http_response_code(404);
            $this->response['status']=false;
            $this->response['message']='Enviroment file not found';
            return $this->response;
        }

        Logger::configure(__DIR__.'/../../logconf.php');
        $this->log = Logger::getLogger('company');

        try{
            $dotenv = new Dotenv(__DIR__.'/../../');
            $dotenv->load();

            $dotenv->required([
                'DB_HOST',
                'DB_NAME',
                'DB_USER',
                'DB_PASSWORD',
                'SNS_KEY',
                'SNS_SECRET',
                'SNS_VERSION',
                'SNS_REGION',
                'SNS_SCHEME',
            ]);
        }catch(\Exception $e){
            $this->log->warn(date('Y-m-d H:i:s').'!!!!!! Configuration Not Found. Also check config keys !!!!!!!');
            http_response_code(404);
            $this->response['status']=false;
            $this->response['message']='Configuration Not Found. Also check config keys.';           
        }
        return $this->response;
    }
    public function validateDBConnection()
    {

    }
}

?>