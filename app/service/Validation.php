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

class Validation
{
    public $response=[];
    
    public function __construct()
    {
        $this->response = array(
            'status'=>true,
            'message'=>''
        );
    }
    public function validateConfig()
    {
        if(!file_exists(__DIR__.'/../../.env')){
            $this->log->warn(date('Y-m-d H:i:s').'!!!!!! .env file not found !!!!!!!');
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
            $this->log->warn(date('Y-m-d H:i:s').'!!!!!! Configuration Not Found !!!!!!!');
            $this->response['status']=false;
            $this->response['message']='Configuration Not Found';           
        }
        return $this->response;
    }
    public function validateDBConnection()
    {

    }
}

?>