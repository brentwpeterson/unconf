<?php

use Etn\Utils\Helper;

defined('ABSPATH') || exit;

if ( !class_exists( 'WC_Product_Data_Store_CPT' ) ) {
    return;
}

class Etn_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT implements WC_Object_Data_Store_Interface, WC_Product_Data_Store_Interface {

    /**
     * Method to read a product from the database.
     * @param WC_Product
     */
    public function read( &$product ) {
        
        $product->set_defaults();

        if ( !$product->get_id() || !( $post_object = get_post( $product->get_id() ) ) || !in_array( $post_object->post_type, ['etn', 'product'] ) ) {
            throw new Exception( esc_html__( 'Invalid product.', 'eventin' ) );
        }

        // $id = $product->get_id();

        $product->set_id( $post_object->ID );
        
        $product->set_props( [
            'product_id'        => $post_object->ID,
            'name'              => $post_object->post_title,
            'slug'              => $post_object->post_name,
            'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
            'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
            'status'            => $post_object->post_status,
            'description'       => $post_object->post_content,
            'short_description' => $post_object->post_excerpt,
            'parent_id'         => $post_object->post_parent,
            'menu_order'        => $post_object->menu_order,
            'reviews_allowed'   => 'open' === $post_object->comment_status,
        ] );

        $this->read_attributes( $product );
        $this->read_downloads( $product );
        $this->read_visibility( $product );
        $this->read_product_data( $product );
        $this->read_extra_data( $product );
        $product->set_object_read( true );
    }

    /**
     * Get the product type based on product ID.
     */
    public function get_product_type( $product_id ) {

        $post_type = get_post_type( $product_id );

        if ( 'product_variation' === $post_type ) {
            return 'variation';
        } elseif ( in_array( $post_type, ['etn', 'product'] ) ) {
            $terms = get_the_terms( $product_id, 'product_type' );
            return !empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'etn';
        } else {
            return false;
        }

    }

}


/**
 * returns the price of the custom product
 * product is the custom post we are creating
 */
function etn_woocommerce_product_get_price( $price, $product ) {
    $product_id = $product->get_id();

    if ( get_post_type( $product_id ) == 'etn' ) {
        $price = get_post_meta( $product_id, 'etn_ticket_price', true );
        $price = isset( $price ) ? ( floatval( $price ) ) : 0;
    }

    return $price;
}

/**
 * overwrite woocommerce store and make our custom post as a product
 */
function etn_woocommerce_data_stores( $stores ) {
    $stores['product'] = 'Etn_Product_Data_Store_CPT';

    return $stores;
}

/**
 * Return product quantity
 */
function wc_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {

    // deactivate product quantity
    if ( is_cart() ) {
        if ( get_post_type( $cart_item['product_id'] ) == 'etn' ) {

            $product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
        }

    }

    return $product_quantity;
}

/**
 * aftersuccessfull checkout, some data are returned from woocommerce
 * we can use these data to update our own data storage / tables
 */
function etn_checkout_callback( $order_id ) {

    if ( !$order_id ) {
        return;
    }

    global $wpdb;
    
    $order = wc_get_order( $order_id );

    if ( $order->is_paid() ) {
        $paid = 'Paid';
    } else {
        $paid = 'Unpaid';
    }

    // Allow code execution only once
    if ( !get_post_meta( $order_id, '_thankyou_action_done', true ) ) {

        $userId = 0;
        if ( is_user_logged_in() ) {
            $userId = get_current_user_id();
        }
        
        $payment_type = get_post_meta( $order_id, '_payment_method', true );
        $order_status = !empty( get_post_status( $order_id ) ) ? get_post_status( $order_id ) : '';

        if ( $payment_type == 'cod' ) {
            $etn_payment_method = 'offline_payment';
        } elseif ( $payment_type == 'bacs' ) {
            $etn_payment_method = 'bank_payment';
        } elseif ( $payment_type == 'cheque' ) {
            $etn_payment_method = 'check_payment';
        } elseif ( $payment_type == 'stripe' ) {
            $etn_payment_method = 'stripe_payment';
        } else {
            $etn_payment_method = 'online_payment';
        }

        if ( $order_status == 'wc-pending' ) {
            $status = 'Pending';
        } elseif ( $order_status == 'wc-processing' ) {
            $status = 'Processing';
        } elseif ( $order_status == 'wc-on-hold' ) {
            $status = 'Hold';
        } elseif ( $order_status == 'wc-completed' ) {
            $status = 'Completed';
        } elseif ( $order_status == 'wc-refunded' ) {
            $status = 'Refunded';
        } elseif ( $order_status == 'wc-failed' ) {
            $status = 'Failed';
        } else {
            $status = 'Pending';
        }

        foreach ( $order->get_items() as $item_id => $item ) {
            
            // Get the product name
            $product_name     = $item->get_name();
            $event_id         = !is_null( $item->get_meta( 'event_id', true ) ) ? $item->get_meta( 'event_id', true ) : "";
            $product_quantity = (int) $item->get_quantity();
            $product_total    = $item->get_total();

            if( !empty( $event_id ) ){
                $event_object = get_post( $event_id );
            } else{
                $event_object = get_page_by_title( $product_name, OBJECT, 'etn' );
            }

            if ( !empty( $event_object->post_type ) && ('etn' == $event_object->post_type) ) {

                $event_id             = $event_object->ID;

                $pledge_id = "";
                $insert_post_id         = $event_id;
                $insert_form_id         = $order_id;
                $insert_invoice         = get_post_meta( $order_id, '_order_key', true );
                $insert_event_amount    = $product_total;
                $insert_user_id         = $userId;
                $insert_email           = get_post_meta( $order_id, '_billing_email', true );
                $insert_event_type      = "ticket";
                $insert_payment_type    = 'woocommerce';
                $insert_pledge_id       = $pledge_id;
                $insert_payment_gateway = $etn_payment_method;
                $insert_date_time       = date( "Y-m-d" );
                $insert_status          = $status;
                $inserted               = $wpdb->query( "INSERT INTO `" . $wpdb->prefix . "etn_events` (`post_id`, `form_id`, `invoice`, `event_amount`, `user_id`, `email`, `event_type`, `payment_type`, `pledge_id`, `payment_gateway`, `date_time`, `status`) VALUES ('$insert_post_id', '$insert_form_id', '$insert_invoice', '$insert_event_amount', '$insert_user_id', '$insert_email', '$insert_event_type', '$insert_payment_type', '$insert_pledge_id', '$insert_payment_gateway', '$insert_date_time', '$insert_status')" );
                $id_insert              = $wpdb->insert_id;

                if ( $inserted ) {
                    $metaKey                              = [];
                    $metaKey['_etn_first_name']           = get_post_meta( $order_id, '_billing_first_name', true );
                    $metaKey['_etn_last_name']            = get_post_meta( $order_id, '_billing_last_name', true );
                    $metaKey['_etn_email']                = get_post_meta( $order_id, '_billing_email', true );
                    $metaKey['_etn_post_id']              = $event_id;
                    $metaKey['_etn_order_key']            = '_etn_' . $id_insert;
                    $metaKey['_etn_order_shipping']       = get_post_meta( $order_id, '_order_shipping', true );
                    $metaKey['_etn_order_shipping_tax']   = get_post_meta( $order_id, '_order_shipping_tax', true );
                    $metaKey['_etn_order_qty']            = $product_quantity;
                    $metaKey['_etn_order_total']          = $product_total;
                    $metaKey['_etn_order_tax']            = get_post_meta( $order_id, '_order_tax', true );
                    $metaKey['_etn_addition_fees']        = 0;
                    $metaKey['_etn_addition_fees_amount'] = 0;
                    $metaKey['_etn_addition_fees_type']   = '';
                    $metaKey['_etn_country']              = get_post_meta( $order_id, '_billing_country', true );
                    $metaKey['_etn_currency']             = get_post_meta( $order_id, '_order_currency', true );
                    $metaKey['_etn_date_time']            = date( "Y-m-d H:i:s" );

                    foreach ( $metaKey as $k => $v ) {
                        $data               = [];
                        $data["event_id"]   = $id_insert;
                        $data["meta_key"]   = $k;
                        $data["meta_value"] = $v;
                        $wpdb->insert( $wpdb->prefix . "etn_trans_meta", $data );
                    }
                }

                // ========================== Attendee related works start ========================= //
                $settings               = Helper::get_settings();
                $attendee_reg_enable    = !empty( $settings["attendee_registration"] ) ? true : false;
                if( $attendee_reg_enable ){
                    // update attendee status and send ticket to email
                    $event_location   = !is_null( get_post_meta( $event_object->ID , 'etn_event_location', true ) ) ? get_post_meta( $event_object->ID , 'etn_event_location', true ) : "";
                    $etn_ticket_price = !is_null( get_post_meta( $event_object->ID , 'etn_ticket_price', true ) ) ? get_post_meta( $event_object->ID , 'etn_ticket_price', true ) : "";
                    $etn_start_date   = !is_null( get_post_meta( $event_object->ID , 'etn_start_date', true ) ) ? get_post_meta( $event_object->ID , 'etn_start_date', true ) : "";
                    $etn_end_date     = !is_null( get_post_meta( $event_object->ID , 'etn_end_date', true ) ) ? get_post_meta( $event_object->ID , 'etn_end_date', true ) : "";
                    $etn_start_time   = !is_null( get_post_meta( $event_object->ID , 'etn_start_time', true ) ) ? get_post_meta( $event_object->ID , 'etn_start_time', true ) : "";
                    $etn_end_time     = !is_null( get_post_meta( $event_object->ID , 'etn_end_time', true ) ) ? get_post_meta( $event_object->ID , 'etn_end_time', true ) : "";
                    $update_key       = !is_null( $item->get_meta( 'etn_status_update_key', true ) ) ? $item->get_meta( 'etn_status_update_key', true ) : "";
                    $insert_email     = !is_null( get_post_meta( $order_id, '_billing_email', true ) ) ? get_post_meta( $order_id, '_billing_email', true ) : "";
        
                    $pdf_data = [
                        'order_id'          => $order_id,
                        'event_name'        => $product_name ,
                        'update_key'        => $update_key ,
                        'user_email'        => $insert_email , 
                        'event_location'    => $event_location , 
                        'etn_ticket_price'  => $etn_ticket_price,
                        'etn_start_date'    => $etn_start_date,
                        'etn_end_date'      => $etn_end_date,
                        'etn_start_time'    => $etn_start_time,
                        'etn_end_time'      => $etn_end_time  
                    ];
                    
                    Helper::mail_attendee_report( $pdf_data );
                }
                // ========================== Attendee related works start ========================= //
            }
        }

        $order->update_meta_data( '_thankyou_action_done', true );
        $order->save();
    }
    ?>
    <div class="etn-thankyou-page-order-details">
        <?php echo esc_html__( "Order ID: ", "eventin" ) . esc_html( $order_id ); ?> | <?php echo esc_html__("Order Status: ", "eventin") . esc_html( wc_get_order_status_name( $order->get_status() )); ?> | <?php echo esc_html__( "Order is Payment Status: ", "eventin" ) . esc_html( $paid ); ?>
    </div>
    <?php
    //checking for zoom event
    show_zoom_events_details( $order );

    do_action("eventin/after_thankyou");

}


/**
 * check if any zoom meeting exists in order
 */
function show_zoom_events_details( $order ) {
    
    foreach ( $order->get_items() as $item_id => $item ) {
        // Get the product name
        $product_name     = $item->get_name();
        $event_id         = !is_null( $item->get_meta( 'event_id', true ) ) ? $item->get_meta( 'event_id', true ) : "";
            
        if( !empty( $event_id ) ){
            $product_post = get_post( $event_id );
        } else{
            $product_post = get_page_by_title( $product_name, OBJECT, 'etn' );
        }

        if ( !empty( $product_post ) ) {
            $post_id        = $product_post->ID;
            $is_zoom_event  = Helper::check_if_zoom_event($post_id);

            if( $is_zoom_event ){
                ?>
                <div class="etn-thankyou-page-order-details">
                <?php echo esc_html__('NB. This order includes Events which will be hosted on Zoom. After successful payment, Zoom details will be sent through email', 'eventin');?>
                </div>
                <?php
                break;
            }
        }
    }
}

/**
 * @snippet Redirect to Checkout Upon Add to Cart - WooCommerce
 */
function etn_redirect_checkout_add_cart() {
    $add_to__redirect       = empty( \Etn\Utils\Helper::get_option( 'add_to_cart_redirect' ) ) ? 'event' : \Etn\Utils\Helper::get_option( 'add_to_cart_redirect' );
    if( 'cart' == $add_to__redirect ){
        return wc_get_cart_url();
    } elseif('checkout' == $add_to__redirect){
        return wc_get_checkout_url();
    }
}

 //all hooks required to hook our event as woocommerce product
add_filter( 'woocommerce_add_to_cart_redirect', 'etn_redirect_checkout_add_cart' );
add_filter( 'woocommerce_data_stores', 'etn_woocommerce_data_stores' );
add_filter( 'woocommerce_product_get_price', 'etn_woocommerce_product_get_price', 10, 2 );
add_filter( 'woocommerce_cart_item_quantity', 'wc_cart_item_quantity', 10, 3 );
add_action( 'woocommerce_thankyou', 'etn_checkout_callback', 10, 1 );


