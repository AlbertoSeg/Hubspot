<?php
namespace API;

require __DIR__ . '/../config/parameters.php';

/**
 * Fotocasa API.
 *
 * @author Bloo Media <jesus@bloo.media>
 * @version 1.0
 */
class Fotocasa {

    private $fotocasa_url_contacts = null, $owner_id = null, $auth_user = null, $auth_password = null, $platform_channel = null;

    private $contacts = array(), $deals = array();

    public function __construct($fotocasa_url_contacts, $owner_id, $auth_user, $auth_password, $platform_channel) {

        // Fecha de recogida de datos
        $date = new \DateTime();
        $date->modify('-1 day');

        $query = array(
            'startDate' => $date->format('Y-m-d'),
            'endDate' => $date->format('Y-m-d'),
            'type' => 'json'
        );

        $result = $this->makeRequest($this->fotocasa_url_contacts.'?'.http_build_query($query));
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
                            "value" => $this->platform_channel
                        )
                    ),
                    'notes' => array(
                        "engagement" => array(
                            "type" => "NOTE",
                            "ownerId" => $this->owner_id,
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
        curl_setopt($ch, CURLOPT_USERPWD, $this->auth_user.":".$this->auth_password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}