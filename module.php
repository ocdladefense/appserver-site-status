<?php

define("SITE_HEALTHY", 1);
define("SITE_UNHEALTHY", 2);

define("RESULTS_PATH", BASE_PATH."/content/sitestatus/results");

class SiteStatusModule extends Module {
    private $deps = array();

    public function __construct() {
        parent::__construct();

        $this->name = "sitestatus";
        $this->routes = siteStatusModRoutes();
        $this->dependencies = $this->deps;
        $this->files = siteStatusModRoutes()["init-probemanager"]["files"];
    }

}

function siteStatusModRoutes() {
    $siteStatusModRoutes = array(
        "init-probemanager" => array(
            "callback" => "runProbes",
            "files" => array(
                "FileSystem.php",
                "DomainRecord.php",
                "ProbeManager.php",
                "DomainRecordManagerException.php",
                "Probe.php",
                "ProbeException.php",
                "ProbeRenderer.php",
                "ProbeResult.php",
                "ProbeResultCombinedFormat.php"
            )
        ),
        "site-statuses" => array(
            "callback" => "allSiteStatus",
            "files" => array(
                "FileSystem.php"
            )
        )
    );
    return $siteStatusModRoutes;
}

function runProbes() {
    
    $probeManager = ProbeManager::newFromFileSystem(BASE_PATH."/site-json");
    $probeManager->doValidation();
    $probeManager->doProbes();
    $probeManager->renderOutput();
    // $domainRecord = DomainRecord::makeDomainRecord('www.google.com');
    // print_r($domainRecord);
}

function allSiteStatus() {
    print_r("Working");

    $filePaths = FileSystem::list(RESULTS_PATH."/*.json");

    $objects = [];

    foreach($filePaths as $filePath) {
        $objects[] = FileSystem::toObject($filePath, FileSystem::$FILE_TYPE_JSON);
    }

    // var_dump($objects);

    $domains = [];

    foreach($objects as $object) {
        $domains[] = $object->domain;
    }

    // var_dump($domains);

    $uniqueDomains = array_unique($domains);

    foreach($uniqueDomains as $key => $value) {
        if(empty($value)) {
            unset($uniqueDomains[$key]); 
        }
    }          

    // var_dump($uniqueDomains);
    $allCombinedFormats = [];

    foreach($uniqueDomains as $uniqueDomain) {

        $domainObjects = [];

        foreach($objects as $object) {
            if($object->domain == $uniqueDomain) {
                $domainObjects[] = $object;
            }
        }

        // sort the array so the last element is the most recent result
        sort($domainObjects);

        // add the most recent result to allCombinedFormats
        $allCombinedFormats[] = $domainObjects[count($domainObjects) - 1];
    }

    var_dump($allCombinedFormats);


    function getSiteStatus($format) {
        return array("domain" => $format->domain, "overallSiteStatus" => $format->overallSiteStatus);
    }

    $siteStatuses = array_map("getSiteStatus", $allCombinedFormats);

    var_dump($siteStatuses);

    $template = new Template("site-status");
    $content = $template->render(array("sites" => $siteStatuses));
    $template = new Template("page");
    return $template->render(array("content" => $content));
}


