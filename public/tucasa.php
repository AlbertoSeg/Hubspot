<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/parameters.php';

use API\Tucasa;

$tucasa = new Tucasa($tucasa_url, $tucasa_owner_id, $tucasa_platform_channel);

$tucasa->updateHubspotData($api_key_hubspot);