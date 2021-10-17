<?php

namespace Etn\Core\Settings;

defined( 'ABSPATH' ) || exit;

class Settings {

    use \Etn\Traits\Singleton;

    private $key_settings_option;

    public function init() {
        $this->key_settings_option = 'etn_event_options';
        
        add_action( 'admin_menu', [$this, 'add_setting_menu'] );

        add_action( 'admin_init', [$this, 'register_actions'], 999 );
    }

    public function get_settings_option( $key = null, $default = null ) {

        if ( $key != null ) {
            $this->key_settings_option = $key;
        }

        return get_option( $this->key_settings_option );
    }

    /**
     * Add Settings Sub-menu
     *
     * @since 1.0.1
     * 
     * @return void
     */
    public function add_setting_menu() {

        // Add settings menu if user has specific access
        if( current_user_can( 'manage_etn_settings' ) && current_user_can('manage_options')){
            
            add_submenu_page(
                'etn-events-manager',
                esc_html__( 'Settings', 'eventin' ),
                esc_html__( 'Settings', 'eventin' ),
                'manage_options',
                'etn-event-settings',
                [$this, 'etn_settings_page'],
                6
            );
        }
    }

    /**
     * Settings Markup Page
     *
     * @return void
     */
    public function etn_settings_page() {
        $settings_file = \Wpeventin::plugin_dir() . "core/settings/views/etn-settings.php"; 
        if( file_exists( $settings_file ) ){
            include $settings_file;
        }
    }

    /**
     * Save Settings Form Data
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function register_actions() {

        if ( isset( $_POST['etn_settings_page_action'] ) ) {
            if ( !check_admin_referer( 'etn-settings-page', 'etn-settings-page' ) ) {
                return;
            }

            $post_arr = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
            
            // empty field discard logic
            if( is_array($post_arr) && !empty($post_arr) ){
                if( array_key_exists('attendee_extra_fields', $post_arr) ){
                    $attendee_extra_fields = $post_arr['attendee_extra_fields'];
               
                    $new_attendee_extra_fields = []; // for storing o,1,2... based index

                    $special_types = [
                        'date',
                        'radio',
                    ];

                    foreach( $attendee_extra_fields as $index => $attendee_extra_field ){
                         
                        // if label/type empty then discard this index
                        if( !isset($attendee_extra_field['label']) || empty($attendee_extra_field['label']) || 
                            !isset($attendee_extra_field['type']) || empty($attendee_extra_field['type']) ){
                            unset($attendee_extra_fields[$index]);
                        } else {
                            $selected_type = $attendee_extra_field['type'];

                            // no need placeholder text for date, radio, checkbox etc.
                            if( in_array( $selected_type, $special_types ) ){
                                $attendee_extra_field['place_holder'] = ''; // change placeholder value to empty
                            }
                            
                            // check whether it is radio, if radio then unset all empty radio label
                            if( $selected_type == 'radio1' ){
                                if( isset( $attendee_extra_field['radio'] ) && count( $attendee_extra_field['radio'] ) >= 2 ){
                                   
                                    $new_radio_arr = [];
                                    foreach( $attendee_extra_field['radio'] as $radio_index => $radio_val ){
                                        if( empty( $radio_val ) ){
                                            unset( $attendee_extra_field['radio'][$radio_index] );
                                        } else {
                                            // for maintaing 0,1,2... based index
                                            array_push( $new_radio_arr, $radio_val ); 
                                        }
                                    }
                                    $attendee_extra_field['radio'] = $new_radio_arr;
                                    
                                    // after discarding empty radio label check there exists minimum 2 radio label
                                    if( count( $attendee_extra_field['radio'] ) < 2 ){
                                        unset($attendee_extra_field); // minimium 2 radio label required, else unset
                                    }
                                    
                                } else {
                                    unset($attendee_extra_field); // minimium 2 radio label required, else unset
                                }
                            } else {
                                // radio index can only stay if selected type is radio, otherwise discard
                                // unset( $attendee_extra_field['radio'] ); // rel 2.5
                            }

                            if( !empty( $attendee_extra_field ) ){
                                array_push( $new_attendee_extra_fields, $attendee_extra_field );
                            }
                        }
                    }

                    $post_arr['attendee_extra_fields'] = $new_attendee_extra_fields;
                    
                }
            }

            $data            = \Etn\Base\Action::instance()->store( -1, $post_arr );
            $check_transient = get_option( 'zoom_user_list' );

            if ( isset( $post_arr['zoom_api_key'] ) && isset( $post_arr['zoom_secret_key'] ) && $check_transient == false ) {
                // get host list
                \Etn\Core\Zoom_Meeting\Api_Handlers::instance()->zoom_meeting_user_list();
            }
            return $data;
        }
        return false;
    }

}