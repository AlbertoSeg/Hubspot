<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/parameters.php';

use API\Fotocasa;
use API\CustomFileStorage;
use SevenShores\Hubspot\Factory;
use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use bandwidthThrottle\tokenBucket\BlockingConsumer;

// Control de API rate limit
$storage  = new CustomFileStorage(__DIR__ . "/../tmp/api.bucket");
$rate     = new Rate(4, Rate::SECOND);
$bucket   = new TokenBucket(4, $rate, $storage);
$consumer = new BlockingConsumer($bucket);
if(!$storage->isBootstrapped()) {
    $bucket->bootstrap(0);
} else {
    $storage->remove();
    $storage->open();
    $bucket->bootstrap(0);
}

$fotocasa = new Fotocasa($fotocasa_url_contacts, $owner_id, $auth_user, $auth_password, $platform_channel);
$contacts = $fotocasa->getContacts();
$deals = $fotocasa->getDeals();


$hubspot = Factory::create($api_key_hubspot);

$all = $hubspot->owners()->all();

echo "<pre>";
print_r($all);
echo "</pre>";
exit();


foreach ($contacts as $contact) {
    $vid = 0;

    try {
        // Comprobar si existe el usuario

        // Gastar 1 token de API. Control de rate limit
        $consumer->consume(1);
        $response_hubspot_contact = $hubspot->contacts()->getByEmail($contact['email']);
    } catch (Exception $e) {
        if($e->getCode() == 404) {
            $response_hubspot_contact = null;

        } else {
            // Ha habido un error. Falta registrar errores
            $log  = "Error code 1: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
                "Message: ".$e->getMessage().PHP_EOL.
                "-------------------------".PHP_EOL;
            //Save string to log, use FILE_APPEND to append.
            file_put_contents('../tmp/log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
            continue;
        }
    }

    if(!empty($response_hubspot_contact)) {
        $contact_hubspot = $response_hubspot_contact->getData();

        $properties_values = array();
        foreach ( $contact['properties'] as $key => $prop) {
            // Si el contacto existe, comprobar si tiene el teléfono y el nombre rellenados
            if($prop['property'] == 'phone') {
                $phone = $contact_hubspot->properties->phone->value;
                if(!empty($phone)) {
                    // Pasar a la siguiente propiedad para no incluirla
                    continue;
                }
            }

            if($prop['property'] == 'firstname') {
                $firstname = $contact_hubspot->properties->firstname->value;
                if (!empty($firstname)) {
                    // Pasar a la siguiente propiedad para no incluirla
                    continue;
                }
            }

            $properties_values[] = $prop;
        }

        $contact['properties'] = $properties_values;
    }

    try {

        // Gastar 1 token de API. Control de rate limit
        $consumer->consume(1);
        $response = $hubspot->contacts()->createOrUpdate($contact['email'], $contact['properties']);
        if($response->getStatusCode() != 200) {
            // Ha habido un error. Falta registrar errores
            $log  = "Error code 2: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
                "Message: Error en la llamada createOrUpdate.".PHP_EOL.
                "Data: email = ".$contact['email'].", properties = ".serialize($contact['properties']).PHP_EOL.
                "-------------------------".PHP_EOL;
            //Save string to log, use FILE_APPEND to append.
            file_put_contents('../tmp/log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
            continue;
        }
        $data_response = $response->getData();

        // $data_response->vid (id del usuario) y $data_response->isNew (creado o actualizado)
        $vid = $data_response->vid;
    } catch(Exception $e) {
        // Ha habido un error. Falta registrar errores
        $log  = "Error code 3: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
            "Message: ".$e->getMessage().PHP_EOL.
            "-------------------------".PHP_EOL;
        //Save string to log, use FILE_APPEND to append.
        file_put_contents('../tmp/log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
        continue;
    }

    // Añadir nota al contacto
    if(isset($deals[$contact['email']])) {

        foreach ($deals[$contact['email']] as $d) {
            try {
                $d['notes']['associations'] = array(
                    "contactIds" => array($vid)
                );

                // Gastar 1 token de API. Control de rate limit
                $consumer->consume(1);
                $result_engagement = $hubspot->engagements()->create($d['notes']['engagement'], $d['notes']['associations'], $d['notes']['metadata']);

                if($result_engagement->getStatusCode() != 200) {
                    // Ha habido un error. Falta registrar errores
                    $log  = "Error code 4: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
                        "Message: Error en la llamada createOrUpdate.".PHP_EOL.
                        "Data: notes = ".serialize($d).PHP_EOL.
                        "-------------------------".PHP_EOL;
                    //Save string to log, use FILE_APPEND to append.
                    file_put_contents('../tmp/log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
                    continue;
                }
            } catch (Exception $e) {
                // Ha habido un error. Falta registrar errores
                $log  = "Error code 5: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
                    "Message: ".$e->getMessage().PHP_EOL.
                    "-------------------------".PHP_EOL;
                //Save string to log, use FILE_APPEND to append.
                file_put_contents('../tmp/log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
            }
        }
    }
}



