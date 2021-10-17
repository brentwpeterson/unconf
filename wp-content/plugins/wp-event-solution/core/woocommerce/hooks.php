<?php

namespace Etn\Core\Woocommerce;

use Etn\Utils\Helper;

defined( 'ABSPATH' ) || exit;

class Hooks {

    use \Etn\Traits\Singleton;

    public $action;
    public $base;

    public function Init() {

        add_filter( 'woocommerce_cart_item_name', [$this, 'etn_cart_item_name'], 10, 3 );
        add_filter( 'woocommerce_cart_item_price', [$this, '_cart_item_price'], 10, 3 );

        // add the filter 
        add_action('woocommerce_order_status_changed', [$this, 'update_event_stock_on_order_status_update' ], 10, 3);
        add_action('woocommerce_order_status_changed', [$this, 'change_attendee_payment_status_on_order_status_update' ], 10, 3);
        add_action('woocommerce_order_status_changed', [$this, 'change_purchase_report_status_on_order_status_update' ], 10, 3);
        add_action('woocommerce_order_status_changed', [$this, 'email_zoom_event_details_on_order_status_update' ], 10, 3);

        add_filter('woocommerce_hidden_order_itemmeta', [$this, 'hide_order_itemmeta_on_order_status_update'], 10, 1);

        // ====================== Attendee registration related hooks for woocommerce start ======================== //
        {
            // insert attendee data into database before add-to-cart
            add_action( 'woocommerce_add_to_cart', [$this, 'insert_attendee_before_add_to_cart'], 0 );
            // insert attendee data into cart item object
            add_filter( 'woocommerce_add_cart_item_data', [$this, 'add_cart_item_data'], 10, 3 );
            // Hide order item meta data (in thank you  and order page)
            add_filter( 'woocommerce_order_item_get_formatted_meta_data', [$this, 'hide_order_itemmeta'], 10, 2 );
            // save cart item data while checkout
            add_action( 'woocommerce_checkout_create_order_line_item', [$this, 'save_update_status_key'], 10, 4 );
        }
        
        // ===================== Attendee registration related hooks for woocommerce end ========================== //

        // compare cart item with stock in cart page
        add_action( 'woocommerce_before_cart', [$this, 'before_cart_check_stock'] );
        // compare cart item with stock before checkout
        add_action( 'woocommerce_before_checkout_form', [$this, 'before_checkout_check_stock'], 9 );
        // compare cart item with stock before place order
        add_action( 'woocommerce_after_checkout_validation', [$this, 'review_order_before_submit'] );

        // compare cart item with stock before adding to cart
        add_filter( 'woocommerce_add_to_cart_validation', [$this, 'validate_add_to_cart_item'], 10, 5 );

    }

    /**
     * Update Event Stock On Order Status Change
     *
     * @param [type] $order_id
     * @param [type] $old_order_status
     * @param [type] $new_order_status
     * @return void
     */
    public function update_event_stock_on_order_status_update( $order_id, $old_order_status, $new_order_status ){

        $decrease_states = [
            'processing',
            'on-hold',
            'completed',
        ];

        $increase_state = [
            'pending',
            'cancelled',
        ];

        $no_action_state = [
            'refunded',
            'failed',
        ];

        global $wpdb;
        $order = wc_get_order( $order_id );

        foreach ( $order->get_items() as $item_id => $item ){
            
            $decreased_stock = wc_get_order_item_meta( $item_id, '_etn_decreased_stock', true ); 

            if( $decreased_stock != (int) $item->get_quantity() ){
                if( in_array($new_order_status, $decrease_states) && !in_array($old_order_status, $decrease_states) ){
                     //decrease event stock
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

                        $event_id             = $event_object->ID;
                        $etn_sold_tickets     = get_post_meta( $event_id, 'etn_sold_tickets', true );
                        $etn_sold_tickets     = isset( $etn_sold_tickets ) ? intval( $etn_sold_tickets ) : 0;
                        $updated_sold_tickets = $etn_sold_tickets + intval( trim( $product_quantity ) );
                        $total_tickets        = get_post_meta( $event_id, 'etn_avaiilable_tickets', true );
                        if( $updated_sold_tickets <= $total_tickets && $updated_sold_tickets >= 0 ){
                            update_post_meta( $event_id, 'etn_sold_tickets', $updated_sold_tickets );
                            
                            wc_delete_order_item_meta( $item_id, '_etn_increased_stock' ); 
                            wc_add_order_item_meta( $item_id, '_etn_decreased_stock', $product_quantity, true);
                        }
                    }
                }
            }
        }

           
        foreach ( $order->get_items() as $item_id => $item ){

            $increased_stock = wc_get_order_item_meta( $item_id, '_etn_increased_stock', true ); 
  
            if( $increased_stock != (int) $item->get_quantity() ){
                if( in_array($new_order_status, $increase_state) && !in_array($old_order_status, $increase_state ) ){
                    //increase event stock
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

                        $event_id             = $event_object->ID;
                        $etn_sold_tickets     = get_post_meta( $event_id, 'etn_sold_tickets', true );
                        $etn_sold_tickets     = isset( $etn_sold_tickets ) ? intval( $etn_sold_tickets ) : 0;
                        $updated_sold_tickets = $etn_sold_tickets - intval( trim( $product_quantity ) );
                        $total_tickets        = get_post_meta( $event_id, 'etn_avaiilable_tickets', true );
                        if( $updated_sold_tickets <= $total_tickets && $updated_sold_tickets >= 0 ){
                            update_post_meta( $event_id, 'etn_sold_tickets', $updated_sold_tickets );
                            
                            wc_delete_order_item_meta( $item_id, '_etn_decreased_stock' ); 
                            wc_add_order_item_meta( $item_id, '_etn_increased_stock', $product_quantity, true);
                        }
                    }
                }
            }
        }

        return;
    }


    /**
     * Send Zoom Event Details On Status CHange
     *
     * @param [type] $order_id
     * @param [type] $old_order_status
     * @param [type] $new_order_status
     * @return void
     */
    function email_zoom_event_details_on_order_status_update(  $order_id, $old_order_status, $new_order_status ) {
         
        $payment_success_status_array = [
            // 'pending', 'on-hold', 'cancelled','refunded', 'failed',
            'processing',
            'completed',
        ];

        $zoom_email_sent = Helper::check_if_zoom_email_sent_for_order( $order_id );

        if( !$zoom_email_sent && in_array($new_order_status, $payment_success_status_array)){

            //email not sent yet and order order status is paid, so proceed..
            $order = wc_get_order( $order_id );
            Helper::send_email_with_zoom_meeting_details( $order_id, $order );
        }
    }

    /**
     * Change attandee payment status
     *
     * @param [type] $order_id
     * @param [type] $old_order_status
     * @param [type] $new_order_status
     * @return void
     */
    function change_attendee_payment_status_on_order_status_update(  $order_id, $old_order_status, $new_order_status ) {
        $order_attendees = Helper::get_attendee_by_woo_order( $order_id );
        if( is_array( $order_attendees ) && !empty( $order_attendees )){
            foreach($order_attendees as $attendee_id){
                Helper::update_attendee_payment_status($attendee_id, $new_order_status);
            }
        }
    }

    /**
     * Change Purchase Report Status On Order Status Change
     * 
     * @since 2.4.1
     *
     * @param [type] $order_id
     * @param [type] $old_order_status
     * @param [type] $new_order_status
     * @return void
     */
    function change_purchase_report_status_on_order_status_update( $order_id, $old_order_status, $new_order_status ) {

        $order_status_array = [
            'pending'   => "Pending",
            'processing'=> "Processing",
            'on-hold'   => "Hold",
            'completed' => "Completed",
            'cancelled' => "Cancelled",
            'refunded'  => "Refunded",
            'failed'    => "Failed",
        ];

        global $wpdb;
        $order = wc_get_order( $order_id );
        foreach ( $order->get_items() as $item_id => $item ) {
            
            $product_name     = $item->get_name(); // Get the event name
            $event_id         = !is_null( $item->get_meta( 'event_id', true ) ) ? $item->get_meta( 'event_id', true ) : "";

            if( !empty( $event_id ) ){
                $event_object = get_post( $event_id );
            } else{
                $event_object = get_page_by_title( $product_name, OBJECT, 'etn' );
            }

            if ( !empty( $event_object ) ) {
                //this item is an event, proceed...
                
                //update purchase history status
                $event_id    = $event_object->ID;
                $status      = $order_status_array[$new_order_status];
                $table_name  = $wpdb->prefix . "etn_events";
                $order_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE post_id = '$event_id' AND form_id = '$order_id'" );
                if ( $order_count > 0 ) {
                    $wpdb->query("UPDATE $table_name SET status = '$status' WHERE post_id = '$event_id' AND form_id = '$order_id'");
                }

            }
        }

        return;

    }

    /**
     * Display custom cart item meta data (in cart and checkout)
     */
    public function hide_order_itemmeta( $formatted_meta, $item ) {

        foreach ( $formatted_meta as $key => $meta ) {

            if ( isset( $meta->key ) && 'etn_status_update_key' == $meta->key ) {
                unset( $formatted_meta[$key] );
            }

            if ( isset( $meta->key ) && 'event_id' == $meta->key ) {
                unset( $formatted_meta[$key] );
            }

        }

        return $formatted_meta;
    }

    /**
     * save cart item custom meta as order item_meta to show in thank you and order page
     */
    public function save_update_status_key( $item, $cart_item_key, $values, $order ) {

        if ( isset( $values['etn_status_update_key'] ) ) {
            $item->update_meta_data( 'etn_status_update_key', $values['etn_status_update_key'] );
        }
        if ( isset( $values['event_id'] ) ) {
            $item->update_meta_data( 'event_id', $values['event_id'] );
        }

    }

    /**
     * Get event price
     */
    public function _cart_item_price( $price, $cart_item, $cart_item_key ) {
        return $price;
    }

    /**
     * Get event name
     */
    public function etn_cart_item_name( $product_title, $cart_item, $cart_item_key ) {

        if ( get_post_type( $cart_item['product_id'] ) == 'etn' ) {
            $_product          = $cart_item['data'];
            $return_value      = $_product->get_title();
            $product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';

            if ( !$product_permalink ) {
                $return_value = $_product->get_title() . '&nbsp;';
            } else {
                $return_value = sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_title() );
            }

            return $return_value;
        } else {

            return $product_title;
        }

    }

    /**
     * Post attendee data
     */
    public function insert_attendee_before_add_to_cart() {
        
        if ( isset( $_POST['ticket_purchase_next_step'] ) && $_POST['ticket_purchase_next_step'] === "three" ) {
       
            $post_arr = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
            
            $check    = wp_verify_nonce( $post_arr['ticket_purchase_next_step_three'], 'ticket_purchase_next_step_three' );

            if ( $check && !empty( $post_arr['attendee_info_update_key'] )
                && !empty( $post_arr["add-to-cart"] ) && !empty( $post_arr["quantity"] )
                && !empty( $post_arr["attendee_name"] ) ) {

                $access_token   = $post_arr['attendee_info_update_key'];
                $event_id       = $post_arr["add-to-cart"];
                $total_attendee = $post_arr["quantity"];
                $payment_token  = md5( 'etn-payment-token' . $access_token . time() . rand( 1, 9999 ) );
                $ticket_price   = get_post_meta( $event_id, "etn_ticket_price", true );

                // check if there's any attendee extra field set from Plugin Settings
                $settings              = Helper::get_settings();
                $attendee_extra_fields = isset($settings['attendee_extra_fields']) ? $settings['attendee_extra_fields'] : [];

                $extra_field_array = [];
                if( is_array( $attendee_extra_fields ) && !empty( $attendee_extra_fields )){

                    foreach( $attendee_extra_fields as $attendee_extra_field ){
                        $label_content = $attendee_extra_field['label'];

                        if( $label_content != '' ){
                            $name_from_label['label'] = $label_content;
                            $name_from_label['name']  = Helper::generate_name_from_label("etn_attendee_extra_field_", $label_content);
                            array_push( $extra_field_array, $name_from_label );
                        }
                    }
                }

                // insert attendee custom post
                for ( $i = 0; $i < $total_attendee; $i++ ) {
                    $attendee_name  = !empty( $post_arr["attendee_name"][$i] ) ? $post_arr["attendee_name"][$i] : "";
                    $attendee_email = !empty( $post_arr["attendee_email"][$i] ) ? $post_arr["attendee_email"][$i] : "";
                    $attendee_phone = !empty( $post_arr["attendee_phone"][$i] ) ? $post_arr["attendee_phone"][$i] : "";

                    $post_id = wp_insert_post( [
                        'post_title'  => $attendee_name,
                        'post_type'   => 'etn-attendee',
                        'post_status' => 'publish',
                    ] );

                    if ( $post_id ) {
                        $info_edit_token = md5( 'etn-edit-token' . $post_id . $access_token . time() );
                        $data            = [
                            'etn_status_update_token'       => $access_token,
                            'etn_payment_token'             => $payment_token,
                            'etn_info_edit_token'           => $info_edit_token,
                            'etn_timestamp'                 => time(),
                            'etn_name'                      => $attendee_name,
                            'etn_email'                     => $attendee_email,
                            'etn_phone'                     => $attendee_phone,
                            'etn_status'                    => 'failed',
                            'etn_attendeee_ticket_status'   => 'unused',
                            'etn_ticket_price'              => (float) $ticket_price,
                            'etn_event_id'                  => intval( $event_id ),
                            'etn_unique_ticket_id'          => Helper::generate_unique_ticket_id_from_attendee_id($post_id),
                        ];
                        
                        // check and insert attendee extra field data from attendee form
                        if( is_array( $extra_field_array ) && !empty( $extra_field_array ) ){
                            foreach( $extra_field_array as $key => $value ){
                                $data[$value['name']] = $post_arr[$value['name']][$i];
                            }
                        }
                                    
                        foreach ( $data as $key => $value ) {
                            // insert post meta data of attendee
                            update_post_meta( $post_id, $key, $value );
                        }

                        // Write post content (triggers save_post).
                        wp_update_post( ['ID' => $post_id] );
                    }

                }

                unset( $_POST['ticket_purchase_next_step'] );
            } else {
                wp_redirect( get_permalink() );
            }

        }

    }

    /**
     * get attendee info update token
     *
     */
    public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ){

        $post_arr = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

        if( isset( $post_arr['add-to-cart'] ) ) {
            $event_id = intval( $post_arr['add-to-cart'] );

            if( get_post_type( $event_id ) == 'etn' ){
                $cart_item_data['event_id'] = $event_id;
            }
        }
   
        if ( isset( $post_arr['attendee_info_update_key'] ) ) {
            $cart_item_data['etn_status_update_key'] = $post_arr['attendee_info_update_key'];
        }

        return $cart_item_data;
    }

     /**
     * Hide item specific meta so that thay won't show in order update page
     *
     * @param [type] $item_hidden_metas
     * @return array
     */
    function hide_order_itemmeta_on_order_status_update( $item_hidden_metas ){

        array_push( $item_hidden_metas, '_etn_increased_stock', '_etn_decreased_stock' );

        return $item_hidden_metas;
    }

    /**
     * validate cart quantity with stock so that user unable to add more item than stock quantity
     */
    function validate_add_to_cart_item( $passed, $product_id, $qty, $variation_id = '', $variations= '' ){
   
        if( get_post_type( $product_id ) == 'etn' ){
            $cart_item_quantities = WC()->cart->get_cart_item_quantities(); 

            if( is_array( $cart_item_quantities ) && !empty( $cart_item_quantities ) ){
                if( array_key_exists( $product_id, $cart_item_quantities ) ){
                    $already_qty = intval( $cart_item_quantities[$product_id] );

                    $total_sold_ticket = !empty( get_post_meta( $product_id, "etn_sold_tickets", true ) ) ? intval(get_post_meta( $product_id, "etn_sold_tickets", true )) : 0;
                    $available_ticket  = !empty( get_post_meta( $product_id, "etn_avaiilable_tickets", true ) ) ? intval(get_post_meta( $product_id, "etn_avaiilable_tickets", true )) : 0;
                    
                    $remaining_ticket  = $available_ticket - $total_sold_ticket;

                    $attempted_cart_qty= $already_qty + $qty;
                    
                    if( $attempted_cart_qty > $remaining_ticket ){
                        $passed = false;
                        wc_clear_notices();
                        
                        // $error_msg = 'cart add time';
                        $error_msg = esc_html__( 'You cannot add that amount to the cart — we have ', 'eventin' ) .$remaining_ticket. esc_html__( ' in stock and you already have ', 'eventin' ) .$already_qty. esc_html__( ' in your cart.', 'eventin' );
                        wc_add_notice( esc_html__( $error_msg, 'eventin' ), 'error' );
                        wp_safe_redirect(get_permalink( $product_id ) );
                        exit();
                    }           
                }
            }
        }

        return $passed;
    }

     /**
     * in cart page, compare cart item with stock
     */
    public function before_cart_check_stock(){

        $cart_stock_status  = $this->before_placeorder_check_stock();
        $proceed_to_go_next = $cart_stock_status['proceed_to_go_next'];
        
        if( !$proceed_to_go_next ){
            wc_clear_notices();

            $product_name     = $cart_stock_status['product_name'];
            $remaining_ticket = $cart_stock_status['remaining_ticket'];

            // $error_msg = 'before cart';
            $error_msg = esc_html__( 'Sorry, we do not have enough "' , 'eventin' ) . $product_name . esc_html__( '" in stock to fulfill your order (', 'eventin' ) . $remaining_ticket . esc_html__( ' available). We apologise for any inconvenience caused.', 'eventin' );
            wc_print_notice( esc_html__( $error_msg, 'eventin' ), 'error' );
        }
    }

    /**
     * in checkout page, compare cart item with stock
     */
    public function before_checkout_check_stock(){

        $cart_stock_status  = $this->before_placeorder_check_stock();
        $proceed_to_go_next = $cart_stock_status['proceed_to_go_next'];
        
        if( !$proceed_to_go_next ){
            wc_clear_notices();
           
            // $error_msg = 'before checkout';
            $error_msg = esc_html__( 'There are some issues with the items in your cart. Please go back to the cart page and resolve these issues before checking out.', 'eventin' );
            wc_print_notice( esc_html__( $error_msg, 'eventin' ), 'error' );

            $cart_url = wc_get_cart_url();
            ?>
            <a class="button wc-backward" href="<?php echo esc_url( $cart_url ); ?>"><?php echo esc_html__( 'Return to cart', 'eventin' ); ?></a>
            
            <?php
            die();
        }

    }

   /**
     * in checkout page, when click place order button: final chance to compare cart item with stock
     */
    public function review_order_before_submit(){

        $cart_stock_status  = $this->before_placeorder_check_stock();
        $proceed_to_go_next = $cart_stock_status['proceed_to_go_next'];

        if( !$proceed_to_go_next ){
            wc_clear_notices();
            
            // $error_msg = 'before place order';
            $error_msg = esc_html__( 'There are some issues with the items in your cart. Please go back to the cart page and resolve these issues before checking out.', 'eventin' );
            wc_add_notice( esc_html__($error_msg, 'eventin' ), 'error' );
        }
   
    }

    /**
     * compare cart item with stock. If greater than stock: notice user with error message
     */
    public function before_placeorder_check_stock(){
     
        $proceed_to_go_next = true;
        $product_name = ''; $remaining_ticket = 0;

        $cart_item_quantities = WC()->cart->get_cart_item_quantities();

        if( is_array( $cart_item_quantities ) && !empty( $cart_item_quantities ) ){
            foreach( $cart_item_quantities as $product_id => $quantity ){

                if( get_post_type( $product_id ) == 'etn' ){
                    $product_name = get_the_title( $product_id );

                    $total_sold_ticket = !empty( get_post_meta( $product_id, "etn_sold_tickets", true ) ) ? intval(get_post_meta( $product_id, "etn_sold_tickets", true )) : 0;
                    $available_ticket  = !empty( get_post_meta( $product_id, "etn_avaiilable_tickets", true ) ) ? intval(get_post_meta( $product_id, "etn_avaiilable_tickets", true )) : 0;
                    
                    $remaining_ticket  = $available_ticket - $total_sold_ticket;

                    if( $quantity > $remaining_ticket ){
                        $proceed_to_go_next = false;
                        break;
                    }
                }
     
            }
        }
        $return_arr = [
            'proceed_to_go_next' => $proceed_to_go_next,
            'product_name'       => $product_name,
            'remaining_ticket'   => $remaining_ticket,
        ];

        return $return_arr;
    }
}
