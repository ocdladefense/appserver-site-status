<?php
// this class is used to render the output of any given Probe
class ProbeRenderer {
    
    function __construct() {
    }

    public static function ResultCombinedFormat($domainRecord, $probeResults, $probeDate) {

        $name = "";
        $comments = "";
        $totalResponseTime = 0;
        $overallSiteStatus = SITE_HEALTHY;

        if(isset($domainRecord->name)) {
            $name = $domainRecord->name;
        }

        if(isset($domainRecord->comments)) {
            $comments = $domainRecord->comments;
        }

        // get the totalResponseTime and overallSiteStatus from the $results array of ProbeResult objects
        foreach($probeResults as $result) {
            $totalResponseTime += $result->responseTime;

            if($result->expectedStatusCode != $result->actualStatusCode) {
                $overallSiteStatus = SITE_UNHEALTHY;

                // // this is an extra feature that should be done elsewhere
                // $output = ProbeRenderer::ResultIndividualFormat($domainRecord, $result, $probeDate);
            }
        }

        // create an object for the result
        $output = (object) [
            'name' => $name,
            'domain' => $domainRecord->domain,
            'date' => $probeDate,
            'totalResponseTime' => $totalResponseTime,
            'overallSiteStatus' => $overallSiteStatus,
            'comments' => $comments,
            'probeResults' => $probeResults
        ];

        return $output;  
    }

    public static function ResultIndividualFormat($domainRecord, $probeResult, $probeDate) {
        // this could maybe be used to package an individual probe result in case expectedStatusCode and actualStatusCode are different
    }
}