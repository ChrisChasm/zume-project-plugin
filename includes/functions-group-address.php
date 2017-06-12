<?php
/**
 * Custom functions that add the group address meta data to the buddy press
 * @source https://codex.buddypress.org/plugindev/how-to-edit-group-meta-tutorial/
 * @since 0.1
 */


/**
 * Gets the group meta data
 * @param string $meta_key
 * @return mixed
 */
function custom_field($meta_key='') {
    //get current group id and load meta_key value if passed. If not pass it blank
    return groups_get_groupmeta( bp_get_group_id(), $meta_key) ;
}

/**
 * Markup for the edit section of group details
 */
function group_edit_fields_markup() {
    global $bp, $wpdb;
    ?>

    <label for="address">Address (required)</label>
    <input id="address" type="text" name="address" value="<?php echo custom_field('address'); ?>" required/>

    <label for="city">City (required)</label>
    <input id="city" type="text" name="city" value="<?php echo custom_field('city'); ?>" required/>

    <label for="state">State (required)</label>
    <input id="state" type="text" name="state" value="<?php echo custom_field('state'); ?>" required/>

    <label for="zip">Zip (required)</label>
    <input id="zip" type="text" name="zip" value="<?php echo custom_field('zip'); ?>" required/>

    <?php
}

/**
 * Markup for the create step of the group details
 */
function group_create_fields_markup() {
    global $bp, $wpdb;
    ?>

    <label for="address">Search with your address for your tract in the map below.</label>
    <input id="address" type="text" name="address" value="" required/> <button class="button" type="button" value="submit">Search</button>

    <input type="hidden" name="tract" />

    <style>
        /* Always set the map height explicitly to define the size of the div
    * element that contains the map. */
        #map {
            height: 600px;
            width: 75%;
        }
        /* Optional: Makes the sample page fill the window. */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
    <div id="map" style="height:200px;"></div>

    <script type="text/javascript">

        jQuery(document).ready(function() {
            var map;
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 38.7767479, lng: -104.0954098},
                zoom: 3
            });

            jQuery('button').click( function () {
                jQuery

                var address = jQuery('#address').val();
                var restURL = '<?php echo get_rest_url(null, '/lookup/v1/tract/gettractmap'); ?>';
                jQuery.post( restURL, { address: address })
                    .done(function( data ) {
                        jQuery('#search-response').html('We found that your tract is ' + data.geoid );

                        jQuery('#map').css('height', '600px');

                        var map = new google.maps.Map(document.getElementById('map'), {
                            zoom: data.zoom,
                            center: {lng: data.lng, lat: data.lat},
                            mapTypeId: 'terrain'
                        });

                        // Define the LatLng coordinates for the polygon's path.
                        var coords = [ data.coordinates ];

                        var tracts = [];

                        for (i = 0; i < coords.length; i++) {
                            tracts.push(new google.maps.Polygon({
                                paths: coords[i],
                                strokeColor: '#FF0000',
                                strokeOpacity: 0.5,
                                strokeWeight: 2,
                                fillColor: '',
                                fillOpacity: 0.2
                            }));

                            tracts[i].setMap(map);
                        }

                    });
            });
        });
    </script>
    <script
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcddCscCo-Uyfa3HJQVe0JdBaMCORA9eY">
    </script>

    <?php
}


/**
 * @param $group_id
 */
function group_header_fields_save($group_id)
{
    global $bp, $wpdb;
    $plain_fields = array(
        'address', 'city', 'state', 'zip', 'country'
    );
    foreach ($plain_fields as $field) {
        $key = $field;
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            groups_update_groupmeta($group_id, $field, $value);
        }
    }
}

add_filter( 'bp_after_group_details_creation_step', 'group_create_fields_markup' );
add_filter( 'bp_after_group_details_admin', 'group_edit_fields_markup' );
add_action( 'groups_group_details_edited', 'group_header_fields_save' );
add_action( 'groups_created_group',  'group_header_fields_save' );
