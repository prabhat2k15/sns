<?php

class ElasticSearch {

    //function __construct($server = 'http://ip-10-170-21-184.ec2.internal:9200')    
    function __construct($server = 'http://localhost:9200') {
        $this->server = $server;
    }

    //This function returns the elasticsearch results
    function call($path, $http = array()) {
        $content = @file_get_contents($this->server . '/' . $this->index . '/' . $path, NULL, stream_context_create(array('http' => $http)));
        if ($content === FALSE)
            return array(null, 0);
        else
            return array(json_decode($content), 1);
    }

    //curl -X PUT http://localhost:9200/{INDEX}/
    // This function is to create an index
    function create() {
        return $this->call(NULL, array('method' => 'PUT', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n"));
    }

    //curl -X GET http://localhost:9200/{INDEX}/_status
    function status() {
        return $this->call('_status');
    }

    //curl -X GET http://localhost:9200/{INDEX}/{TYPE}/_count -d {matchAll:{}}
    function count() {
        return $this->call($this->type . '/_count', array('method' => 'GET', 'content' => '{ matchAll:{} }'));
    }

    //curl -X PUT http://localhost:9200/{INDEX}/{TYPE}/_mapping -d ...
    function map($data) {
        return $this->call($this->type . '/_mapping', array('method' => 'PUT', 'content' => $data));
    }

    function setting($data) {
        return $this->call($this->index . '/', array('method' => 'POST', 'content' => $data));
    }

    //curl -X PUT http://localhost:9200/{INDEX}/{TYPE}/{ID} -d ...
    function add($id, $data) {
        return $this->call($this->type . '/' . $id, array('method' => 'PUT', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $data));
    }

    function get($id) {
        return $this->call($this->type . '/' . $id, array('method' => 'GET', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n"));
    }

    //curl -X DELETE http://localhost:9200/{INDEX}/
    //Delete an indexed item by ID
    function delete($id) {
        return $this->call($this->type . '/' . $id, array('method' => 'DELETE'));
    }

    //curl -X GET http://localhost:9200/{INDEX}/{TYPE}/_search?q= ...
    function query($q) {  //echo urldecode($this->type . '/_search?' . http_build_query(array('q' => $q)));
        return $this->call($this->type . '/_search?' . http_build_query(array('q' => $q)));
    }

    function query_wresultSize($q, $size = 999) {
        return $this->call($this->type . '/_search?' . http_build_query(array('q' => $q, 'size' => $size)));
    }

    function query_all($query) {
        return $this->call('_search?' . http_build_query(array('q' => $q)));
    }

    function query_all_wresultSize($query, $size = 999) {
        return $this->call('_search?' . http_build_query(array('q' => $q, 'size' => $size)));
    }

    function query_highlight($q) {
        return $this->call($this->type . '/_search?' . http_build_query(array('q' => $q)), array('header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => '{"highlight":{"fields":{"field_1":{"pre_tags" : ["<b style=\"background-color:#C8C8C8\">"], "post_tags" : ["</b>"]}, "field_2":{"pre_tags" : ["<b style=\"background-color:#C8C8C8\">"], "post_tags" : ["</b>"]}}}}'));
    }

    //  Description: This functions POSTs the request to ES and searches according to I/p
    //  John Chornelius
    function elasticsearch($json_doc, $index) {

        //$baseUri = $this->server . '/' . $this->index . '/' . $doc_type . '/_search';
        $baseUri = $this->server . '/' . $index . '/_search';
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri);
        curl_setopt($ci, CURLOPT_TIMEOUT, 2000);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);
        $response = curl_exec($ci);
        return $response;
    }

    function fetchRecords($json_doc, $index, $doc_type) {

        $baseUri = $this->server . '/' . $index . '/' . $doc_type . '/_search';
        //$baseUri = $this->server . '/' . $index . '/_search';
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri);
        curl_setopt($ci, CURLOPT_TIMEOUT, 2000);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);
        $response = curl_exec($ci);
        return $response;
    }

    //  Description: This functions POSTs the request to ES and counts according to I/p
    //  John Chornelius
    function elasticcount($json_doc, $index) {

        //$baseUri = $this->server . '/' . $this->index . '/' . $doc_type . '/_search';
        $baseUri = $this->server . '/' . $index . '/_count';
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri);
        curl_setopt($ci, CURLOPT_TIMEOUT, 2000);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);
        $response = curl_exec($ci);
        return $response;
    }

    function elasticupdate($json_doc, $index, $type, $id) {

        $baseUri = $this->server . '/' . $index . '/' . $type . '/' . $id . '/_update';
        //$baseUri = $this->server . '/' . $index . '/_search';
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri);
        curl_setopt($ci, CURLOPT_TIMEOUT, 2000);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);
        $response = curl_exec($ci);
        return $response;
    }

    function elasticadd($json_doc, $index, $type, $id) {

        $baseUri = $this->server . '/' . $index . '/' . $type . '/' . $id . '/';
        //$baseUri = $this->server . '/' . $index . '/_search';
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri);
        curl_setopt($ci, CURLOPT_TIMEOUT, 2000);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);

        $response = curl_exec($ci);
        return $response;
    }

}

?>