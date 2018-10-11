<?php

namespace Service;

date_default_timezone_set('UTC');

use Logger;
use Model\Query;
use Dotenv\Dotenv;

class ProcessCompany 
{
    private $query;
    private $company = array();
    private $data = array();
    private $display_number;
    private $ivr_keys;
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
        
        $this->query = new Query;        
    }

    /* $keys = [
        'setings'=>['id1','id2'],
        'ivrs'=>['id1','id2']
    ] */
    public function run($display_number, $keys=[])
    {
        $this->display_number = $display_number;

        $this->initCompanyDetails();
        
        $this->log->info('New Request For Display No : '.$this->display_number);
        $this->company = $this->query->_pick_company($this->display_number);
        /* log company */
        if(!empty($this->company)){
            $this->log->info('Company found for display no : '.$this->display_number);
            $this->company_id = $this->company[0]['company_id'];
            $this->loadCompanyDetails();
                $this->loadCompanySettings();
            $this->loadIvrs();
                $this->loadIvrSettings();
            $this->loadNodes();
            $this->loadDepartments();
    	        $this->loadDepartmentSettings();
            $this->loadCompanyUsers();
            $this->loadLanguages();
            
            $this->log->info('Company details for display no : '.json_encode($this->data));            
            print_r($this->data);

        }else{
            $this->log->info('Company not found for display no : '.$this->display_number);
        }
        /* Hit SNS here  */
        try{
            $sns = new SNS;
            $sns->publish($this->data);
        }catch(\Exception $e){
            $this->log->error('SNS push failed for display no : '.$this->display_number .'|||'. $e->getMessage());
        }
        


    }

    private function initCompanyDetails()
    {
        $this->data = array(
            $this->display_number . ':details' => null,
            $this->display_number . ':dept' => null,
            $this->display_number . ':languages' => null,
            $this->display_number . ':users' => null,
            $this->display_number . ':ivrs' => null,
            $this->display_number . ':nodes' => null,
            $this->display_number . ':ivr_settings' => null,
            $this->display_number . ':settings' => null,
            $this->display_number . ':dept' =>null
        );
    }


    private function loadCompanyDetails()
    {
        $company_details = $this->query->_fetch_company_details($this->company_id);
        $hash = $this->display_number . ":details";

        if ($company_details) {
	        $event_push = $this->query->_fetch_company_event_push($this->company_id);
            $company_details[0]['event_push'] = ($event_push == true) ? 1 : 0;
            $this->data[$hash] = $company_details[0];
        } else {
            // $this->log("company's details not found");
            // $this->log($this->company_id);
        }  
        return $company_details; 
    }
    private function loadCompanySettings()
    {
        $company_settings = $this->query->_fetch_company_settings($this->company_id);
        $hash = $this->display_number . ":settings";

        if ($company_settings) {
            foreach ($company_settings as $setting) {
                $settings[$setting['property_key']] = $setting['property_value'];
            }
            $this->data[$hash] = $settings;
        } else {
            $this->data[$hash] = null;

        }
        return $company_settings;
    }
    private function loadIvrs()
    {
        $ivrs = $this->query->_fetch_ivrs($this->company_id);
        $hash = $this->display_number . ":ivrs";

        if (!empty($ivrs)) {
            $this->ivr_keys = $ivrs['keys'];
            $this->data[$hash] = $ivrs['response'];
        } else {
            $this->data[$hash] = null;
        }
        return $ivrs;
    }
    private function loadIvrSettings()
    {
        $ivr_settings = $this->query->_fetch_ivr_settings($this->ivr_keys);
        $hash = $this->display_number . ":ivr_settings";

        if ($ivr_settings) {
            $this->ivr_keys = array_keys($ivr_settings);
            $this->data[$hash] = $ivr_settings;
        } else {
            $this->data[$hash] = null;
        }
        return $ivr_settings;
    }
    private function loadNodes()
    {
        $nodes = $this->query->_fetch_nodes($this->company_id, $this->ivr_keys);
        $hash = $this->display_number . ":nodes";

        if ($nodes) {
            $this->data[$hash] = $nodes;
        } else {
            $this->data[$hash] = null;
        }   
        return $nodes;
    }
    private function loadDepartments()
    {
        $departments = $this->query->_fetch_departments($this->company_id);
        $hash = $this->display_number . ":dept";

        if ($departments) {
            $this->data[$hash] = $departments;
        } else {
            $this->data[$hash] = null;
            /* ASK HERE if department is empty delete it or not */
        }
        return $departments;
    }

    private function loadDepartmentSettings()
    {
        $department_settings = $this->query->_fetch_department_settings($this->company_id);
        $hash = $this->display_number . ":dept_settings";
        
        if ($department_settings) {
            $this->data[$hash] = $department_settings;
        } 
        return $department_settings;
    }

    private function loadCompanyUsers()
    {
        $users = $this->query->_fetch_company_users($this->company_id);
        $hash = $this->display_number . ":users";

        if ($users) {
            $this->data[$hash] = $users;
        } 
        return $users;
    }
    private function loadLanguages()
    {
        $languages = $this->query->_fetch_languages($this->company_id);
        $hash = $this->display_number . ":languages";

        if ($languages) {
            $his->result[$hash] = $languages;
        }
        return $languages;
    }
   
}


/*************************************
    For CRON
**************************************/

if( empty(getallheaders()) && ENV !='TESTING'){
    if (!empty($_REQUEST['data']) ) {
        $data = $_REQUEST['data'];
        $post_data = json_decode($data, true);
        $Class = new ProcessCompany();
        $Class->run($post_data['display_number']);
    } else {
        echo  "Invalid Request";
    }
} 