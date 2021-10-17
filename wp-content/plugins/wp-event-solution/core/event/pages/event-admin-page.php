<?php

namespace Etn\Core\Event\Pages;

defined( 'ABSPATH' ) || exit;

class Event_Admin_Page {

    public function add_admin_pages() {

        if ( current_user_can( 'manage_etn_event' ) ||
            current_user_can( 'manage_etn_speaker' ) ||
            current_user_can( 'manage_etn_schedule' ) ||
            current_user_can( 'manage_etn_attendee' ) ||
            current_user_can( 'manage_etn_zoom' ) ||
            current_user_can( 'manage_etn_settings' ) 
        ) {
            add_menu_page(
                'Eventin Event Manager',
                'Eventin',
                'read',
                'etn-events-manager',
                '',
                'dashicons-calendar',
                10
            );
        }
    }

}
