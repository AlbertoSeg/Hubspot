<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/parameters.php';

use API\Fotocasa;

$fotocasa = new Fotocasa($fotocasa_url_contacts, $fotocasa_owner_id, $fotocasa_auth_user, $fotocasa_auth_password, $fotocasa_platform_channel);

$fotocasa->updateHubspotData($api_key_hubspot);