<?php
// this class is used to create a new customized exception when validation in Probe fails
class DomainRecordException extends Exception {
    
    function __construct($domainRecord, $status) {
        $this->setMessage($domainRecord->domain." is invalid. Please check that the file has a 'domain' key with a 
        value that begins with 'http' and a 'probes' key that contains an array of objects, each with a 'path'.");
    }

    public function setMessage($message){
        $this->message = $message;
    }
}