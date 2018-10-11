<?php

// require "predis/autoload.php";
// Predis\Autoloader::register();
namespace Model;

class redis_loader {

    function __construct() {

    }

    public function run($ip, $key, $value) {

        try {
            //$redis = new Predis\Client();
            //print_r($redis);
            // This connection is for a remote server
            $redis = new PredisClient(array(
                "scheme" => "tcp",
                "host" => $ip,
                "port" => 6379
            ));



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

    public function hmset_array($ip, $hash, $input) {

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

    public function hmset_key($ip, $hash, $key, $value) {

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

    public function set_key($ip, $key, $value) {

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


    public function delete($ip, $hash) {
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

    public function hash_delete($ip, $hash) {
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
	
    public function expire($ip, $hash, $seconds) {
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
