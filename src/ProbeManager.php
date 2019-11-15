<?php

class ProbeManager {

    private $savePath = BASE_PATH."/results/";

    private $domainRecords;

    private $startTime;
    private $endTime;

    function __construct() {
        define("SITE_HEALTHY", 1);
        define("SITE_UNHEALTHY", 2);
    }

    function getTime() {
        return microtime(true);
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

    function doValidation() {

        foreach($this->domainRecords as $domainRecord) {

            DomainRecord::validate($domainRecord);
            
        }
    }

    function doProbes() {
        $this->startTime = $this->getTime();
        
        foreach($this->domainRecords as $domainRecord) {
            // get an array of Probe objects from the domain record
            $probes = $domainRecord->getProbes();

            $probeDate = $this->getDate();
            $probeResults = ProbeManager::probe($domainRecord->domain, $probes);

            // combine the probe results together into one object
            $output = ProbeRenderer::ResultCombinedFormat($domainRecord, $probeResults, $probeDate);

            // create a name for the result file
            $fileName = $domainRecord->name."-".$probeDate;

            // save the output as a .json file
            FileSystem::save($output, $fileName, $this->savePath, FileSystem::$FILE_TYPE_JSON);
        }

        $this->endTime = $this->getTime();
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
