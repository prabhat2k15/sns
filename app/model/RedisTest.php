<?php

namespace Model;
// Predis\Autoloader::register();
// include(__DIR__.'/../../logconf.php');
use Predis\Client;
use Logger;
class RedisTest
{
    private $client;
    private $host;
    private $port;
    private $response;
    public function __construct()
    {
        Logger::configure(__DIR__.'/../../logconf.php');

        $this->host = $_ENV['REDIS'][$_ENV['MODE']]['ip'];
        $this->port = $_ENV['REDIS'][$_ENV['MODE']]['port'] ?? 6379;
        
        echo __FUNCTION__;
        $this->connect();

        $this->response = array(
            'status'=>false,
            'message'=>'Something went wrong',
            'method'=>__FUNCTION__
        );
        // print_r($this->_save_request_in_db(1));
    }
    public function connect()
    {
        try{
            $this->client = new Client([
                'scheme' => 'tcp',
                'host'   => $this->host,
                'port'   => $this->port,
            ]);
            $this->client->set('foo', 'ball');
            $value = $this->client->get('foo');
            print_r($value);
            // Same set of parameters, passed using an URI string:
            
        }catch(\Predis\Connection\ConnectionException $pe){
            echo $pe->getMessage();
            print_r(('connection failed'));
        }
       
    }

    function _save_request_in_db($company_id, $type = null) 
        {
            $this->response['method'] = __FUNCTION__;
            $id = uniqid();
            $insert_query = "INSERT INTO reload_requests (id, company_id, type, loaded_into, created, modified)
                             VALUES ('$id', '$company_id', $type, 0, '" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "')";
    
            $insert = 1;//$this->db->query($insert_query);
            if ($insert){
                $this->response['status'] = true;
                $this->response['message'] = $id.' Inserted into DB';
            }
            
            return $this->response;
        }
    public function redisPush()
    {
        $hash = '919873832455:details';
        $data = '{
            "id":"5ab25ca5d1801476",
            "display_number":"919873832455",
            "destination":"41160166",
            "destination_2":"33576995",
            "is_myoperator":"1",
            "company_name":"Adesh Test Account",
            "time_zone":"+05:30",
            "account_type":"6",
            "country_code":"+91",
            "number_type":"2",
            "created":"2018-03-21 13:22:45",
            "event_push":0
        }';
        // print_r(json_decode($data));
        // die;
        // $this->client->del('123');
        $this->client->hmset($hash, json_decode($data,1));
        $result = $this->client->hgetall($hash);
        print_r($result);
        echo 'done';
        $logger = Logger::getLogger("main");
        $logger->error(json_encode(array(123,123,13,)));
    }
}