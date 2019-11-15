<?php
// this class is used to Probe a single site path and make a new ProbeResult
class Probe {

    private $path;
    private $expectedStatusCode;

    function __construct($probe) {
        $this->path = $probe->path;
        $this->expectedStatusCode = $probe->expectedStatusCode;
    }

    function run($domain) {

        // make a new request object for the path
        $request = new HTTPRequest($domain.$this->path);

        // make the HTTPRequest (it seems like $response is not needed for anything)
        $response = $request->makeHTTPRequest();

        // put the result of the path probe into respective arrays using $path as the key
        $actualStatusCode = $request->getStatus();
        $responseTime = $request->getInfo()['total_time'];

        // after all the paths have been probed, pass the orginal file and the results to a new ProbeResult
        // to store the results in a new .JSON file
        return new ProbeResult($this->path, $this->expectedStatusCode, $actualStatusCode, $responseTime);
    }

}
