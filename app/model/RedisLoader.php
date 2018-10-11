<?php

namespace Model;

class RedisLoader 
{
    private $client;
    private $host;
    private $port;

    public function __construct()
    {
        $this->host = $_ENV['REDIS'][$_ENV['MODE']]['ip'];
        $this->port = $_ENV['REDIS'][$_ENV['MODE']]['port'] ?? 6379;
        $this->connect();
    }

    public function connect()
    {
        try{
            $this->client = new Client([
                'scheme' => 'tcp',
                'host'   => $this->host,
                'port'   => $this->port,
            ]);
            $this->client->set('foo', 'hello');
            $value = $this->client->get('foo');
            print_r($value);
            // Same set of parameters, passed using an URI string:
            
        }catch(\Predis\Connection\ConnectionException $pe){
            echo $pe->getMessage();
            print_r(('connection failed'));
        }
       
    }


    public function run($ip, $key, $value) 
    {
        try {
            // $redis = new PredisClient(array(
            //     "scheme" => "tcp",
            //     "host" => $ip,
            //     "port" => 6379
            // ));

            $value = $redis->get('message');
            print($value);
            echo "\n";

            $redis->set('message', 'Its ME');

            // gets the value of message
            $value = $redis->get('message');

            // Hello world
            print($value);
            echo "\n";

            echo ($redis->exists('message')) ? "Oui" : "please populate the message key";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function hmset_array($ip, $hash, $input) 
    {
        try {
            // This connection is for a remote server
            $redis = new Predis\Client(array(
                "scheme" => "tcp",
                "host" => $ip,
                "port" => 6379
            ));

            $result = $redis->hmset($hash, $input);
	    //print_r("success result -  ");print_r($result);
            return true;
            return $result;
        } catch (Exception $e) {
	    //print_r("\ninto exception - 1\n");
	    try {
	    	$result = $redis->hmset($hash, $input);
	    } catch (Exception $e) {
	    	//print_r("into exception - 2\n");
            	//print_r($e->getMessage());
		return false;
	    }
        }
    }

    public function hmset_key($ip, $hash, $key, $value) 
    {
        try {
            // This connection is for a remote server
            $redis = new PredisClient(array(
                "scheme" => "tcp",
                "host" => $ip,
                "port" => 6379
            ));

            $redis->hmset($hash, $key, $value);
	    return true;
        } catch (Exception $e) {
	    try {
	    	$redis->hmset($hash, $key, $value);
		return true;
	    } catch (Exception $e) {
            	print_r($e->getMessage());
            	return false;
	    }
        }
    }

    public function set_key($ip, $key, $value) 
    {
        try {
            // This connection is for a remote server
            $redis = new Predis\Client(array(
                "scheme" => "tcp",
                "host" => $ip,
                "port" => 6379
            ));

            $result = $redis->set($key, $value);
	    return true;
        } catch (Exception $e) {
	    try {
	    	$result = $redis->set($key, $value);
		return true;
	    } catch (Exception $e) {
            	print_r($e->getMessage());
            	return false;
	    }
        }
    }


    public function delete($ip, $hash) 
    {
        try {
            // This connection is for a remote server
            $redis = new Predis\Client(array(
                "scheme" => "tcp",
                "host" => $ip,
                "port" => 6379
            ));

            $redis->delete($hash);
        } catch (Exception $e) {
           return $e->getMessage();
        }
    }

    public function hash_delete($ip, $hash) 
    {
        try {
            // This connection is for a remote server
            $redis = new Predis\Client(array(
                "scheme" => "tcp",
                "host" => $ip,
                "port" => 6379
            ));

            $result = $redis->del($hash);
	    return $result;
        } catch (Exception $e) {
	    try {
		$result = $redis->del($hash);
                return $result;
	    } catch (Exception $e) {
            	print_r($e->getMessage());
		return false;
	    }
        }
    }
	
    public function expire($ip, $hash, $seconds) 
    {
        try {
            // This connection is for a remote server
            $redis = new Predis\Client(array(
                "scheme" => "tcp",
                "host" => $ip,
                "port" => 6379
            ));

            $result = $redis->expire($hash, $seconds);
            return $result;
        } catch (Exception $e) {
            try {
                $result = $redis->expire($hash, $seconds);
                return $result;
            } catch (Exception $e) {
                print_r($e->getMessage());
                return false;
            }
        }
    }

} 