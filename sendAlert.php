<?php
    $config = [
        'twilio' => [
            'account_sid' => '',
            'auth_token' => '',
            'twilio_number' => ''
        ],
        'purpleAir' => [
            'key' => '',
            'sensorId' => ''
        ],
        'numbersToSendSmsAlert' => [],
        'timezone' => 'America/Los_Angeles',
        'lastRecordedDataStoreFileName' => 'LastRecordedData.json',
        'differenceThreshold' => 20
    ];

    date_default_timezone_set($config['timezone']);
    
    use Twilio\Rest\Client;
    require 'Twilio_PHP_SDK/vendor/autoload.php';
    require 'convertPM25ToAQI.php';

    function getPurpleAirQualityDataFromSensor($sensorId) {
        global $config;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.purpleair.com/json?key='.$config['purpleAir']['key'].'&show='.$sensorId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true)['results'][0];
    }

    function updateLastRecordedData($data) {
        $lastRecordedDataDataStore = fopen('data/'.$config['lastRecordedDataStoreFileName'], 'w');
        fwrite($lastRecordedDataDataStore, json_encode($data));
        fclose($lastRecordedDataDataStore);
    }

    function getLastRecordedData() {
        $lastRecordedDataDataStore = fopen('data/'.$config['lastRecordedDataStoreFileName'], 'r') or die('Unable to open previous data file!');
        $lastRecordedDataRaw = fread($lastRecordedDataDataStore, filesize('data/'.$config['lastRecordedDataStoreFileName']));
        $lastRecordedData = json_decode($lastRecordedDataRaw, true);
        fclose($lastRecordedDataDataStore);

        return $lastRecordedData;
    }

    function sendSmsAlert($to, $message) {
        global $config;

        $client = new Client($config['twilio']['account_sid'], $config['twilio']['auth_token']);
        $client->messages->create(
            '+1'.$to,
            array(
                'from' => '+1'.$config['twilio']['twilio_number'],
                'body' => $message
            )
        );
    }

    function determineWhetherToSendSmsAlert() {
        global $config;
        global $purpleAirQualityDataFormatted;

        $lastRecordedData = getLastRecordedData();

        $differenceBetweenLastRecordedAQIAndCurrentAQI = intval($purpleAirQualityDataFormatted['AQI'])-intval($lastRecordedData['AQI']);
        $absoluteDifferenceBetweenLastRecordedAQIAndCurrentAQI = abs($differenceBetweenLastRecordedAQIAndCurrentAQI);
    
        if ($absoluteDifferenceBetweenLastRecordedAQIAndCurrentAQI >= $config['differenceThreshold']) {
            $airQualityDifferenceString = 'not changed';
    
            if ($purpleAirQualityDataFormatted['AQI'] < $lastRecordedData['AQI']) {
                // Air quality has gotten BETTER since last recorded
                $airQualityDifferenceString = 'improved by '.$absoluteDifferenceBetweenLastRecordedAQIAndCurrentAQI;
            } else if ($purpleAirQualityDataFormatted['AQI'] > $lastRecordedData['AQI']) {
                // Air quality has gotten WORSE since last recorded
                $airQualityDifferenceString = 'worsened by '.$absoluteDifferenceBetweenLastRecordedAQIAndCurrentAQI;
            }
    
            $message = 'Air quality at '.
                        $purpleAirQualityDataFormatted['Location'].
                        ' has '.
                        $airQualityDifferenceString.
                        ' since '.
                        date('M d, Y \a\\t g:i A', $lastRecordedData['ScriptLastRecordedDataSavedTimestamp']).
                        '.\n\n'.
                        'It was previously '.
                        $lastRecordedData['AQI'].
                        ' and now at '.
                        date('g:i A').
                        ' it is '.
                        $purpleAirQualityDataFormatted['AQI'].
                        ' ('.
                        getAQIDescription($purpleAirQualityDataFormatted['AQI']).
                        ').';

            foreach ($config['numbersToSendSmsAlert'] as $number) {
                sendSmsAlert($number, $message);
            }
        }
    }

    $purpleAirQualityData = getPurpleAirQualityDataFromSensor($config['purpleAir']['sensorId']);

    $purpleAirQualityDataFormatted = [
        'Location' => $purpleAirQualityData['Label'],
        'PM2_5Value' => $purpleAirQualityData['PM2_5Value'],
        'AQI' => aqiFromPM($purpleAirQualityData['PM2_5Value']),
        'SensorLastUpdatedTimestamp' => $purpleAirQualityData['LastUpdateCheck'],
        'ScriptLastRecordedDataSavedTimestamp' => time(),
        'Timezone' => $config['timezone']
    ];

    determineWhetherToSendSmsAlert();

    updateLastRecordedData($purpleAirQualityDataFormatted);
?>