<?php

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