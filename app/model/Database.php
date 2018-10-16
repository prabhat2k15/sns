<?php
namespace Model;

use PDO;
use Logger;
use Dotenv\Dotenv;

class Database {

    public $hostname, $dbname, $username, $password, $conn;

    function __construct() {
        $this->host_name = getenv('DB_HOST');//"internal-myop-internal-galera-1243103973.ap-south-1.elb.amazonaws.com";
        $this->dbname = getenv('DB_NAME');
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASSWORD');
        try {
            $this->conn = new PDO("mysql:host=$this->host_name;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        $this->conn->setAttribute( PDO::ATTR_PERSISTENT, true );
            $this->conn->exec("use ".$this->dbname.";");//"use myoperator;");//????????????
        } catch (\PDOException $e) {
            echo json_encode(array('status'=>false,'message'=>$e->getMessage()));
            $this->log->warn('!!!!!! PDO EXCEPTION !!!!!!!'.$e->getMessage());
            exit;
        }
    }

    function connect() {
	try {
	    $this->conn = new PDO("mysql:host=$this->host_name;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute( PDO::ATTR_PERSISTENT, true );
            $this->conn->exec("use myoperator;");
	    return true;
	} catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    function query($sql) {
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute();
        return $result;
    }

    function run($sql, $array) {
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute($array);
    }

    function customSelect($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // assuming $result == true
            return $rows;
        } catch (PDOException $e) {
            #echo 'Error: ' . $e->getMessage();
	    $this->connect();
	    $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // assuming $result == true
            return $rows;
        }
    }

    function custom_update($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount(); // 1
        } catch (PDOException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    function select($tbl, $cond = '', $mem = false, $ttl = 1800) {
        $sql = "SELECT * FROM $tbl";
        if ($cond != '') {
            $sql .= " WHERE $cond ";
        }

        try {
            if ($mem) {
                $key = md5($sql);
                $rows = $this->memcache->get($key);
                if ($rows) {
                    return $rows;
                } else {

                    $stmt = $this->conn->prepare($sql);
                    $result = $stmt->execute();
                    $rows = $stmt->fetchAll(); // assuming $result == true   
                    $this->memcache->set($key, $rows, false, $ttl);
                    return $rows;
                }
            } else {
                $stmt = $this->conn->prepare($sql);
                $result = $stmt->execute();
                $rows = $stmt->fetchAll(); // assuming $result == true   
                return $rows;
            }
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    function num_rows($rows) {
        $n = count($rows);
        return $n;
    }

    function delete($tbl, $cond = '') {
        $sql = "DELETE FROM `$tbl`";
        if ($cond != '') {
            $sql .= " WHERE $cond ";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount(); // 1
        } catch (PDOException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    function insert($tbl, $arr) {
        $sql = "INSERT INTO $tbl (`";
        $key = array_keys($arr);
        $vals = array_values($arr);
        foreach ($vals as $val_a) {
            $val[] = trim($val_a);
        }
        $sql .= implode("`, `", $key);
        $sql .= "`) VALUES ('";
        $sql .= implode("', '", $val);
        $sql .= "')";

        $sql1 = "SELECT MAX( id ) FROM  `$tbl`";
        try {
            //echo "\n";print_r($sql);echo "\n";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt2 = $this->conn->prepare($sql1);
            $stmt2->execute();
            $rows = $stmt2->fetchAll(); // assuming $result == true
            return $rows[0][0];
        } catch (PDOException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    function custominsert($tbl, $arr) {
        $sql = "INSERT INTO $tbl (`";
        $key = array_keys($arr);
        $vals = array_values($arr);
        foreach ($vals as $val_a) {
            $val[] = trim($val_a);
        }
        $sql .= implode("`, `", $key);
        $sql .= "`) VALUES ('";
        $sql .= implode("', '", $val);
        $sql .= "')";

        $sql1 = "SELECT MAX( id ) FROM  `$tbl`";
        try {
            // echo "\n";print_r($sql);echo "\n";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt2 = $this->conn->prepare($sql1);
            $stmt2->execute();
            $rows = $stmt2->fetchAll(); // assuming $result == true
            return $rows[0][0];
        } catch (PDOException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    function update($tbl, $arr, $cond) {
        $sql = "UPDATE `$tbl` SET ";
        $fld = array();
        foreach ($arr as $k => $v) {
            $fld[] = "`$k` = '$v'";
        }
        $sql .= implode(", ", $fld);
        $sql .= " WHERE " . $cond;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount(); // 1
        } catch (PDOException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    function check_MSG_Balance($company) {

        $sql = "select property_value from company_settings where company_id='$company' and property_key='balance_left'";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $return = 0;
            if ($rows)
                $return = $rows['property_value'];
            return $return;
        } catch (PDOException $e) {
            return 'Error:' . $e->getMessage();
        }
    }
    
    function _trail() {

        $sql = "SELECT * FROM `api_pushes` GROUP BY company_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $return = 0;
            if ($rows)
                $return = $rows;
            return $return;
        } catch (PDOException $e) {
            return 'Error:' . $e->getMessage();
        }
    }

    function _is_number_exists($number, $old_did) {

        $sql = "SELECT D.id from number_pool N
                INNER JOIN did_pool D ON D.number_pool_id = N.id
                WHERE N.number = :number AND D.did = :old_did";

        try {
            # prepare query
            $stmt = $this->conn->prepare($sql);

            # bind parameters
            $stmt->bindParam(':number', $number, PDO::PARAM_STR);
            $stmt->bindParam(':old_did', $old_did, PDO::PARAM_STR);

            # run the query
            $stmt->execute();

            # fetch the result
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $return = 0;
            # return data
            if ($rows)
                $return = $rows['id'];
            return $return;
        } catch (PDOException $e) {
            return 'Error:' . $e->getMessage();
        }
    }

    function _check_duplicate($did) {

        $sql = "SELECT id from did_pool WHERE did = :did";

        try {
            # prepare query
            $stmt = $this->conn->prepare($sql);

            # bind parameters
            $stmt->bindParam(':did', $did, PDO::PARAM_STR);

            # run the query
            $stmt->execute();

            # fetch the result
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $return = 0;
            # return data
            if ($rows)
                $return = true;
            return $return;
        } catch (PDOException $e) {
            return 'Error:' . $e->getMessage();
        }
    }

    function _update_number_did($id, $old_did, $new_did) {

        $sql = "UPDATE did_pool SET did = :new_did WHERE did = :old_did AND id = :id";

        try {
            # prepare/frame query
            $stmt = $this->conn->prepare($sql);

            # bind parameters
            $stmt->bindParam(':new_did', $new_did, PDO::PARAM_STR);
            $stmt->bindParam(':old_did', $old_did, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);

            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return 'Error:' . $e->getMessage();
        }
    }

}
