<?php

namespace Etn\Core\Migration;

use Etn\Utils\Helper;

defined('ABSPATH') || exit;

class Migration {
    use \Etn\Traits\Singleton;

    /**
     * Main Function 
     *
     * @return void
     */
    public function init(){
        $this->migrate_event_price();
        $this->migrate_attendee_unique_id();
        add_action( 'init', [$this, 'woocommerce_after_loaded'], 10, 0 ); 
    }

    /**
     * after woocommerce loaded then able to use woo functionality
     *
     * @return void
     */
    public function woocommerce_after_loaded(){
        if ( class_exists( 'WooCommerce' ) ) {
            $this->migrate_event_order_status();
        }
    }

    /**
     * migrate event order status by adding meta flag to check what was the last increment/decrement status
     *
     * @return void
     */
    public function migrate_event_order_status(){
        $migration_done = !empty( get_option( "etn_event_order_status_migration_done" ) ) ? true : false;
  
        if( !$migration_done && class_exists( 'WooCommerce' ) ){
            $all_order_posts = \Etn\Utils\Helper::get_order_posts();
       
            if( is_array($all_order_posts) && !empty($all_order_posts) ){
                foreach( $all_order_posts as $order_post ){
                    $order = wc_get_order( intval( $order_post['ID'] ) );
           
                    foreach ( $order->get_items() as $item_id => $item ){
                        $product_name     = $item->get_name();
                        $event_id         = !is_null( $item->get_meta( 'event_id', true ) ) ? $item->get_meta( 'event_id', true ) : "";
                        $product_quantity = (int) $item->get_quantity();
                        
                        if( !empty( $event_id ) ){
                            $event_object = get_post( $event_id );
                        } else {
                            $event_object = get_page_by_title( $product_name, OBJECT, 'etn' );
                        }
            
                        if ( !empty( $event_object ) ){
                            //this item is an event, proceed...
                            $decrease_states = [
                                'processing',
                                'on-hold',
                                'completed',
                                'on' // same as 'on-hold' 
                            ];
                    
                            $increase_state = [
                                'pending',
                                'cancelled',
                            ];
                    
                            $no_action_state = [
                                'refunded',
                                'failed',
                            ];
                        
                            if ( !empty( $event_object->post_type ) && ('etn' == $event_object->post_type) ) {
                                $event_id = $event_object->ID;

                                $order_status = $order->get_status();
                            
                                // decrease event stock
                                if( in_array($order_status, $decrease_states) ){
                                    $decreased_stock = wc_get_order_item_meta( $item_id, '_etn_decreased_stock', true ); 
                                
                                    if( $decreased_stock != $product_quantity ){
                                        wc_delete_order_item_meta( $item_id, '_etn_increased_stock' ); 
                                        wc_add_order_item_meta( $item_id, '_etn_decreased_stock', $product_quantity, true);
                                    }
                                }

                                // increase event stock
                                if( in_array($order_status, $increase_state) ){
                                    $increased_stock = wc_get_order_item_meta( $item_id, '_etn_increased_stock', true );

                                    if( $increased_stock != $product_quantity ){
                                        wc_delete_order_item_meta( $item_id, '_etn_decreased_stock' ); 
                                        wc_add_order_item_meta( $item_id, '_etn_increased_stock', $product_quantity, true);
                                    }
                                }
                           
                                // complex case: refunded/failed handling
                                if( in_array($order_status, $no_action_state) ){
                                    
                                    $order_notes = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );
                                    
                                    if( is_array($order_notes) && !empty($order_notes) ){
                                        krsort($order_notes);
                                      
                                        foreach( $order_notes as $order_note ){
                                            $matched = preg_match('/Order status changed from /i', $order_note->content);
                                          
                                            if( $matched ){
                                                $note_content = strtolower( rtrim( $order_note->content, '.' ) );
                                                $note_from_to = strstr($note_content, 'from');

                                                $old_order_status   = explode(' ', $note_from_to)[1];  
                                                $note_to            = strstr($note_from_to, 'to');
                                                $new_order_status   = explode(' ', $note_to)[1];

                                                // stock meta decrease/increase logic
                                                // decrease event stock
                                                $decreased_stock = wc_get_order_item_meta( $item_id, '_etn_decreased_stock', true ); 

                                                if( $decreased_stock != $product_quantity ){
                                                    if( in_array($new_order_status, $decrease_states) && !in_array($old_order_status, $decrease_states) ){
                                                        wc_delete_order_item_meta( $item_id, '_etn_increased_stock' ); 
                                                        wc_add_order_item_meta( $item_id, '_etn_decreased_stock', $product_quantity, true);
                                                    }
                                                }

                                                // increase event stock
                                                $increased_stock = wc_get_order_item_meta( $item_id, '_etn_increased_stock', true ); 
  
                                                if( $increased_stock != $product_quantity ){
                                                    if( in_array($new_order_status, $increase_state) && !in_array($old_order_status, $increase_state ) ){
                                                        wc_delete_order_item_meta( $item_id, '_etn_decreased_stock' ); 
                                                        wc_add_order_item_meta( $item_id, '_etn_increased_stock', $product_quantity, true);
                                                    }
                                                }
                                            }  
                                        }
                                    }
                                }  
                            }  
                        }
                    }
                }
            }

            update_option( "etn_event_order_status_migration_done", true );
        }
    }


    /**
     * migrate event price into Woocommerce product price
     *
     * @return void
     */
    public function migrate_event_price(){
        $migration_done = !empty( get_option( "etn_event_price_migration_done" ) ) ? true : false;
        
        if( !$migration_done ){
            $all_events = \Etn\Utils\Helper::get_events();
            if( is_array($all_events) && !empty($all_events) ){
                foreach( $all_events as $event_id => $event_title ){
                    $event_price = !empty(get_post_meta( $event_id, "etn_ticket_price", true )) ? get_post_meta( $event_id, "etn_ticket_price", true ) : 0;
                    update_post_meta( $event_id, "_price", $event_price );
                    update_post_meta( $event_id, "_regular_price", $event_price );
                    update_post_meta( $event_id, "_sale_price", $event_price );
                }
            }

            update_option( "etn_event_price_migration_done", true );
        }
    }

    /**
     * Generate Unique ID For Attendee Ticket
     *
     * @return void
     */
    public function migrate_attendee_unique_id(){
        $migration_done = !empty( get_option( "etn_attendee_unique_id_migration_done" ) ) ? true : false;
        
        if( !$migration_done ){

            $args          = [
                'post_type' => 'etn-attendee',
            ];
            $all_attendees = get_posts($args);
            foreach( $all_attendees as $attendee ){
                $attendee_id    = $attendee->ID;
                $ticket_id      = Helper::generate_unique_ticket_id_from_attendee_id( $attendee_id );
                update_post_meta( $attendee_id, 'etn_unique_ticket_id', $ticket_id );
            }

            update_option( "etn_attendee_unique_id_migration_done", true );
        }
    }

}

if( !function_exists('etn_speaker_schedule_title_migration') ){

    /**
     * Migration To Update Speaker And Schedule Title
     *
     * @return void
     */
    function etn_speaker_schedule_title_migration(){
        
        $all_speakers = get_posts( [
            'post_type' => 'etn-speaker',
        ] );
        $all_schedule = get_posts( [
            'post_type' => 'etn-schedule',
        ] );

        if( is_array($all_speakers) && !empty( $all_speakers )){

            // update speaker data
            foreach( $all_speakers as $speaker ){
                $speaker_id     = $speaker->ID;
                $speaker_content= get_post_meta( $speaker->ID, 'etn_speaker_summery', true );
                $speaker_title  = get_post_meta( $speaker_id, 'etn_speaker_title', true );
                $post_slug      = sanitize_title_with_dashes( $speaker_title, '', 'save' );
                $speaker_slug   = sanitize_title( $post_slug );
                $speaker_data   = array(
                    'ID'           => $speaker_id,
                    'post_name'    => $speaker_slug, // new title
                    'post_title'   => $speaker_title, // new title
                    'post_content' => $speaker_content,
                );
                wp_update_post( $speaker_data );
            }
        }

        if( is_array($all_schedule) && !empty( $all_schedule )){
            //update schedule data
            foreach( $all_schedule as $schedule ){
                $schedule_id    = $schedule->ID;
                $schedule_title = get_post_meta( $schedule_id, 'etn_schedule_title', true );
                $post_slug      = sanitize_title_with_dashes( $schedule_title, '', 'save' );
                $schedule_slug  = sanitize_title( $post_slug );
                $schedule_data  = array(
                    'ID'            => $schedule_id,
                    'post_title'    => $schedule_title, // new title
                    'post_name'     => $schedule_slug,
                );
                wp_update_post( $schedule_data );
            }
        }
    }
}
// etn_speaker_schedule_title_migration();