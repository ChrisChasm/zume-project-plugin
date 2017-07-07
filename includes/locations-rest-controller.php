<?php

/**
 * Serves the rest api response data
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Location_Lookup_Controller {

    /**
     * Returns the tract geoid from an address
     * @param $address
     * @return array
     */
    public static function get_tract_by_address ($address) {

        $google_result = Disciple_Tools_Google_Geolocation::query_google_api($address, $type = 'core'); // get google api info
        if ($google_result == 'ZERO_RESULTS') {
            return array(
                'status' => 'ZERO_RESULTS',
                'tract' => '',
            );
        }

        $census_result = Disciple_Tools_Census_Geolocation::query_census_api($google_result['lng'], $google_result['lat'], $type = 'core'); // get census api data
        if ($census_result == 'ZERO_RESULTS') {
            return array(
                'status' => 'ZERO_RESULTS',
                'tract' => '',
            );
        }

        return array(
          'status' => 'OK',
            'tract' => $census_result['geoid'],
        );
    }

    /**
     * Returns the all the array elements needed for an address to tract map search
     * @param $address
     * @return array
     */
    public static function get_tract_map ($address) {

        // Google API
        $google_result = Disciple_Tools_Google_Geolocation::query_google_api($address, $type = 'core'); // get google api info
        if ($google_result == 'ZERO_RESULTS') {
            return array(
                'status' => 'ZERO_RESULTS',
                'message' => 'Failed google geolocation lookup.',
            );
        }
        $lng = $google_result['lng'];
        $lat = $google_result['lat'];
        $formatted_address = $google_result['formatted_address'];

        // Census API
        $census_result = Disciple_Tools_Census_Geolocation::query_census_api($lng, $lat, $type = 'core'); // get census api data
        if ($census_result == 'ZERO_RESULTS') {
            return array(
                'status' => 'ZERO_RESULTS',
                'message' => 'Failed getting census data',
            );
        }
        $geoid = $census_result['geoid'];
        $zoom = $census_result['zoom'];
        $state = $census_result['state'];
        $county = $census_result['county'];

        // Boundary data
        $coordinates = Disciple_Tools_Coordinates_DB::get_db_coordinates( $geoid); // return coordinates from database

        return array(
            'status' => 'OK',
            'zoom' => $zoom,
            'lng'   =>  $lng,
            'lat'   =>  $lat,
            'formatted_address' => $formatted_address,
            'geoid' => $geoid,
            'coordinates' => $coordinates,
            'state' =>  $state,
            'county'    => $county,
        );
    }

    /**
     * Returns the all the array elements needed for an address to tract map search
     * @param $params
     * @return array
     */
    public static function get_map_by_geoid ($params) {

        $geoid = $params['geoid'];
        $lng = $params['lng'];
        $lat = $params['lat'];
        $state = substr($geoid, 0, 2);

        // Boundary data
        $coordinates = Disciple_Tools_Coordinates_DB::get_db_coordinates( $geoid); // return coordinates from database


        if(empty($lng) || empty($lat)) {
            $coor = $coordinates[0];
            $lng = $coor['lng'];
            $lat = $coor['lat'];
        }

        $zoom = Location_Lookup_Placemark_Info::get_placemark_zoom($geoid, $state);

        return array(
            'status' => 'OK',
            'zoom' => $zoom,
            'lng'   =>  (float)$lng,
            'lat'   =>  (float)$lat,
            'geoid' => $geoid,
            'coordinates' => $coordinates,
            'state' =>  $state,
        );
    }


}