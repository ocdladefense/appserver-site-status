<?php

define("SITE_HEALTHY", 1);
define("SITE_UNHEALTHY", 2);

define("SITES_PATH", BASE_PATH."/site-json");
define("RESULTS_PATH", BASE_PATH."/content/sitestatus/results");



class SiteStatusModule extends Module {
    

    public function __construct() {
        parent::__construct();

        // $this->routes = siteStatusModRoutes();
        // $this->dependencies = $this->deps;
        // $this->files = siteStatusModRoutes()["init-probemanager"]["files"];
    }




    public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}
		return $randomString;
	}


	public function siteStatus($id) {

		$file = BASE_PATH ."/config/incidents/{$id}.csv";

		// print $file;exit;

		if(!file_exists($file)) {
			return "Incident data not found.";
		}

		if (($handle = fopen($file, "r")) !== false) {
			$headers = fgetcsv($handle, 1000, ",");
			// print_r($data);
			if($headers !== false) {

				// $headers = $data;
				$data = fgetcsv($handle, 1000, ",");
			}

			$vars = array_combine($headers,$data);

			fclose($handle);
		}

		// exit;
		$out = [];
		foreach($vars as $label => $value) {
			$out []= "<span class='label'>{$label}</span>: {$value}";
		}
		return implode("<br />",$out);
	}



	public function siteStatuses() {

		$file = BASE_PATH ."/config/incidents.csv";

		$row = 1;
		$rows = [];

		if (($handle = fopen($file, "r")) !== false) {
			while (($data = fgetcsv($handle, 1000, ",")) !== false) {
				// $num = count($data);
				// echo "<p> $num fields in line $row: <br /></p>\n";

				$row++;
				$col = 0;
				$cols = [];
				foreach($data as $cell) {
					if($col++ == 0) {
						$cols []= "<a href='/status/{$cell}'>{$cell}</a>";
						continue;
					}
					$cols []= $cell;
				}
				$rows []= "<span class='table-cell'>".implode("</span><span class='table-cell'>",$cols)."</span>";
			}

			fclose($handle);
		}
	
		return "<div class='table-row'>" . implode("</div><div class='table-row'>", $rows) . "</div>";
	}

    /*
    public function siteStatusModRoutes() {
        $siteStatusModRoutes = array(
            "init-probemanager" => array(
                "callback" => "runProbes",
                "files" => array(
                    "FileSystem.php",
                    "DomainRecord.php",
                    "ProbeManager.php",
                    "ProbeManagerException.php",
                    "Probe.php",
                    "DomainRecordException.php",
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
            "site-status-check-site" => array(
                "content-type" => "json",
                "method" => "post",
                "callback" => "getSiteStatusFromUrl",
                "files" => array(
                    "FileSystem.php",
                    "DomainRecord.php",
                    "ProbeManager.php",
                    "ProbeManagerException.php",
                    "Probe.php",
                    "DomainRecordException.php",
                    "ProbeRenderer.php",
                    "ProbeResult.php",
                    "ProbeResultCombinedFormat.php"
                )
                
            ),
            "site-status-load-sites" => array(
                "content-type" => "json",
                "method" => "post",
                "callback" => "getSiteStatusesFromDatabase",
                "files" => array(
                    "FileSystem.php",
                    "DomainRecord.php",
                    "ProbeManager.php",
                    "ProbeManagerException.php",
                    "Probe.php",
                    "DomainRecordException.php",
                    "ProbeRenderer.php",
                    "ProbeResult.php",
                    "ProbeResultCombinedFormat.php"
                )
            )
        );
        return $siteStatusModRoutes;
    }
    */

    public function runProbes() {
        
        $probeManager = ProbeManager::newFromFileSystem(SITES_PATH);
        $probeManager->doValidation();
        $probeManager->doProbes(true);
        $probeManager->renderOutput();
        // $domainRecord = DomainRecord::makeDomainRecord('www.google.com');
        // print_r($domainRecord);
    }

    public function getSiteStatusFromUrl($json, $status = "200") {
        //return print_r($json);
        $object = JSON_Decode($json);
        $probeManager = ProbeManager::newFromUrl($object->url, $object->name);
        $probeManager->doValidation();
        $output = $probeManager->doProbes(false);
        return $output;
        // $domainRecords = $probeManager->getDomainRecords();
    }

    public function getSiteStatusesFromDatabase($json) {
        $objects = JSON_Decode($json);

        $probeManager = ProbeManager::newFromDatabase($objects);
        $probeManager->doValidation();
        $output = $probeManager->doProbes(false);
        return $output;
    }


    public function allSiteStatus() {

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
        return $template->render(array("sites" => $siteStatuses));

        // $template = new Template("site-status");
        // $content = $template->render(array("sites" => $siteStatuses));
        // $template = new Template("page");
        // return $template->render(array("content" => $content));
    }


}