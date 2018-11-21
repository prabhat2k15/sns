<?php
/**
 * Proocess Company API
 *
 * @category Class
 * @package  ProcessCompanyAPI
 * @author   Prabhat Kumar <prabhat.kumar@myoperator.co>
 * @license  Proprietary http://myoperator.co
 */

namespace Service;

date_default_timezone_set('UTC');

use Logger;
use Model\Query;
use Dotenv\Dotenv;

/**
 * Process Company class 
 */
class ProcessCompany 
{
    private $query;
    private $company = array();
    private $ivr_keys;

    public $data = array();
    public $display_number;
    public $company_id;
    public $log;
    public $response = array('status'=>false);

    public function __construct()
    {   
        Logger::configure(__DIR__.'/../../logconf.php');
        $this->log = Logger::getLogger('company');
        
        // $validation = new Validation;
        // $validator_response = $validation->validateConfig();
        // if(!$validator_response['status']){
        //     echo json_encode($validator_response);
        //     exit;
        // }
        
        $this->query = new Query;        
    }

    /**
     * Entry point for process company api
     *
     * @param array $key contains the company keys to fetch details
     * @param array $ids contains the ids of the keys  
     * 
     * @return void
     */
    public function run($key, $ids=[])
    {
        // $company = $this->query->_pick_company($this->display_number);

        // if(!empty($this->company_id)){

            // $this->company_id = $company[0]['company_id'];
            // $this->log->info('New Request For Company Id : '.$this->company_id);
    
            
            switch($key){
                case 'companies':
                    $this->loadCompanyDetails($ids);
                    break;
                case 'company_settings':
                    $this->loadCompanySettings($ids);
                    break;
                case 'company_users':
                    $this->loadCompanyUsers($ids);
                    break;
                case 'ivrs':
                    $this->loadIvrs($ids);
                    break;
                case 'ivr_settings':
                    $this->loadIvrSettings($ids);
                    break;
                case 'nodes':
                    $this->loadNodes($ids);
                    break;
                case 'departments':
                    $this->loadDepartments($ids);
                    break;
                case 'department_settings':
                    $this->loadDepartmentSettings($ids);
                    break;
                case 'languages':
                    $this->loadLanguages($ids);
                    break;
                case 'all':
                    $this->loadCompanyDetails();
                    $this->loadCompanySettings();
                    $this->loadCompanyUsers();
                    $this->loadIvrs();
                    $this->loadIvrSettings();
                    $this->loadNodes();
                    $this->loadDepartments();
                    $this->loadDepartmentSettings();
                    $this->loadLanguages();                    
                    break;
                default:
                    $this->log->warn('Invalid Request. Running default for: '.$this->display_number);
                    return $this->response['message'] = 'Invalid Key for display no : '.$this->display_number;

            }
        
            $this->response['status'] = true;
            $this->response['message'] = 'Success';

        // }else{
        //     $this->initCompanyDetails();
        //     $this->log->info('Company not found for display no : '.$this->display_number);
        //     $this->response['message'] = 'Company not found for display no : '.$this->display_number;
        // }

       
        
        return $this->response;

    }

    /**
     * This is to initiallize array of comapny data to null by default
     *
     * @return void
     */
    public function initCompanyDetails()
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
            $this->display_number . ':dept_settings' =>null
        );
    }

    /**
     * Loads the company details
     *
     * @param array $ids contains the ids of key 
     * 
     * @return void
     */
    private function loadCompanyDetails($ids=[])
    {
        $company_details = $this->query->_fetch_company_details($this->company_id);
        $hash = $this->display_number . ":details";

        if ($company_details) {
            if(!empty($ids)){
                //filter details
                $company_details[0] = (array_intersect_key($company_details[0], $ids));
            }

	        $event_push = $this->query->_fetch_company_event_push($this->company_id);
            $company_details[0]['event_push'] = ($event_push == true) ? 1 : 0;
            $this->data[$hash] = $company_details[0];
        } else{
            $this->data[$hash] = null;
        } 
        return $company_details; 
    }

    /**
     * Loads the company settings
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $company_settings
     */
    private function loadCompanySettings($ids=[])
    {
        $company_settings = $this->query->_fetch_company_settings($this->company_id);
        $hash = $this->display_number . ":settings";
        if ($company_settings) {
            foreach ($company_settings as $setting) {
                $settings[$setting['property_key']] = $setting['property_value'];
            }
            if(!empty($ids)){
                //filter details
                $settings = (array_intersect_key($settings, $ids));
            }
            $this->data[$hash] = $settings;
        } else{
            $this->data[$hash] = null;
        } 
        return $company_settings;
    }

    /**
     * Loads the Ivrs of the company
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $ivrs
     */
    private function loadIvrs($ids=[])
    {
        $ivrs = $this->query->_fetch_ivrs($this->company_id);
        $hash = $this->display_number . ":ivrs";

        if (!empty($ivrs)) {
             if(!empty($ids)){
                $filter_data = $this->filterData($ivrs['response'], $ids);
                //filter details
                // foreach($ids as $key=>$id){
                //     $filter_data = (array_intersect_key($ids[$key], json_decode($ivrs['response'][$key],1)[0]));
                //     $tmp_ivrs[$key]=json_encode($filter_data);
                // }
                $ivrs['response']=$filter_data;
            }
            $this->ivr_keys = $ivrs['keys'];
            $this->data[$hash] = $ivrs['response'];
        } else {
            $this->data[$hash] = null;
        }
        return $ivrs;
    }

    /**
     * Loads Ivr Settings of the company
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $ivr_settings
     */
    private function loadIvrSettings($ids=[])
    {
        $ivr_settings = $this->query->_fetch_ivr_settings($this->ivr_keys);
        $hash = $this->display_number . ":ivr_settings";
        if ($ivr_settings) {
             if(!empty($ids)){
                //filter details
                $filter_data = $this->filterData($ivr_settings, $ids);
                // foreach($ids as $key=>$id){
                //     $filter_data = (array_intersect_key($ids[$key], json_decode($ivr_settings[$key],1)));
                //     $tmp_ivrs_settings[$key]=json_encode($filter_data);
                // }
                $ivr_settings = $filter_data;
            }
            $this->ivr_keys = array_keys($ivr_settings);
            $this->data[$hash] = $ivr_settings;
        } else {
            $this->data[$hash] = null;
        }
        return $ivr_settings;
    }

    /**
     * Loads Nodes of the company
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $nodes
     */
    private function loadNodes($ids=[])
    {
        $nodes = $this->query->_fetch_nodes($this->company_id, $this->ivr_keys);
        $hash = $this->display_number . ":nodes";

        if ($nodes) {
             if(!empty($ids)){
                //filter details
                $filter_data = $this->filterData($nodes, $ids);
                // foreach($ids as $key=>$id){
                //     $nodes[$key]=json_encode($id);
                // }
                $nodes=$filter_data;
            }
            $this->data[$hash] = $nodes;
        }else{
            $this->data[$hash] = null;
        } 
        return $nodes;
    }

    /**
     * Loads Departments of the company
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $departments
     */
    private function loadDepartments($ids=[])
    {
        $departments = $this->query->_fetch_departments($this->company_id);
        $hash = $this->display_number . ":dept";

        if ($departments) {
             if(!empty($ids)){
                //filter details
               $filter_data = $this->filterData($departments, $ids);
               $departments = $filter_data;
            }
            $this->data[$hash] = $departments;
        } else{
            $this->data[$hash] = null;
        } 
        return $departments;
    }

    /**
     * Loads department settings
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $department_settings
     */
    private function loadDepartmentSettings($ids=[])
    {
        $department_settings = $this->query->_fetch_department_settings($this->company_id);
        $hash = $this->display_number . ":dept_settings";
        
        if ($department_settings) {
             if(!empty($ids)){
                //filter details
               $filter_data = $this->filterData($department_settings, $ids);
                $department_settings = $filter_data;
            }
            $this->data[$hash] = $department_settings;
        } else{
            $this->data[$hash] = null;
        } 
        return $department_settings;
    }

    /**
     * Loads company users
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $users
     */
    private function loadCompanyUsers($ids=[])
    {
        $users = $this->query->_fetch_company_users($this->company_id);
        $hash = $this->display_number . ":users";
        if ($users) {
             if(!empty($ids)){
                //filter details
                $filter_data = $this->filterData($users, $ids);
                $users = $filter_data;
            }
            $this->data[$hash] = $users;
        }else{
            $this->data[$hash] = null;
        } 
        
        return $users;
    }

    /**
     * Loads languages
     *
     * @param array $ids contains the ids of key 
     * 
     * @return array $languages
     */
    private function loadLanguages($ids=[])
    {
        $languages = $this->query->_fetch_languages($this->company_id);
        $hash = $this->display_number . ":languages";
        
        if ($languages) {
             if(!empty($ids)){
                //filter details
                $filter_data = $this->filterData($languages, $ids);
                $languages = $filter_data;
            }
            $this->data[$hash] = $languages;
        }else{
            $this->data[$hash] = null;
        } 
        return $languages;
    }

    /**
     * Filters out the data in the old and substitue in the new 
     *
     * @param array $old contains the data fetched from DB 
     * @param array $new contains the key and ids whose value to be substituted
     * 
     * @return array $new_ar
     */
    private function filterData($old, $new)
    {
        $new_ar=[];
        foreach($new as $key=>$ids){
            if(array_key_exists($key, $old)){
                if(empty($ids)){
                    $new_ar[$key] = $old[$key];//is_array(json_decode($old[$key],1)) ? json_decode($old[$key],1): $old[$key];
                }else{
                    foreach($ids as $id){
                        $new_ar[$key][$id] = isset(json_decode($old[$key],1)[$id]) ? json_decode($old[$key],1)[$id] : json_decode($old[$key],1)[0][$id];
                    }
                    $new_ar[$key]=json_encode($new_ar[$key]);

                }
                
            }else{
               $new_ar[$key] = null; 
            }
        }
        return $new_ar;

       
    }
   
}


/*************************************
    For CRON
**************************************/

// if( empty(getallheaders()) && ENV !='TESTING'){
//     if (!empty($_REQUEST['data']) ) {
//         $data = $_REQUEST['data'];
//         $post_data = json_decode($data, true);
//         $Class = new ProcessCompany();
//         $Class->run($post_data['display_number']);
//     } else {
//         echo  "Invalid Request";
//     }
// } 


// {"919873832455:users":{"542c021d6745d657":{"is_enabled":"1","timing_manager":[{"day":127,"start_time":"03:30:00","end_time":"15:29:00"}]},"57c5dd207e859871":{"id":"57c5dd207e859871","name":"Sales","extension":null,"display_number":"919873832455","user_ids":["542c022219226915","542c021d6745d657"],"user_type":"2"}}}
// {"919873832455:users":{"542c021d6745d657":"{\"is_enabled\":\"1\",\"linked_companies\":[{\"display_number\":\"919873832455\"}],\"timing_manager\":[{\"day\":127,\"start_time\":\"03:30:00\",\"end_time\":\"15:29:00\"}]}","542c022219226915":"{\"uuid\":\"542c022219226915\",\"contact\":\"8800203456\",\"contact_2\":\"\",\"contact_country\":\"+91\",\"contact_2_country\":\"\",\"contact_type\":\"mobile\",\"contact_type_2\":\"mobile\",\"extension\":\"10\",\"email\":\"ankit@myoperator.co\",\"user_type\":\"1\",\"is_enabled\":\"1\",\"linked_companies\":[{\"display_number\":\"919873832455\"},{\"display_number\":\"\"},{\"display_number\":\"\"},{\"display_number\":\"\"},{\"display_number\":\"\"},{\"display_number\":\"\"},{\"display_number\":\"\"},{\"display_number\":\"\"},{\"display_number\":\"\"},{\"display_number\":\"\"}],\"timing_manager\":[{\"day\":62,\"start_time\":\"18:30:00\",\"end_time\":\"18:30:00\"}]}","57c5dd207e859871":"{\"id\":\"57c5dd207e859871\",\"name\":\"Sales\",\"extension\":null,\"display_number\":\"919873832455\",\"user_ids\":[\"542c022219226915\",\"542c021d6745d657\"],\"user_type\":\"2\"}","57c5dd207e909603":"{\"id\":\"57c5dd207e909603\",\"name\":\"General Enquiry\",\"extension\":null,\"display_number\":\"919873832455\",\"user_ids\":[\"542c022219226915\",\"542c021d6745d657\"],\"user_type\":\"2\"}","57c5dd207e9b1701":"{\"id\":\"57c5dd207e9b1701\",\"name\":\" Extension\",\"extension\":null,\"display_number\":\"919873832455\",\"user_ids\":[\"542c022219226915\",\"542c021d6745d657\"],\"user_type\":\"2\"}"},"919873832455:ivrs":{"time":"[{\"ivr_name\":\"Myoperator\",\"ivr_basis\":\"time\",\"ivr_id\":\"57c5dd20809e5147\",\"day\":127,\"start_time\":0,\"end_time\":86399}]"},"919873832455:ivr_settings":{"57c5dd20809e5147":"{\"moh\":\"false\",\"voicemail\":\"false\"}"},

