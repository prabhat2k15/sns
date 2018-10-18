<?php
/**
 * Proocess Company Controller
 *
 * @category Class
 * @package  ProcessCompanyAPI
 * @author   Prabhat Kumar <prabhat.kumar@myoperator.co>
 * @license  Proprietary http://myoperator.co
 */
namespace Controller;

date_default_timezone_set('UTC');

use Logger;
use Service\ProcessCompany;
use Service\Validation;
use Model\Query;
use Service\SNS;

/**
 * Class : This class controls the incoming request and passes the request to Process Company Class.
 * Extends : None
 */
class ProcessCompanyController 
{
    private $data = array();
    private $display_number;
    private $company_id;
    private $response;
    private $validation;

    public $log;


    public function __construct()
    {   
        $this->validation = new Validation;
        $validator_response = $this->validation->validateConfig();
        if(!$validator_response['status']){
            echo json_encode($validator_response);
            exit;
        }

        Logger::configure(__DIR__.'/../../logconf.php');
        $this->log = Logger::getLogger('company');
        
        
        $this->process_company = new ProcessCompany;        
    }

     /**
     * Entry point for process company api
     *
     * @param string $display_number 
     * @param array $keys contains the company keys to fetch details
     * 
     * @return json $response
     */
    public function run($display_number=null, $keys=[])
    {
        $this->process_company->display_number = $display_number;
        
        if(empty($keys)){
            $this->log->info('Loading all data for '.$display_number);
            $this->response = $this->process_company->run('all');

        }else{
            if($this->validation->validateKeys($keys)){
                $this->log->info('Loading individual data for '.$display_number);
                foreach($keys as $key => $ids){
                    $this->response = $this->process_company->run($key, $ids);
                }
            }else{
                $this->response['status'] = false;
                $this->response['message'] = 'Key or Keys invalid';
            }
            
        }

        $this->log->info('Company details for display no : '.$display_number.':'.json_encode($this->process_company->data),JSON_PRETTY_PRINT);            
        
        /* Pushing data to SNS here  */
        try{
            $sns = new SNS;
            $sns->publish($this->process_company->data);
        }catch(\Exception $e){
            $this->log->error('SNS push failed for display no : '.$this->process_company->display_number .'|||'. $e->getMessage());
            $this->response['status'] = false;
            $this->response['message'] = 'SNS push failed';

        }
        // print_r($this->process_company->data);
        return $this->response;

    }
   
}


