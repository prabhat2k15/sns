<?php
namespace Model;
// include_once 'database.php';

class Query {

    public $db;

    function __construct() {
        $this->db = new Database();
    }

    # saves the incoming reqeuest into DB & pick uuid for the reference

    function _save_request_in_db($company_id, $type = null) {
        $id = $this->uuid();
        $insert_query = "INSERT INTO reload_requests (id, company_id, type, loaded_into, created, modified)
                         VALUES ('$id', '$company_id', $type, 0, '" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "')";

        $insert = $this->db->query($insert_query);
        if ($insert)
            return true;
        else
            return false;
    }

    function _fetch_display_number($company_id) {
        $query = "SELECT display_number FROM companies where id = '$company_id' AND status = 1 AND account_type != 6";
        $company = $this->db->customSelect($query);

        return $company;
    }

    function _fetch_company_details($company_id) {
        $query = "SELECT id, display_number, destination, destination_2, is_myoperator, company_name, time_zone, account_type, country_code, number_type, created FROM companies where id = '$company_id' AND status = 1 AND account_type != 6";
        $company = $this->db->customSelect($query);

        return $company;
    }

    function _fetch_company_event_push($company_id) {
        $query = "select * from api_pushes where company_id = '$company_id' AND is_active = 1 AND service_type = 2";
        $company = $this->db->customSelect($query);
        if (!empty($company)) {
                return true;
        } else {
                return false;
        }
    }

    function _fetch_company_settings($company_id) {
        $query = "SELECT property_key, property_value FROM company_settings where company_id = '$company_id'";
        $company_settings = $this->db->customSelect($query);

        return $company_settings;
    }

    function _fetch_ivrs($company_id) {
        $query = "SELECT I.*, T.start_time, T.end_time, T.sun, T.mon, T.tue, T.wed,
                  T.thu, T.fri, T.sat FROM ivrs I
                  INNER JOIN timing_managers T ON T.type_id = I.id AND T.type = 'ivr'
                  WHERE I.company_id = '$company_id' AND I.status = 1";

        $tmp_ivrs = $this->db->customSelect($query);

        $ivrs = array();
        if ($tmp_ivrs) {
            foreach ($tmp_ivrs as $tmp) {
                $keys[] = $tmp['id'];
                $tmp_ivr = array('ivr_name' => $tmp['ivr_name'], 'ivr_basis' => $tmp['ivr_basis'], "ivr_id" => $tmp['id']);
                $day = 0;
                $day = ($tmp['sun'] == '1') ? $day + 1 : $day;
                $day = ($tmp['mon'] == '1') ? $day + 2 : $day;
                $day = ($tmp['tue'] == '1') ? $day + 4 : $day;
                $day = ($tmp['wed'] == '1') ? $day + 8 : $day;
                $day = ($tmp['thu'] == '1') ? $day + 16 : $day;
                $day = ($tmp['fri'] == '1') ? $day + 32 : $day;
                $day = ($tmp['sat'] == '1') ? $day + 64 : $day;
                $tmp_ivr['day'] = $day;

                if (empty($ivr[$tmp['id']]['start_time'])) {
                    $tmp_ivr['start_time'] = $this->_calculate_seconds($tmp['start_time']);
                }
                if (empty($ivr[$tmp['id']]['end_time'])) {
                    $tmp_ivr['end_time'] = $this->_calculate_seconds($tmp['end_time']);
                }
                if (!empty($ivrs[$tmp['ivr_basis']])) {
                    $decode = json_decode($ivrs[$tmp['ivr_basis']], true);
                    $decode[] = $tmp_ivr;
                    $ivrs[$tmp['ivr_basis']] = json_encode($decode);
                } else {
                    // $ivrs[$tmp['ivr_basis']] = json_encode(array($tmp_ivr));
                    $ivrs[$tmp['ivr_basis']] = json_encode(($tmp_ivr));
                }
            }
        } else {
		return array();
	}
        $response = array("keys" => $keys, "response" => $ivrs);
        return $response;
    }

    function _fetch_ivr_settings($ivr_keys) {
        if(empty($ivr_keys)){
            return null;
        }
        #fetch settings
        $settings = array();
        foreach ($ivr_keys as $key => $ivr) {
            $sql1 = "SELECT property_key, property_value FROM ivr_settings where ivr_id = '$ivr'";
            $tmp_settings = $this->db->customSelect($sql1);
            if ($tmp_settings) {
                $tmp = array();
                foreach ($tmp_settings as $value) {
                    $tmp[$ivr][$value['property_key']] = $value['property_value'];
                }
                $settings[$ivr] = json_encode($tmp[$ivr]);
            } else {
                $settings[$ivr] = "";
            }
        }
        return $settings;
    }

    function _fetch_nodes($company_id, $ivr_keys) {
        if(empty($ivr_keys)){
            return null;
        }
        $node_ivr = $final_node = array();
        foreach ($ivr_keys as $ivr_id) {
            $sql = "SELECT N.id, N.node_value, parent_id, lft, rght, plugin, pre_processor, post_processor FROM nodes N
                    LEFT OUTER JOIN sounds S ON S.node_id = N.id AND S.status = 1
                    WHERE N.company_id = '$company_id' AND N.property = '$ivr_id' AND N.status = 1 ORDER BY N.node_value ASC";
            $node = $this->db->customSelect($sql);

            if ($node) {
                $node_ivr[$ivr_id] = $node;
                foreach ($node as $node_detail) {
                    # fetch node properties
                    $sql1 = "SELECT property_key, property_value FROM node_properties P WHERE node_id = '" . $node_detail['id'] . "'";
                    $node_properties = $this->db->customSelect($sql1);
                    # fetch sounds
                    $sql2 = "SELECT * FROM sounds S WHERE node_id = '" . $node_detail['id'] . "' AND status = 1";
                    $sounds = $this->db->customSelect($sql2);

                    $extra['sounds'] = $sounds;
                    foreach ($node_properties as $property) {
                        $properties[$property['property_key']] = $property['property_value'];
                    }
                    $extra['properties'] = $properties;
                    $final_node[$ivr_id][] = array_merge($node_detail, $extra);
		    unset($properties);
                    unset($sounds);
                    unset($node_properties);
                }
                $final_node[$ivr_id] = json_encode($final_node[$ivr_id]);
            } else {
                //$final_node[$ivr_id] = json_encode(array());
                $final_node[$ivr_id] = "";
            }
        }

        return $final_node;
    }

    function _fetch_company_users($company_id) {
        $user = array();
        $sql = "SELECT C.uuid, C.contact, C.contact_2, C.contact_type, C.contact_type_2, C.is_enabled, C.contact_country, C.contact_2_country, C.extension, C.email, 1 AS user_type FROM company_users C
                WHERE C.company_id = '$company_id' AND C.is_active IN (1, 4) AND C.is_enabled = 1";

        $users = $this->db->customSelect($sql);
	if (count($users) > 500) {
		return false;
	}
        if (!empty($users)) {
            foreach ($users as $user) {

                $company_users[$user['uuid']] = array("uuid" => $user['uuid'], "contact" => $user["contact"], "contact_2" => $user['contact_2'], "contact_country" => $user['contact_country'],
                    "contact_2_country" => $user['contact_2_country'], "contact_type" => $user['contact_type'], "contact_type_2" => $user['contact_type_2'], "extension" => $user['extension'], "email" => $user['email'], 'user_type' => $user['user_type'], "is_enabled" => $user['is_enabled']);

                $company_sql = "SELECT C.display_number FROM company_users U
                            INNER JOIN companies C ON U.company_id = C.id
                            WHERE U.contact = '" . $user["contact"] . "' AND U.is_active >0 AND C.status= 1 AND account_type IN (1, 2, 3)";
                $service_numbers = $this->db->customSelect($company_sql);
                $company_users[$user['uuid']]['linked_companies'] = array_values($service_numbers);

                $sql1 = "SELECT * FROM timing_managers WHERE type_id = '" . $user['uuid'] . "' AND type = 'user'";
                $timings = $this->db->customSelect($sql1);
        // echo "\n";
        $z = 0;
                if ($timings) {
                    foreach ($timings as $timing) {
                        $day = 0;
                        $day = ($timing['sun'] == '1') ? $day + 1 : $day;
                        $day = ($timing['mon'] == '1') ? $day + 2 : $day;
                        $day = ($timing['tue'] == '1') ? $day + 4 : $day;
                        $day = ($timing['wed'] == '1') ? $day + 8 : $day;
                        $day = ($timing['thu'] == '1') ? $day + 16 : $day;
                        $day = ($timing['fri'] == '1') ? $day + 32 : $day;
                        $day = ($timing['sat'] == '1') ? $day + 64 : $day;
                        $company_users[$user['uuid']]['timing_manager'][] = array('day' => $day, 'start_time' => $timing['start_time'], 'end_time' => $timing['end_time']);
			//print_r($company_users[$user['uuid']]);echo "\n z : ". $z++ . " \n";
                    }
                } else {
			if ($user['is_enabled'] == 1) {
				$company_users[$user['uuid']]['timing_manager'][] = array('day' => 127, 'start_time' => '18:30:00', 'end_time' => '18:30:00');
			}
		}

                $company_users[$user['uuid']] = json_encode($company_users[$user['uuid']]);
            }
        } else {
            return false;
        }

	# Fetch data for departments
        $sql = "SELECT
                    D.id,
                    D.name,
                    D.extension,
                    C.display_number,
                    GROUP_CONCAT(U.uuid) user_ids,
                    2 AS user_type
                FROM
                    company_users U
                        INNER JOIN
                    departments D USING (company_id)
                        INNER JOIN
                    companies C ON U.company_id = C.id
                WHERE
                    U.company_id = '$company_id'
                        AND U.is_active IN (1 , 4)
                        AND U.is_enabled IN (1, 2)
                        AND D.status = 1
                        AND C.status = 1
                        AND account_type IN (1, 2, 3)
                GROUP BY U.company_id , D.id";
        $departments = $this->db->customSelect($sql);

        if (!empty($departments)) {
            foreach ($departments as $department) {
                $company_users[$department['id']] = array("id" => $department['id'], "name" => $department["name"], "extension" => $department['extension'], 'display_number' => $department['display_number'], "user_ids" => explode(',', $department['user_ids']), 'user_type' => $department['user_type']);

                $company_users[$department['id']] = json_encode($company_users[$department['id']]);
            }
        } else {
            return;
        }

        return $company_users;
    }

    function _fetch_department_settings($company_id) {
        $sql = "SELECT D.id, S.property_key, S.property_value FROM departments D
                INNER JOIN department_settings S ON S.department_id = D.id
                WHERE D.company_id = '$company_id' AND D.status = 1";
        $department_settings = $this->db->customSelect($sql);

        if (!empty($department_settings)) {
                foreach ($department_settings as $property) {
                        $properties[$property['id']][$property['property_key']] = $property['property_value'];
                }

                # frame json
                foreach ($properties as $key => $property) {
                        $data_set[$key] = json_encode($property);
                }
        } else {
                return array();
        }
        return $data_set;
    }

    function _fetch_departments($company_id) {
        //$company_id = "54b7b270c1c8e301";
        $sql = "SELECT * FROM departments D
                WHERE D.company_id = '$company_id' AND D.status = 1";

        $departments_raw = $this->db->customSelect($sql);

        //print_r($departments_raw);echo "\n";
        $departments = array();
        if ($departments_raw) {
            foreach ($departments_raw as $department) {
                $dept_users = array();
                $sql_1 = "SELECT U.user_id, U.language_id FROM department_users U
                INNER JOIN company_users C ON C.uuid = U.user_id
                WHERE U.department_id = '" . $department['id'] . "' AND C.is_active IN (1, 4) AND C.is_enabled IN (1, 2) ORDER BY U.id ASC";

                $department_users = $this->db->customSelect($sql_1);

		if (count($department_users) > 500) {
			return false;
		}

                $dept_users = array();
                if ($department_users) {
                    foreach ($department_users as $dept_user) {
                        $dept_users[] = array("user_id" => $dept_user['user_id'], "language_id" => $dept_user['language_id']);
                    }
                    //$dept_users = array_column($department_users, 'user_id');
                }

                $departments[$department['id']]['name'] = $department['name'];
                $departments[$department['id']]['users'] = $dept_users;
                $departments[$department['id']] = json_encode($departments[$department['id']]);
            }
            return $departments;
        } else {
            return;
        }
    }

    function _fetch_languages($company_id) {
        $sql = "SELECT * FROM languages WHERE company_id = '$company_id' AND status = 1";
        $languages = $this->db->customSelect($sql);

        if ($languages) {
            foreach ($languages as $language) {
                $tmp_languages[$language['ivr_id']][] = $language;
            }
            foreach ($tmp_languages as $key => $tmp_language) {
                $final_language[$key] = json_encode($tmp_language);
            }
            return $final_language;
        } else {
            //return json_encode(array());
            return;
        }
    }

    function uuid() {
        return uniqid() . rand(111, 999);
    }

    function _calculate_seconds($str_time) {
        $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "$1:$2:00", $str_time);
        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
        $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
        return $time_seconds;
    }

    function _pick_request_from_db() {
        $current_time = time();
        $sql = "SELECT * FROM reload_queues WHERE is_processed = 9 AND load_at <= $current_time";
        // echo $sql. "\n";
        $requests = $this->db->customSelect($sql); 
        // print_r($requests);
        $current_requests = array();
        if (!empty($requests)) {
            foreach ($requests as $request) {
                $update_request = "UPDATE reload_queues SET is_processed = 8 WHERE id = '" . $request['id'] . "'";
                $result = $this->db->custom_update($update_request); // returns affected rows
                if ($result) {
                    $current_requests[] = $request;
                }
            }
        }
        return $current_requests;
    }


   function _pick_request_emergency() {
        $current_time = time();
        $sql = "SELECT * FROM reload_queues WHERE is_processed = 8 AND created >= '2016-11-04 00:00:00' LIMIT 100";
        $requests = $this->db->customSelect($sql);
        $current_requests = array();
        if (!empty($requests)) {
            foreach ($requests as $request) {
                $update_request = "UPDATE reload_queues SET is_processed = 7 WHERE id = '" . $request['id'] . "'";
		// print_r($update_request);echo "\n";
                $result = $this->db->custom_update($update_request); // returns affected rows
                if ($result) {
                    $current_requests[] = $request;
                }
            }
        }
        return $current_requests;
    }

    function _pick_companies($offset = 0) {
        # query for S3
	//$sql = "SELECT DISTINCT display_number, id as `company_id` FROM `companies` where id = '1' LIMIT $offset, 100";
        $sql = "SELECT DISTINCT display_number, id as `company_id` FROM companies WHERE status = 1 AND account_type IN (1, 2) ORDER BY id ASC LIMIT $offset, 50";
        //$sql = "SELECT id as `company_id`, display_number FROM companies WHERE status = 1 AND account_type != 6 LIMIT $offset, 100";
        $requests = $this->db->customSelect($sql);
        return $requests;
    }

    function _pick_company($display_number) {
        # query for S3
        $sql = "SELECT display_number, id as `company_id` FROM companies WHERE display_number = '$display_number' AND status = 1 AND account_type != 6";
        //$sql = "SELECT id as `company_id`, display_number FROM companies WHERE status = 1 AND account_type != 6 LIMIT $offset, 100";
        $requests = $this->db->customSelect($sql);
        return $requests;
    }

    function _fetch_ivr_keys($company_id) {
        $sql = "SELECT id FROM ivrs WHERE company_id = '$company_id' AND status = 1";
        $ivrs = $this->db->customSelect($sql);
        if (!empty($ivrs)) {
            foreach ($ivrs as $tmp) {
                $ivr_keys[] = $tmp['id'];
            }
        } else {
            return;
        }
        return $ivr_keys;
    }

    function _update_load_servers($request_id, $load_into) {
        $update_request = "UPDATE reload_queues SET load_into = $load_into WHERE id = '" . $request_id . "'";
        $result = $this->db->custom_update($update_request); // returns affected rows
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    function _remove_request($request_id) {

        # insert into dumps table and delete it from here
        $insert_query = "INSERT INTO dump_reload_queues SELECT * FROM reload_queues WHERE id = '" . $request_id . "'";
        $this->db->query($insert_query);

        $update_request = "DELETE FROM reload_queues WHERE id = '" . $request_id . "'";
        $result = $this->db->custom_update($update_request);  // returns affected rows
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    function _fetch_expired_number($company_id) {
        $sql = "SELECT display_number FROM companies where id = '$company_id' AND status = 0";
        $company = $this->db->customSelect($sql);
        if (!empty($company)) {
            # remove _h4
            $tmp = str_replace('_h4', '', $company[0]['display_number']);
            if (!empty($tmp))
                return $tmp;
            else
                return false;
        } else {
            return false;
        }
    }


}
