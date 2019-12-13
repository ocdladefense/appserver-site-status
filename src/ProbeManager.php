<?php

class ProbeManager {

    private $savePath = BASE_PATH."/content/sitestatus/results/";

    private $domainRecords;

    private $startTime;
    private $endTime;

    function __construct() {

    }

    function getDomainRecords() {
        return $this->domainRecords;
    }

    function getMicroTime() {
        return microtime(true);
    }

    function getTime() {
        return time();
    }

    function getDate() {
        date_default_timezone_set('America/Los_Angeles');
        $date = getDate();
        $date = date("m-d-Y-H-i");
        return $date;
    }

    // takes a folder path and creates an array of domain record objects (one domain record object per .json file)
    public static function newFromFileSystem($path) {
        $manager = new ProbeManager();

        if(is_dir($path)) {
            $filePaths = FileSystem::list($path."/*.json");
        } else {
            $filePaths = FileSystem::list($path);
        }

        foreach($filePaths as $filePath) {
            $domainRecord = DomainRecord::makeDomainRecord($filePath);
            $manager->domainRecords[$filePath] = $domainRecord;
        }

        return $manager;
    }

    public static function newFromUrl($url, $name) {
        $manager = new ProbeManager();
        
        $domainRecord = $manager->makeDomainRecordFromUrl($url, $name);
        $manager->domainRecords[$url] = $domainRecord;

        return $manager;
    }

    public static function newFromDatabase($objects) {
        $manager = new ProbeManager();

        foreach($objects as $object) {
            $domainRecord = $manager->makeDomainRecordFromUrl($object->url, $object->name);
            $manager->domainRecords[$object->url] = $domainRecord;
        }

        return $manager;
    }

    function makeDomainRecordFromUrl($url, $name) {
        $domainRecord = DomainRecord::makeDomainRecord($url);
        $domainRecord->addName($name);
        return $domainRecord;
    }

    function doValidation() {

        foreach($this->domainRecords as $domainRecord) {

            DomainRecord::validate($domainRecord);
            
        }
    }

    function doProbes($save) {
        $jsonOutput = [];
        $this->startTime = $this->getMicroTime();

        $timeStamp = $this->getTime();

        foreach($this->domainRecords as $domainRecord) {
            // get an array of Probe objects from the domain record
            $probes = $domainRecord->getProbes();

            $probeDate = $this->getDate();
            $probeResults = ProbeManager::probe($domainRecord->domain, $probes);

            // combine the probe results together into one object
            $output = ProbeRenderer::resultCombinedFormat($domainRecord, $probeResults, $probeDate, $timeStamp);

            $jsonOutput[] = $output;

            if($save) {
                // create a name for the result file
                $fileName = $domainRecord->name."-".$probeDate;

                // save the output as a .json file
                FileSystem::save($output, $fileName, $this->savePath, FileSystem::$FILE_TYPE_JSON);
            }
        }

        $this->endTime = $this->getMicroTime();

        return $jsonOutput;
    }

    function probe($domain, $probes) {

        $probeResults = [];

        foreach($probes as $probe) {
            $probeResults[] = $probe->run($domain);
        }

        return $probeResults;
    }

    function renderOutput() {
        print_r("All probes completed successfully in ".($this->endTime - $this->startTime)." seconds! Check '/results' for .JSON file results.");
    }
}

// FOR COMMAND LINE TESTING
// $probeManager = new ProbeManager('C:\\wamp64\\www\\trust\\appserver\\site-json\\');
?>
