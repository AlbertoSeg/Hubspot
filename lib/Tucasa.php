<?php
namespace API;

/**
 * Tucasa API. Clase para estandarizar la información recogida desde Tucasa
 *
 * @author Bloo Media <jesus@bloo.media>
 * @version 1.0
 */
class Tucasa extends Core {

    public function __construct($url, $owner_id, $platform_channel) {

        $this->url = $url;
        $this->owner_id = $owner_id;
        $this->platform_channel = $platform_channel;

        // Fecha de recogida de datos
        $date = new \DateTime();
        $date->modify('-1 day');

        $query = array(
            'startDate' => $date->format('Y-m-d'),
            'endDate' => $date->format('Y-m-d'),
            'type' => 'json'
        );

        $result = $this->makeRequest($this->url.'?'.http_build_query($query));
        $solicitudes = json_decode($result, true);

        if(!empty($solicitudes)) {
            $this->fillArrays($solicitudes);
        }
    }

    private function fillArrays($solicitudes) {
        foreach ($solicitudes as $solicitud) {
            $this->deals[$solicitud['email']][] = array(
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
                        "body" => $solicitud['adRef'] . ' - ' . $solicitud['message']
                    )
                )
            );

            $this->contacts[$solicitud['email']] = array(
                'email' => $solicitud['email'],
                'properties' => array(
                    array(
                        'property' => 'firstname',
                        'value' => $solicitud['name']
                    ),
                    array(
                        'property' => 'phone',
                        'value' => $solicitud['phone']
                    ),
                    array(
                        'property' => 'listing_interested',
                        'value' => $solicitud['adRef'] // Se sobreescribe siempre
                    ),
                    array(
                        'property' => 'message',
                        'value' => $solicitud['message'] // Se sobreescribe siempre
                    ),
                    array(
                        "property" => "contact_channel",
                        "value" => $this->platform_channel
                    )
                )
            );
        }
    }

    private function makeRequest($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}