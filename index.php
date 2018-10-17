<?php
include_once('vendor/autoload.php');

use Controller\ProcessCompanyController;

$keys = [
        'companies'=>[
            'destination'=>[],
            'destination_2'=>[],
            'xyz'=>[],
        ],
        // 'company_settings'=>[
        //     'account_type'=>[],
        //     'connection_method'=>[],
        // ],
        // 'ivrs'=>[
        //     'time'=>[
        //         'ivr_name',
        //         'ivr_basis',
        //     ],
        // ],
        // 'ivr_settings'=>[
        //     '57c5dd20809e5147'=>[
        //         'moh',
        //         'voicemail',
        //     ],
        // ],
        // 'nodes'=>[
        //     '57c5dd20809e5147'=>[
        //         'node_value',
        //         'parent_id',
        //     ],
        // ],
        // 'departments'=>[
        //     '57c5dd207e859871' => ["name","users"],
        //     '57c5dd207e909603' => ["name","users"],
        //     '57c5dd207e9b1701' => ["name","users"],
        // ],
        // 'department_settings'=>[
        //     "57c5dd207e859871"=>['max_digits'],
        // ],
        // 'languages'=>[
        //     '546ef22119da5117'=>["language_name","status"],
        // ],
        // 'company_users'=>[
        //     '542c021d6745d657'=>['is_enabled','timing_manager'],
        //     '57c5dd207e859871'=>['is_enabled'],
        // ],
    ];
//     $keys = json_encode($keys);
// echo $keys; die;
if(!empty($_REQUEST['display_number'])){
    
    $keys = $_REQUEST['keys'] ?? json_encode([]);
    $keys=json_decode($keys, 1);
    $pc = new ProcessCompanyController;
    
    echo json_encode($pc->run($_REQUEST['display_number'], $keys));

}else{
    http_response_code(401);
    echo json_encode(array('status'=>false, 'message'=>'Invalid Request'));
}

// 919873832455




?>