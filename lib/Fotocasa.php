<?php
namespace API;

/**
 * Fotocasa API.
 *
 * @author Bloo Media <jesus@bloo.media>
 * @version 1.0
 */
class Fotocasa {

    const URL_CONTACTS = 'http://localhost/hubspot/priv/example.json';
    const PLATFORM_CHANNEL = 'platform-fotocasa-request'; // Campo que se utilizará como platform_channel
    const OWNER_ID = '16828781'; // El usuario es de prueba
    const AUTH_USER = 'prueba'; // Usuario de autenticación de la API de fotocasa
    const AUTH_PASSWORD = 'prueba'; // Contraseña de autenticación de la API de fotocasa

    private $contacts = array(), $deals = array(), $transactionTypeDictionary = array();

    public function __construct()
    {
        // Fecha de recogida de datos
        $date = new \DateTime();
        $date->modify('-1 day');

        $query = array(
            'startDate' => $date->format('Y-m-d'),
            'endDate' => $date->format('Y-m-d'),
            'type' => 'json'
        );

        $result = $this->makeRequest(Fotocasa::URL_CONTACTS.'?'.http_build_query($query));
        $solicitudes = json_decode($result, true);

        if(!empty($solicitudes)) {
            foreach ($solicitudes as $solicitud) {
                $this->deals[$solicitud['Email']][] = array(
                    'properties' => array(
                        array(
                            "name" => "pipeline",
                            "value" => "default"
                        ),
                        array(
                            "name" => "dealstage",
                            "value" => "appointmentscheduled"
                        ),
                        array(
                            "name" => "createdate",
                            "value" => strtotime($solicitud['Date'])
                        ),
                        array(
                            "name" => "dealname",
                            "value" => $solicitud['Name']
                        ),
                        array(
                            "name" => "contact_channel",
                            "value" => Fotocasa::PLATFORM_CHANNEL
                        )
                    ),
                    'notes' => array(
                        "engagement" => array(
                            "type" => "NOTE",
                            "ownerId" => Fotocasa::OWNER_ID,
                            "active" => true
                        ),
                        "associations" => array(
                            "dealIds" => array(),
                            "contactIds" => array()
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
                        )
                    )
                );
            }
        }
    }


    public function getContacts() {
        return $this->contacts;
    }

    public function getDeals() {
        return $this->deals;
    }

    private function makeRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, Fotocasa::AUTH_USER.":".Fotocasa::AUTH_PASSWORD);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}