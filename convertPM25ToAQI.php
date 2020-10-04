<?php
    /*
        Converted to PHP from Javascript
        Original: https://docs.google.com/document/d/15ijz94dXJ-YAZLi9iZ_RaBwrZ4KtYeCy08goGBwnbCU/edit

        # Example usage
        $AQI = aqiFromPM($PM25value);
        $AQIDescription = getAQIDescription($AQI); // A short description of the provided AQI
        $AQIMessage = getAQIMessage($AQI); // What the provided AQI means (a longer description)

        # Values
        Good                                0 - 50          0.0 - 15.0          0.0 – 12.0
        Moderate                            51 - 100        >15.0 - 40          12.1 – 35.4
        Unhealthy for Sensitive Groups      101 – 150       >40 – 65            35.5 – 55.4
        Unhealthy                           151 – 200       >65 – 150           55.5 – 150.4
        Very Unhealthy                      201 – 300       >150 – 250          150.5 – 250.4
        Hazardous                           301 – 400       >250 – 350          250.5 – 350.4
        Hazardous                           401 – 500       >350 – 500          350.5 – 500
    */

    function aqiFromPM($PM) {
        if (is_nan($PM)) { return '-'; }
        if ($PM == null) { return '-'; }
        if ($PM < 0) { return $PM; }
        if ($PM > 1000) { return '-'; }

        if ($PM > 350.5) {
            return calcAQI($PM, 500, 401, 500, 350.5);
        } else if ($PM > 250.5) {
            return calcAQI($PM, 400, 301, 350.4, 250.5);
        } else if ($PM > 150.5) {
            return calcAQI($PM, 300, 201, 250.4, 150.5);
        } else if ($PM > 55.5) {
            return calcAQI($PM, 200, 151, 150.4, 55.5);
        } else if ($PM > 35.5) {
            return calcAQI($PM, 150, 101, 55.4, 35.5);
        } else if ($PM > 12.1) {
            return calcAQI(pm, 100, 51, 35.4, 12.1);
        } else if ($PM >= 0) {
            return calcAQI($PM, 50, 0, 12, 0);
        } else {
            return null;
        }
    }

    function bplFromPM($PM) {
        if (is_nan($PM)) { return 0; }
        if ($PM == null) { return 0; }
        if ($PM < 0) { return 0; }

        if ($PM > 350.5) {
            return 401;
        } else if ($PM > 250.5) {
            return 301;
        } else if ($PM > 150.5) {
            return 201;
        } else if ($PM > 55.5) {
            return 151;
        } else if ($PM > 35.5) {
            return 101;
        } else if ($PM > 12.1) {
            return 51;
        } else if ($PM >= 0) {
            return 0;
        } else {
            return 0;
        }
    }

    function bphFromPM($PM) {
        if (is_nan($PM)) { return 0; }
        if ($PM == null) { return 0; }
        if ($PM < 0) { return 0; }

        if ($PM > 350.5) {
            return 500;
        } else if ($PM > 250.5) {
            return 500;
        } else if ($PM > 150.5) {
            return 300;
        } else if ($PM > 55.5) {
            return 200;
        } else if ($PM > 35.5) {
            return 150;
        } else if ($PM > 12.1) {
            return 100;
        } else if ($PM >= 0) {
            return 50;
        } else {
            return 0;
        }
    }

    function calcAQI($Cp, $Ih, $Il, $BPh, $BPl) {
        $a = ($Ih - $Il);
        $b = ($BPh - $BPl);
        $c = ($Cp - $BPl);

        return round(($a / $b) * $c + $Il);
    }

    function getAQIDescription($AQI) {
        if ($AQI >= 401) {
            return 'Hazardous';
        } else if ($AQI >= 301) {
            return 'Hazardous';
        } else if ($AQI >= 201) {
            return 'Very Unhealthy';
        } else if ($AQI >= 151) {
            return 'Unhealthy';
        } else if ($AQI >= 101) {
            return 'Unhealthy for Sensitive Groups';
        } else if ($AQI >= 51) {
            return 'Moderate';
        } else if ($AQI >= 0) {
            return 'Good';
        } else {
            return null;
        }
    }

    function getAQIMessage($AQI) {
        if ($AQI >= 401) {
            return '>401: Health alert: everyone may experience more serious health effects';
        } else if ($AQI >= 301) {
            return '301-400: Health alert: everyone may experience more serious health effects';
        } else if ($AQI >= 201) {
            return '201-300: Health warnings of emergency conditions. The entire population is more likely to be affected. ';
        } else if ($AQI >= 151) {
            return '151-200: Everyone may begin to experience health effects; members of sensitive groups may experience more serious health effects.';
        } else if ($AQI >= 101) {
            return '101-150: Members of sensitive groups may experience health effects. The general public is not likely to be affected.';
        } else if ($AQI >= 51) {
            return '51-100: Air quality is acceptable; however, for some pollutants there may be a moderate health concern for a very small number of people who are unusually sensitive to air pollution.';
        } else if ($AQI >= 0) {
            return '0-50: Air quality is considered satisfactory, and air pollution poses little or no risk';
        } else {
            return null;
        }
    }
?>