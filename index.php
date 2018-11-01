<?php
include_once('vendor/autoload.php');

use Controller\ProcessCompanyController;
header('Content-Type: application/json');

// echo '<pre>';
// $keys = [
//         'companies'=>[
//             'destination'=>array(),
//             'destination_2'=>array(),
//             'xyz'=>array(),
//         ],
//         'company_settings'=>[
//             'account_type'=>array(),
//             'connection_method'=>array(),
//         ],
//         'ivrs'=>[
//             'time'=>[
//                 'ivr_name',
//                 'ivr_basis',
//             ],
//         ],
//         'ivr_settings'=>[
//             '57c5dd20809e5147'=>[
//                 'moh',
//                 'voicemail',
//             ],
//         ],
//         'nodes'=>[
//             '57c5dd20809e5147'=>[
//                 'node_value',
//                 'parent_id',
//             ],
//         ],
//         'departments'=>[
//             '57c5dd207e859871' => ["name","users"],
//             '57c5dd207e909603' => ["name","users"],
//             '57c5dd207e9b1701' => ["name","users"],
//         ],
//         'department_settings'=>[
//             "57c5dd207e859871"=>['max_digits'],
//         ],
//         'languages'=>[
//             '546ef22119da5117'=>["language_name","status"],
//         ],
//         'company_users'=>[
//             '542c021d6745d657'=>['is_enabled','timing_manager'],
//             '57c5dd207e859871'=>['name'],
//         ],
//     ];
//     $keys = json_encode($keys);
// echo $keys; die;
if(!empty($_REQUEST['display_number'])){
    
    $keys = !empty($_REQUEST['keys']) ? $_REQUEST['keys'] : json_encode(array());
    $keys=json_decode($keys, 1);
    $pc = new ProcessCompanyController;
    
    echo json_encode($pc->run($_REQUEST['display_number'], $keys));

}else{
    echo json_encode(array('status'=>false, 'message'=>'Invalid Request'));
}

// 919873832455




?>