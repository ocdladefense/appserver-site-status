<?php
// this class is used to render the output of any given Probe
class ProbeResultCombinedFormat {
    
    function __construct($domainRecord, $probeResults, $startDate, $endDate) {

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
            }
        }

        // create an object for the result
        $output = (object) [
            'name' => $name,
            'domain' => $domainRecord->domain,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalResponseTime' => $totalResponseTime,
            'overallSiteStatus' => $overallSiteStatus,
            'comments' => $comments,
            'probeResults' => $probeResults
        ];

        return $output;  
    }


}