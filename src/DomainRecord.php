<?php

class DomainRecord {
    private static $DOMAIN_RECORD_VALID = 0;
    private static $DOMAIN_RECORD_INVALID = 1;

    function __construct() {
    }

    // create a domain record object from a filePath or a url
    public static function makeDomainRecord($filePath_or_url) {
        
        $domainRecord = new DomainRecord();

        // treat filePaths and urls differently
        if(file_exists($filePath_or_url)) {
            $filePath = $filePath_or_url;
            $object = FileSystem::toObject($filePath, FileSystem::$FILE_TYPE_JSON);
            
            // convert $object to a DomainRecord object
            if(isset($object->name)) {
                $name = $object->name;
            }

            if(isset($object->domain)) {
                $url = $object->domain;
            }

            if(isset($object->comments)) {
                $comments = $object->comments;
            }

            if(isset($object->probes)) {
                foreach($object->probes as $probe) {
                    $domainRecord->addPath($probe->path, $probe->expectedStatusCode);
                }
            }
        } else {
            $name = $filePath_or_url;
            $url = $filePath_or_url;
            $comments = "";

            $path = "/";
            $expectedStatusCode = 200;
            $domainRecord->addPath($path, $expectedStatusCode);

            $path = "/therebetternotbeasubdomainnamedthisotherwisethisisaWEIRDsite";
            $expectedStatusCode = 404;
            $domainRecord->addPath($path, $expectedStatusCode);
        }

        $domainRecord->addName($name);
        $domainRecord->addDomain($url);
        $domainRecord->addComments($comments);

        // for unit testing
        return $domainRecord;
    }

    function addName($name) {
        $this->name = $name;
    }

    function addDomain($url) {
        $this->domain = $url;
    }

    function addComments($comments) {
        $this->comments = $comments;
    }

    function addPath($path, $expectedStatusCode) {
        $object = new stdClass();
        $object->path = $path;
        $object->expectedStatusCode = $expectedStatusCode;

        $this->probes[] = $object;
    }

    public static function validate($domainRecord) {
        if(self::getState($domainRecord) == self::$DOMAIN_RECORD_INVALID) {
            throw new DomainRecordException($domainRecord, self::$DOMAIN_RECORD_INVALID);
        }
    }

    public static function getState($domainRecord) {

        // check to see that a domain key exists and that the value starts with "http". If either is false, validation should fail
        if(!isset($domainRecord->domain)) {
            return self::$DOMAIN_RECORD_INVALID;
        } 
        
        // if (!preg_match("/^http/", $domainRecord->domain)) {
        //     return self::$DOMAIN_RECORD_INVALID;            
        // }

        // check that there is a "probes" key. If not, validation should fail and return immediately
        if(!isset($domainRecord->probes)) {
            return self::$DOMAIN_RECORD_INVALID;
        }

        // check that there is a "path" key in every probe. If not, validation should fail
        foreach($domainRecord->probes as $probe) {
            if(!isset($probe->path))
                return self::$DOMAIN_RECORD_INVALID;
        }

        return self::$DOMAIN_RECORD_VALID;
    }

    function getProbes() {
        $probes = [];
        
        foreach($this->probes as $probe) {
            $newProbe = new Probe($probe);
            $probes[] = $newProbe;
        }
        
        return $probes;
    }
}