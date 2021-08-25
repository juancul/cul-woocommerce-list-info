<?php

	/**
	 * A Plugin that shows all related pending orders to a subscrition to double check credit reporting
	 *
	 * @package cul-woocommerce-list-info
	 *
	 * Plugin Name:       CUL - Extra information in admin woocommerce lists
	 * Description:       Plugin that shows extra information in orders and subscription lists in the admin panel
	 * Version:           1.0
	 * Author:            CUL
	 */


	/* Add user information to orders list*/
add_action( 'manage_shop_order_posts_custom_column' , 'custom_orders_list_column_content', 50, 2 );
function custom_orders_list_column_content( $column, $post_id ) {
    if ( $column == 'order_number' )
    {
        global $the_order;

        if( $phone = $the_order->get_billing_phone() ){
            $phone_wp_dashicon = '<span class="dashicons dashicons-phone"></span> ';
            echo '<br><a href="https://wa.me/57'.substr($phone,3).'" target="_blank">' . $phone_wp_dashicon . $phone.'</a></strong>';
        }
    
    if( $phone2 = $the_order->get_billing_phone() ){
            $phone_wp_dashicon = '<span class="dashicons dashicons-admin-site-alt"></span> ';
            echo '<br><a href="skype:'.$phone.'?call" target="_blank">'.$phone_wp_dashicon.'Skype ' . $phone.'</a></strong>';
        }

        if( $email = $the_order->get_billing_email() ){
            echo '<br><strong><a href="mailto:'.$email.'">' . $email . '</a></strong><br>';
            $order = new WC_Order($post_id);
            $wp_user_id = my_get_wp_user_id($order);
            echo '<a href="https://vivecul.com.co/wp-admin/edit.php?post_status=all&post_type=shop_order&_customer_user='.$wp_user_id.'">ID de Usuario: ' .$wp_user_id. '</a> <a href="https://vivecul.com.co/wp-admin/user-edit.php?user_id='.$wp_user_id.'">-</a><br>';
            $allSubscriptions = WC_Subscriptions_Manager::get_users_subscriptions($wp_user_id);
            $active_sub_quantity = 0;
            //$active_amount = 0;
            $onhold_sub_quantity = 0;
            foreach ($allSubscriptions as $subscription){
                if ($subscription['status'] == 'active') {
                    $active_sub_quantity += 1;
                    //$active_amount += $subscription['total'];
                }
                if ($subscription['status'] == 'on-hold' | $subscription['status'] == 'late-payment-60' | $subscription['status'] == 'late-payment-90' | $subscription['status'] == 'late-payment-120' | $subscription['status'] == 'late-payment-150' | $subscription['status'] == 'late-payment-1801' | $subscription['status'] == 'bad-payment') {
                    $onhold_sub_quantity += 1;
                }
            }

            if ($active_sub_quantity > 1 && $onhold_sub_quantity > 0){
                echo '<a style ="background-color: #f54b42; color: #ffffff; padding: 3px;border-radius: 2px;" href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-active&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Activos: ' . $active_sub_quantity . '<br></a>';
                echo '<a style ="background-color: #f54b42; color: #ffffff; padding: 3px;border-radius: 2px;" href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-on-hold&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Pago Demorado: ' . $onhold_sub_quantity . '<br></a>';
            }

            else if ($active_sub_quantity <= 1 && $onhold_sub_quantity > 0){
                echo '<a href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-active&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Activos: ' . $active_sub_quantity . '<br></a>';
                echo '<a style ="background-color: #f54b42; color: #ffffff; padding: 3px;border-radius: 2px;" href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-on-hold&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Pago Demorado: ' . $onhold_sub_quantity . '<br></a>';
            }
            else if ($active_sub_quantity > 1 && $onhold_sub_quantity <= 0){
                echo '<a style ="background-color: #f54b42; color: #ffffff; padding: 3px;border-radius: 2px;" href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-active&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Activos: ' . $active_sub_quantity . '<br></a>';
                echo '<a href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-on-hold&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Pago Demorado: ' . $onhold_sub_quantity . '<br></a>';
            }
            else {
                echo '<a href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-active&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Activos: ' . $active_sub_quantity . '<br></a>';
                echo '<a href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-on-hold&post_type=shop_subscription&_customer_user='.$wp_user_id.'"> Alquileres Pago Demorado: ' . $onhold_sub_quantity . '<br></a>';
            }
            
            // Get COMPLETED orders for customer
            $args = array(
                'customer_id' => $wp_user_id,
                'post_status' => 'completed',
                'post_type' => 'shop_order',
                'return' => 'ids',
            );
            $numorders_completed = 0;
            $numorders_completed = count( wc_get_orders( $args ) ); // count the array of orders

            echo '<a href="https://vivecul.com.co/wp-admin/edit.php?post_status=wc-completed&post_type=shop_order&_customer_user='.$wp_user_id.'"> Pagos Exitosos: ' . $numorders_completed . '<br></a>';
            

            if (!empty($wp_user_id)) {
                // Only use the shortcode if we have a userid - otherwise the verified indicator shows your own status.
                echo do_shortcode('[tot-wp-embed tot-widget="verifiedIndicator" show-admin-buttons="true" tot-show-when-not-verified="true" wp-userid="' . $wp_user_id . '"][/tot-wp-embed]');
            }            
        }
    
    }
}