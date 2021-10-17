<?php
 
if ( $check && !empty( $post_arr["quantity"] ) && !empty( $post_arr["event_id"] ) ) {

    $total_qty                = intval( $post_arr["quantity"] );
    $attendee_info_update_key = md5( md5( "etn-access-token" . time() . $total_qty ) );
    wp_head();

    ?>
 
    <div class="etn-es-events-page-container etn-attendee-registration-page">
        <div class="etn-event-single-wrap">
            <div class="etn-container">
                <div class="etn-attendee-form">
                <h3 class="attendee-title"><?php echo esc_html__( "Attendee Details for ", "eventin" ) . esc_html( $post_arr["event_name"] ); ?></h3>
                    <form action="" method="post" id="etn-event-attendee-data-form" class="attende_form">
                        <?php wp_nonce_field( 'ticket_purchase_next_step_three', 'ticket_purchase_next_step_three' );?>

                        <input type="hidden" name="ticket_purchase_next_step" value="three" />
                        <input type="hidden" name="event_name" value="<?php echo esc_html( $post_arr["event_name"] ); ?>" />
                        <input type="hidden" name="add-to-cart" value="<?php echo intval( $post_arr["event_id"] ); ?>" />
                        <input type="hidden" name="quantity" value="<?php echo intval( $post_arr["quantity"] ); ?>" />
                        <input type="hidden" name="attendee_info_update_key" value="<?php echo esc_html( $attendee_info_update_key ); ?>" />

                        <?php
                            for ( $i = 1; $i <= $total_qty; $i++ ) {
                                ?>
                                <div class="etn-attendee-form-wrap">
                                    <div class="etn-attendy-count">
                                        <h4><?php echo esc_html__( "Attendee - ", "eventin" ) . $i; ?></h4>
                                    </div>
                                    <div class="etn-name-field etn-group-field">
                                        <label for="etn_product_qty">
                                            <?php echo esc_html__( 'Name', "eventin" ); ?> <span class="etn-input-field-required">*</span>
                                        </label>
                                        <input required placeholder="<?php echo esc_html__('Enter attendee full name', 'eventin'); ?>" class="attr-form-control" id="attendee_name_<?php echo intval( $i ) ?>" name="attendee_name[]"  type="text"/>
                                        <div class="etn-error attendee_name_<?php echo intval( $i ) ?>"></div>
                                    </div>
                                    <?php

                                    if ( $include_email ) {
                                        ?>
                                        <div class="etn-email-field etn-group-field">
                                            <label for="etn_product_qty">
                                                <?php echo esc_html__( 'Email', "eventin" ); ?><span class="etn-input-field-required"> *</span>
                                            </label>
                                            <input required placeholder="<?php echo esc_html__('Enter email address', 'eventin'); ?>" class="attr-form-control" id="attendee_email_<?php echo intval( $i ) ?>" name="attendee_email[]" type="email"/>
                                            <div class="etn-error attendee_email_<?php echo intval( $i ) ?>"></div>
                                        </div>
                                        <?php
                                    }

                                    if ( $include_phone ) {
                                        ?>
                                        <div class="etn-phone-field etn-group-field">
                                            <label for="etn_product_qty">
                                                <?php echo esc_html__( 'Phone', "eventin" ); ?><span class="etn-input-field-required"> *</span>
                                            </label>
                                            <input required placeholder="<?php echo esc_html__('Enter phone number', 'eventin'); ?>" class="attr-form-control" maxlength="15" id="attendee_phone_<?php echo intval( $i ) ?>" name="attendee_phone[]" type="tel"/>
                                            <div class="etn-error attendee_phone_<?php echo intval( $i ) ?>"></div>
                                        </div>
                                        <?php
                                    }

                                    $attendee_extra_fields = isset($settings['attendee_extra_fields']) ? $settings['attendee_extra_fields'] : [];
    
                                    if ( is_array($attendee_extra_fields) && !empty($attendee_extra_fields) ){
                                        foreach( $attendee_extra_fields as $index => $attendee_extra_field ){
                                            
                                            $label_content = $attendee_extra_field['label'];
                                            if( !empty($label_content) && !empty($attendee_extra_field['type']) ){ 
                                                $name_from_label       = \Etn\Utils\Helper::generate_name_from_label( "etn_attendee_extra_field_" , $label_content); 
                                                $class_name_from_label = \Etn\Utils\Helper::get_name_structure_from_label($label_content);
                                                ?>

                                                <div class="etn-<?php echo esc_attr( $class_name_from_label ); ?>-field etn-group-field">
                                                    <label for="extra_field_<?php echo esc_attr( $index ) . "_attendee_" . intval( $i ) ?>">
                                                        <?php echo esc_html( $label_content ); ?><span class="etn-input-field-required"> *</span>
                                                    </label>
                                                    
                                                    <?php
                                                        if( $attendee_extra_field['type'] == 'radio1' ){
                                                            $radio_arr = isset( $attendee_extra_field['radio'] ) ? $attendee_extra_field['radio'] : [];
                                                            
                                                            if ( is_array($radio_arr) && !empty($radio_arr) ){
                                                                ?>
                                                                <div class="etn-radio-field-wrap">
                                                                <?php
                                                                    foreach( $radio_arr as $radio_index => $radio_val ){
                                                                        ?>
                                                                            <div class="etn-radio-field">
                                                                                <input type="radio" name="<?php echo esc_attr( $name_from_label ); ?>[]" value="<?php echo esc_attr( $radio_index ); ?>"
                                                                                       class="etn-attendee-extra-fields" id="etn_attendee_extra_field_<?php echo esc_attr( $index ) . "_attendee_" . intval( $i ) ?> etn_attendee_extra_field_<?php echo esc_attr( $index ) . "_attendee_" . intval( $radio_index ); ?>" />
                                                                                <label for="etn_attendee_extra_field_<?php echo esc_attr( $index ) . "_attendee_" . intval( $radio_index ); ?>"><?php echo esc_html( $radio_val ); ?></label>
                                                                            </div>
                                                                        <?php
                                                                    }
                                                                ?>
                                                                </div>
                                                                <?php
                                                            }
                                                        } else { 
                                                            ?>
                                                            <input type="<?php echo esc_html( $attendee_extra_field['type'] ); ?>" 
                                                                name="<?php echo esc_attr( $name_from_label ); ?>[]"
                                                                class="attr-form-control etn-attendee-extra-fields" 
                                                                id="etn_attendee_extra_field_<?php echo esc_attr( $index ) . "_attendee_" . intval( $i ) ?>" 
                                                                placeholder="<?php echo esc_attr( !empty($attendee_extra_field['place_holder']) ? $attendee_extra_field['place_holder'] : '' ); ?>" 
                                                                <?php echo ($attendee_extra_field['type'] == 'number') ? "pattern='\d+'" : '' ?> required /> 
                                                            <?php
                                                        }
                                                    ?>
                                                   
                                                    <div class="etn-error etn_attendee_extra_field_<?php echo esc_attr( $index ) . "_attendee_" . intval( $i ) ?>"></div>
                                                </div>
                                                <?php
                                                } else { ?>
                                                    <p class="error-text"><?php echo esc_html__( 'Please Select input type & label name from admin', 'eventin' ); ?></p>
                                                <?php 
                                            } 

                                        }
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        <input type="submit" name="submit" class="etn-btn etn-primary attendee_sumbit" value="<?php echo esc_html__( "Confirm", "eventin" ); ?>" />
                        <a href="<?php echo get_permalink(); ?>" class="etn-btn etn-btn-secondary"><?php echo esc_html__( "Go Back", "eventin" ); ?></a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php 
    wp_footer();
    exit;
} else {
    wp_redirect( get_permalink() );
}

return;

