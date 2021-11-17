<?php
/**
 * Process to apply dynamic discount to users.
 *
 * @package Thoughtcloud Affiliate Manager
 *
 * @author Wbcom Designs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Thoughtcloud_Manage_Coupon_Amount' ) ) {

	/**
	 * Class to apply dynamic discount to users.
	 */
	class Thoughtcloud_Manage_Coupon_Amount {

		/**
		 * The single instance of the class.
		 *
		 * @var Thoughtcloud_Manage_Coupon_Amount
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main Thoughtcloud_Manage_Coupon_Amount Instance.
		 *
		 * Ensures only one instance of Thoughtcloud_Manage_Coupon_Amount is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return Thoughtcloud_Manage_Coupon_Amount - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Thoughtcloud_Manage_Coupon_Amount Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			
			/**
 			* Managing coupon amount in cart and checkout page frontend.
 			*/
			add_filter( 'woocommerce_calculated_total', array( $this, 'manage_woo_calculated_total' ), 10, 2 );

			/**
 			* Managing coupon amount in order table frontend.
 			*/
 			add_filter( 'woocommerce_get_order_item_totals', array( $this, 'manage_coupon_price_in_order' ), 10, 3 );
 			
 			/**
 			* Managing coupon amount in order table backend.
 			*/
 			add_filter( 'woocommerce_order_get_total_discount', array( $this, 'woocommerce_order_get_total_discount' ), 10, 2 );
 		}
		
		/**
 		* Managing coupon amount in order table backend.
 		*/
		public function woocommerce_order_get_total_discount( $total_discount, $order ) {
		    $total_coupon_discount = 0.0;
			foreach ( $order->get_items( 'coupon' ) as $coupon_item ) {
				$coupon_code   = $coupon_item->get_code();
				$coupon_discount   = $coupon_item->get_discount();
				$coupon_id     = wc_get_coupon_id_by_code( $coupon_code );
				if( get_post_meta( $coupon_id, 'affwp_discount_affiliate', true ) ) {
					$total_coupon_discount += $coupon_discount;
					break;
				}
			}
			
			if( $total_coupon_discount ) {
			    $total_discount = round( $total_coupon_discount, WC_ROUNDING_PRECISION );
			}
			
			return $total_discount;
		}
		
		
		/**
		* Managing coupon amount in order table frontend.
		*/
		public function manage_coupon_price_in_order( $total_rows, $order, $tax_display ) {
			$total_coupon_discount = 0.0;
			foreach ( $order->get_items( 'coupon' ) as $coupon_item ) {
				$coupon_code   = $coupon_item->get_code();
				$coupon_discount   = $coupon_item->get_discount();
				$coupon_id     = wc_get_coupon_id_by_code( $coupon_code );
				if( get_post_meta( $coupon_id, 'affwp_discount_affiliate', true ) ) {
					$total_coupon_discount += $coupon_discount;
					break;
				}
			}
    
            if( $total_coupon_discount ) {
				if( isset( $total_rows['discount']['value'] ) ) {
					$total_rows['discount']['value'] = '-' . wc_price( $total_coupon_discount );
				}
			}
			
			if( $order->get_used_coupons() ) {
				$used_coupons_list = '';
				foreach( $order->get_used_coupons() as $coupon ) {
				    $used_coupons_list .= $coupon . '<br/>';
				}

				$_total_rows = array();
				foreach( $total_rows as $key => $value ) {
				    $_total_rows[$key] = $value;
					if( 'discount' === $key ) {
						$_total_rows['coupons_used'] = array(
							'label'	=>	__( 'Coupon(s) Used:', 'thoughtcl0ud-affiliate-manager' ),
							'value'	=> $used_coupons_list,
						);
					}
				}
				$total_rows = $_total_rows;
			}
			
			return $total_rows;
		}

		/**
		* Managing coupon amount in cart and checkout page frontend.
		*/
		public function manage_woo_calculated_total( $total, $cart ) {
			$old_discount = 0.0;
			$discount = 0.0;
			$_tc_discount_type = get_option( '_tc_discount_type', 'fixed-price' );
			if( isset( $cart->coupon_discount_totals ) && !empty( $cart->coupon_discount_totals ) && is_array( $cart->coupon_discount_totals ) ) {
				$product_item = $cart->cart_contents;
				
				/* Get Coupon ID */
				foreach ($cart->coupon_discount_totals as $key => $value) {
					$coupon_id = wc_get_coupon_id_by_code( $key );
				}
				
				foreach ($cart->cart_contents as $key => $value) {
					//$coupon_id = wc_get_coupon_id_by_code( $key );
					$line_subtotal = $value['line_subtotal'];
					
					if( get_post_meta( $coupon_id, 'affwp_discount_affiliate', true ) ) {						
						$line_subtotal = floatval( $line_subtotal );
						$discount = $this->get_dynamic_discount_amount( $line_subtotal );
						$old_discount = $cart->coupon_discount_totals[$key];
						if( $discount ) {
							if( 'fixed-price' === $_tc_discount_type ) {
								$cart->cart_contents[$key]['line_total'] = $line_subtotal - $discount;
							}
							else {
								$discount = ( ( $discount / 100 ) * ( $line_subtotal + $old_discount ) );
								$cart->cart_contents[$key]['line_total'] = $line_subtotal - $discount;
							}
						}						
					}
				}
				
				
				foreach ($cart->coupon_discount_totals as $key => $value) {
					$coupon_id = wc_get_coupon_id_by_code( $key );
					if( get_post_meta( $coupon_id, 'affwp_discount_affiliate', true ) ) {
						global $woocommerce;
						$cart_total = $woocommerce->cart->get_subtotal();
						$cart_total = floatval( $cart_total );
						$discount = $this->get_dynamic_discount_amount( $cart_total );
						$old_discount = $cart->coupon_discount_totals[$key];
						if( $discount ) {
							if( 'fixed-price' === $_tc_discount_type ) {
								$cart->coupon_discount_totals[$key] = $discount;
							}
							else {
								$discount = ( ( $discount / 100 ) * ( $cart_total + $old_discount ) );
								$cart->coupon_discount_totals[$key] = $discount;
							}
						}
						break;
					}
				}
				
			}
			if( $discount ) {
				$total += $old_discount;
				$total -= $discount;
			}
			return $total;
		}

		/**
		* Function to calculate discount dynamically depending upon cart total.
		*/
		public function get_dynamic_discount_amount( $cart_total ) {
			$discount = 0.0;
			$discount_rule_array = get_option( '_tc_discount_rule_book', array() );
			ksort( $discount_rule_array );
			$discount_rule_array = array_reverse( $discount_rule_array, true );
			foreach ($discount_rule_array as $key => $value) {
				if( $cart_total >= $key ) {
					$discount = $value;
					break;
				}
			}
			return $discount;
		}

	}
}

/**
 * Main instance of Thoughtcloud_Manage_Coupon_Amount.
 *
 * @return Thoughtcloud_Manage_Coupon_Amount
 */
Thoughtcloud_Manage_Coupon_Amount::instance();