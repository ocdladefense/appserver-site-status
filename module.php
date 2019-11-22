<?php

define("SITE_HEALTHY", 1);
define("SITE_UNHEALTHY", 2);

define("SITES_PATH", BASE_PATH."/site-json");
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
        ),
        "site-status" => array(
            "content-type" => "application/json",
            "callback" => "getSiteStatus"
        )
    );
    return $siteStatusModRoutes;
}

function runProbes() {
    
    $probeManager = ProbeManager::newFromFileSystem(SITES_PATH);
    $probeManager->doValidation();
    $probeManager->doProbes();
    $probeManager->renderOutput();
    // $domainRecord = DomainRecord::makeDomainRecord('www.google.com');
    // print_r($domainRecord);
}

function getSiteStatue($url, $status = "200") {

}


function allSiteStatus() {

    // make a list of paths to all probe result .JSON files
    $filePaths = FileSystem::list(RESULTS_PATH."/*.json");

    // convert to objects
    $objects = [];
    foreach($filePaths as $filePath) {
        $objects[] = FileSystem::toObject($filePath, FileSystem::$FILE_TYPE_JSON);
    }

    // make a list of all the unique domains from the objects
    $domains = [];
    foreach($objects as $object) {
        $domains[] = $object->domain;
    }

    $uniqueDomains = array_unique($domains);

    foreach($uniqueDomains as $key => $value) {
        if(empty($value)) {
            unset($uniqueDomains[$key]); 
        }
    }          


    // make a list of the most recent result from each domain
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


    // format data to be sent to the template
    function getSiteStatus($format) {
        return array("name" => $format->name, "domain" => $format->domain, "overallSiteStatus" => $format->overallSiteStatus, 
                    "probeResults" => $format->probeResults);
    }

    $siteStatuses = array_map("getSiteStatus", $allCombinedFormats);


    // load and render
    $template = new Template("site-status");
    $content = $template->render(array("sites" => $siteStatuses));
    $template = new Template("page");
    return $template->render(array("content" => $content));
}


