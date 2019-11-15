<?php

class SiteChecker extends Module {

    $this->name = "SiteChecker";
    $this->routes = $this->siteCheckerModRoutes();
    $this->dependencies = array();
    $this->files = $this->siteCheckerModRoutes()["files"];

}

function siteCheckerModRoutes() {
    $siteCheckerModRoutes = array(
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
    return $siteCheckerModRoutes;
}

function runProbes() {
    $probeManager = ProbeManager::newFromFileSystem(BASE_PATH."/site-json");
    $probeManager->doValidation();
    $probeManager->doProbes();
    $probeManager->renderOutput();
    // $domainRecord = DomainRecord::makeDomainRecord('www.google.com');
    // print_r($domainRecord);
}