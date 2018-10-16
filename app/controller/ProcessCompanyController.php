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

class ProcessCompanyController 
{
    private $data = array();
    private $display_number;
    private $company_id;
    private $response;
    public $log;

    public function __construct()
    {   
        Logger::configure(__DIR__.'/../../logconf.php');
        $this->log = Logger::getLogger('company');
        
        $validation = new Validation;
        $status = $validation->validateConfig();
        if(!$status['status']){
            echo json_encode($status);
            exit;
        }
        
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
        $query = new Query;
        $company = $query->_pick_company($display_number);
        $this->process_company->company_id = $company[0]['company_id'];
        $this->log->info('New Request For Company Id : '.$this->process_company->company_id);

        if(empty($keys)){
            //upload all data
            $this->log->info('Loading all data for '.$display_number);
            $this->process_company->run('all');
            return;
        }else{
            echo 'Loading individual data'."\n";
            foreach($keys as $key => $ids){
                $this->process_company->run($key, $ids);
            }
        }
        $this->log->info('Company details for display no : '.json_encode($this->data));            

        print_r($this->process_company->data);
        return $this->process_company->response;

    }
   
}


