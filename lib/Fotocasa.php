<?php
namespace API;

/**
 * Fotocasa API. Clase para estandarizar la información recogida desde Fotocasa
 *
 * @author Bloo Media <jesus@bloo.media>
 * @version 1.0
 */
class Fotocasa extends Core {

    private $auth_user = null, $auth_password = null;

    public function __construct($url, $owner_id, $auth_user, $auth_password, $platform_channel) {

        $this->url = $url;
        $this->owner_id = $owner_id;
        $this->auth_user = $auth_user;
        $this->auth_password = $auth_password;
        $this->platform_channel = $platform_channel;

        // Fecha de recogida de datos
        $date = new \DateTime();
        $date->modify('-1 day');

        $query = array(
            'startDate' => $date->format('Y-m-d'),
            'endDate' => $date->format('Y-m-d'),
            'type' => 'json'
        );

        $solicitudes = $this->makeRequest($this->url.'?'.http_build_query($query));

        if(!empty($solicitudes)) {
            $this->fillArrays($solicitudes);
        }
    }

    private function fillArrays($solicitudes) {
        foreach ($solicitudes as $solicitud) {
            $this->deals[$solicitud['Email']][] = array(
                'notes' => array(
                    "engagement" => array(
                        "type" => "NOTE",
                        "ownerId" => $this->owner_id,
                        "active" => true
                    ),
                    "associations" => array(
                        "dealIds" => array(),
                        "contactIds" => array() // Se rellena más adelante, cuando se hace la petición
                    ),
                    "metadata" => array(
                        "body" => $solicitud['Reference'] . ' - ' . $solicitud['Comments']
                    )
                )
            );

            $this->contacts[$solicitud['Email']] = array(
                'email' => $solicitud['Email'],
                'properties' => array(
                    array(
                        'property' => 'firstname',
                        'value' => $solicitud['Name']
                    ),
                    array(
                        'property' => 'phone',
                        'value' => $solicitud['Phone']
                    ),
                    array(
                        'property' => 'listing_interested',
                        'value' => $solicitud['Reference'] // Se sobreescribe siempre
                    ),
                    array(
                        'property' => 'message',
                        'value' => $solicitud['Comments'] // Se sobreescribe siempre
                    ),
                    array(
                        "property" => "contact_channel",
                        "value" => $this->platform_channel
                    )
                )
            );
        }
    }

    private function makeRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->auth_user.":".$this->auth_password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        $resultJson = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($resultJson, true);

        return $result;
    }
}