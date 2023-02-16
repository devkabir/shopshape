<?php


namespace ShopShape\Admin;

/* It prevents direct access to the file. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ShopShape\Traits\Singleton;

/**
 * Class Settings
 *
 * @package ShopShape\Admin
 */
final class Settings {

	use Singleton;

	/**
	 * It adds a submenu page to the WooCommerce menu.
	 */
	public static function add_settings_page() {
		add_submenu_page(
			'woocommerce',
			__( 'ShopShape', 'shopshape' ),
			__( 'Cart Customizations', 'shopshape' ),
			'manage_options',
			'shopshape',
			array( Page::class, 'render' )
		);
	}

	/**
	 * It sanitizes the inputs
	 *
	 * @param array inputs The array of inputs to sanitize.
	 */
	public static function sanitize( array $inputs ): array {
		foreach ( $inputs as $key ) {
			foreach ( $inputs[ $key ] as $field => $input ) {
				if ( in_array( $field, array( 'add_content_empty_cart' ), true ) ) {
					$inputs[ $key ][ $field ] = sanitize_textarea_field( $input );
				} else {
					$inputs[ $key ][ $field ] = is_numeric( $input ) && $input > 0 ? $input : 0;
				}
			}
		}
		return $inputs;
	}
	/**
	 * It creates a settings page for the plugin
	 */
	public static function register_settings() {
		$options = get_option( 'shopshape_options' );
		register_setting(
			'shopshape',
			'shopshape_options',
			array(
				'sanitize_callback' => array(
					self::class,
					'sanitize',
				),
			)
		);
		$fields = array(
			'cart_page' =>
				array(
					'title'  => 'Cart Page Customizations',
					'fields' => array(
						'Show Stock'                       => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][show_stock]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['show_stock'] ),
						),
						'Show SKU'                         => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][show_sku]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['show_sku'] ),
						),
						'Show Category'                    => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][show_category]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['show_category'] ),
						),
						'Show Total Weight'                => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][show_total_weight]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['show_total_weight'] ),
						),
						'Show Total Discount'              => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][show_total_discount]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['show_total_discount'] ),
						),
						'Hide coupon form'                 => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][hide_coupon_form]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['hide_coupon_form'] ),
						),
						'Hide coupon code'                 => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][hide_coupon_code]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['hide_coupon_code'] ),
						),
						'Hide shipping destination'        => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][hide_destination]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['hide_destination'] ),
						),
						'Hide state at shipping calculator' => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][hide_calculator_state]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['hide_calculator_state'] ),
						),
						'Hide city at shipping calculator' => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][hide_calculator_city]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['hide_calculator_city'] ),
						),
						'Hide postcode at shipping calculator' => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][hide_calculator_postcode]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['hide_calculator_postcode'] ),
						),

						'Hide Product Links'               => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][hide_product_links]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['hide_product_links'] ),
						),
						'Remove Product'                   => array(
							'type'        => 'number',
							'name'        => 'shopshape_options[cart_page][remove_product]',
							'value'       => $options['cart_page']['remove_product'] ?? false,
							'placeholder' => 'Product ID',
						),
						'Show text on empty cart'          => array(
							'type'        => 'text',
							'name'        => 'shopshape_options[cart_page][add_content_empty_cart]',
							'value'       => $options['cart_page']['add_content_empty_cart'] ?? false,
							'placeholder' => 'Content as string',
						),
						'Show free shipping notice to engage customer' => array(
							'type'        => 'number',
							'name'        => 'shopshape_options[cart_page][show_free_shipping_notice]',
							'value'       => $options['cart_page']['show_free_shipping_notice'] ?? false,
							'placeholder' => 'Minimum order amount to get free shipping',
						),

						'Cart and Checkout on the Same Page' => array(
							'type'       => 'checkbox',
							'name'       => 'shopshape_options[cart_page][get_checkout_page]',
							'value'      => 1,
							'user_input' => isset( $options['cart_page']['get_checkout_page'] ),
						),

					),
				),
		);
		foreach ( $fields as $page => $inputs ) {
			$section = 'shopshape_section_' . $page;
			add_settings_section(
				$section,
				$inputs['title'],
				null,
				'shopshape'
			);

			foreach ( $inputs['fields'] as $title => $input ) {
				add_settings_field(
					'shopshape_field_' . str_replace( ' ', '_', strtolower( $title ) ),
					$title,
					function () use ( $input ) {
						Page::generate_input( $input );
					},
					'shopshape',
					$section
				);
			}
		}
	}

	/**
	 * It adds a new menu item to the admin menu, and then registers the settings for the plugin
	 */
	public static function init() {
		add_action( 'admin_menu', array( self::class, 'add_settings_page' ) );
		add_action( 'admin_init', array( self::class, 'register_settings' ) );
	}
}
