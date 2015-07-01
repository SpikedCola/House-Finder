<?php

require_once(__DIR__.'/inc.php');

// retrieve additional details for this listing from the actual details page

$url = POST('url');
$providerName = POST('provider', 'str', ['mls', 'comfree', 'remax']);
$providerObjName = "listingProvider_{$providerName}"; 

$provider = new $providerObjName($user);

$listing = new listing($listingId);

$info = $provider->getAdditionalInformation($url);

$response = [];
$response['status'] = 'ok';
$response['info'] = $info;

echo json_encode($response);