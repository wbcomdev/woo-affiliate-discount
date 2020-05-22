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

if ( ! class_exists( 'Thoughtcloud_Discount_Rule_Panel' ) ) {

	/**
	 * Class to apply dynamic discount to users.
	 */
	class Thoughtcloud_Discount_Rule_Panel {

		/**
		 * The single instance of the class.
		 *
		 * @var Thoughtcloud_Discount_Rule_Panel
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main Thoughtcloud_Discount_Rule_Panel Instance.
		 *
		 * Ensures only one instance of Thoughtcloud_Discount_Rule_Panel is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return Thoughtcloud_Discount_Rule_Panel - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Thoughtcloud_Discount_Rule_Panel Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			/**
 			* Adding discount rule menu under settings menu.
 			*/
 			add_action( 'admin_menu', array( $this, 'menu_discount_rule_panel' ), 10 );
			
			/**
 			* Managing coupon amount in cart and checkout page frontend.
 			*/
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_discount_rule_panel_scripts_n_styles' ), 10 );

			/**
 			* Saving discount rules.
 			*/
			add_action( 'wp_loaded', array( $this, 'save_discount_rules' ), 10 );
			
		}

		/**
		* Saving discount rules.
		*/
		public function save_discount_rules() {
			if( isset( $_POST['_tc_discount_rule_submit'] ) ) {
				if ( ! empty( $_POST ) && check_admin_referer( 'thoughtcl0ud-affiliate-manager-action', 'thoughtcl0ud-affiliate-manager-nonce' ) ) {
					$discount_rule_book = array();
					if( isset( $_POST['_tc_discount_rule']['cart_total'] ) && is_array( $_POST['_tc_discount_rule']['cart_total'] ) ) {
						foreach ( $_POST['_tc_discount_rule']['cart_total'] as $key => $value ) {
							if( !empty( $_POST['_tc_discount_rule']['cart_total'][$key] ) && !empty( $_POST['_tc_discount_rule']['discount'][$key] ) ) {
								$discount_rule_book[$_POST['_tc_discount_rule']['cart_total'][$key]] = $_POST['_tc_discount_rule']['discount'][$key];
							}
						}
					}
					update_option( '_tc_discount_rule_book', $discount_rule_book );
					
					if( isset( $_POST['_tc_discount_type'] ) ) {
						update_option( '_tc_discount_type', $_POST['_tc_discount_type'] );
					}
				}
			}
		}

		/**
		* Adding discount rule menu under settings menu.
		*/
		public function menu_discount_rule_panel() {
			add_options_page(
				__( 'ThoughtCl0ud Discount Panel', 'thoughtcl0ud-affiliate-manager'),
				__( 'TC Discount Panel', 'thoughtcl0ud-affiliate-manager'),
				'manage_options',
				'thoughtcl0ud-affiliate-manager',
				array( $this, 'render_discount_rule_panel' )
			);
		}

		/**
		* Function to render discount rule panel.
		*/
		public function render_discount_rule_panel() {
			if( isset( $_POST['_tc_discount_rule_submit'] ) ) {
				?>
				<div class="updated notice is-dismissible">
					<p><strong>
						<?php _e( 'Discount rules saved successfully.', 'thoughtcl0ud-affiliate-manager' ); ?>
					</strong></p>
				</div>
				<?php
			}
			$discount_rule_book = get_option( '_tc_discount_rule_book', array() );
			$_tc_discount_type = get_option( '_tc_discount_type', 'fixed-price' );
			?>
			<div class="wrap">
				<h2><?php _e( 'Set Discount Rules Below:', 'thoughtcl0ud-affiliate-manager' ); ?></h2>
				<form method="POST" id="tc-discount-rule-book">
					
					<table class="wp-list-table widefat fixed striped">
						<tbody>
							<tr>
								<td>
									<?php _e( 'Discount Type', 'thoughtcl0ud-affiliate-manager' ); ?>
								</td>
								<td>
									<select name="_tc_discount_type">
										<option value="fixed-price" <?php selected( $_tc_discount_type, 'fixed-price' ); ?>>
											<?php _e( 'Fixed Price', 'thoughtcl0ud-affiliate-manager' ); ?>
										</option>
										<option value="precentage" <?php selected( $_tc_discount_type, 'precentage' ); ?>>
											<?php _e( 'Precentage', 'thoughtcl0ud-affiliate-manager' ); ?>
										</option>
									</select>
								</td>
							</tr>
						</tbody>
					</table>

					<br/>
					
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th>
									<?php _e( 'Cart Total', 'thoughtcl0ud-affiliate-manager' ); ?>
								</th>
								<th>
									<?php _e( 'Discount', 'thoughtcl0ud-affiliate-manager' ); ?>
								</th>
								<th>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if( !empty( $discount_rule_book ) && is_array( $discount_rule_book ) ) {
								foreach ($discount_rule_book as $key => $value) {
									?>
									<tr>
										<td>
											<input type="text" name="_tc_discount_rule[cart_total][]" value="<?php echo $key; ?>" />
										</td>
										<td>
											<input type="text" name="_tc_discount_rule[discount][]" value="<?php echo $value; ?>" />
										</td>
										<td>
											<span class="del">x</span>
										</td>
									</tr>
									<?php
								}
							}
							else {
								?>
								<tr>
									<td>
										<input type="text" name="_tc_discount_rule[cart_total][]" />
									</td>
									<td>
										<input type="text" name="_tc_discount_rule[discount][]" />
									</td>
									<td>
										<span class="del">x</span>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<p class="add-new-rule">
						<input class="button" type="button" value="<?php _e( 'Add Rule +', 'thoughtcl0ud-affiliate-manager' ); ?>" />
					</p>
					<?php wp_nonce_field( 'thoughtcl0ud-affiliate-manager-action', 'thoughtcl0ud-affiliate-manager-nonce' ); ?>
					<p>
						<input class="button-primary" type="submit" value="<?php _e( 'Save discount rules', 'thoughtcl0ud-affiliate-manager' ); ?>" name="_tc_discount_rule_submit" />
					</p>	
				</form>
			</div>	
			<?php
		}

		/**
		* Managing coupon amount in order table frontend.
		*/
		public function enqueue_discount_rule_panel_scripts_n_styles() {
			wp_enqueue_style(
				'discount-rule-panel-css',
				Thoughtcloud_Affiliate_Manager_PLUGIN_DIR_URL . 'assets/discount-rule-panel.css',
				array(),
				time(),
				'all'
			);
			wp_enqueue_script(
				'discount-rule-panel-js',
				Thoughtcloud_Affiliate_Manager_PLUGIN_DIR_URL . 'assets/discount-rule-panel.js',
				array( 'jquery', 'jquery-ui-sortable' ),
				time(),
				true
			);
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			if ( !wp_script_is( 'jquery-ui-sortable', 'enqueued' ) ) {
				wp_enqueue_script( 'jquery-ui-sortable' );
			}

		}

	

	}
}

/**
 * Main instance of Thoughtcloud_Discount_Rule_Panel.
 *
 * @return Thoughtcloud_Discount_Rule_Panel
 */
Thoughtcloud_Discount_Rule_Panel::instance();