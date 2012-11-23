<?php
        if (!empty($_POST)) {
                $response = array(
                    'status' => 'error',
                    'results' => array()
                );

                $xmlFile = __DIR__ . '/request.xml';
                $requestXml = urlencode(file_get_contents($xmlFile));
                
                $url = 'http://www.realtor.ca/handlers/MapSearchHandler.ashx?xml=' . $requestXml;

                $jsonResponse = file_get_contents($url);
                
                if (!empty($jsonResponse)) {
                        $json = json_decode($jsonResponse);

                        if (isset($json->MapSearchResults) && isset($json->NumberSearchResults)) {
                                // data is probably valid :)
                                $response['status'] = 'ok';
                                $response['results'][] = $json->MapSearchResults[0];
                        }
                }
                
                echo json_encode($response);
                exit;
        }
        
        header("HTTP/1.1 403 Forbidden");
?>
