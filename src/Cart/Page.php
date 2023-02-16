<?php


namespace ShopShape\Cart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ShopShape\Traits\Singleton;


/**
 * Class Page
 *
 * @package ShopShape\Cart
 */
final class Page {
	use Singleton;


	/**
	 * It's a private property that will hold the options for the cart page.
	 *
	 * @var mixed
	 */
	private $options;


	/**
	 * Page constructor.
	 */
	protected function __construct() {
		$this->options = get_option( 'shopshape_options' );
		if ( ! empty( $this->options ) && array_key_exists( 'cart_page', $this->options ) ) {
			$this->load_hooks( $this->options['cart_page'] );

		}

	}

	/**
	 * It loads the hooks for the cart page
	 *
	 * @param array $cart_page This is the array of options that we're passing to the function.
	 *
	 * @return void
	 */
	private function load_hooks( array $cart_page ) {

		$this->show_hooks( $cart_page );

		$this->hide_hooks( $cart_page );

		if ( isset( $cart_page['remove_product'] ) && 0 !== $cart_page['remove_product'] ) {
			add_action(
				'template_redirect',
				function () use ( $cart_page ) {
					$this->remove_product( $cart_page['remove_product'] );
				}
			);
		}

		if ( isset( $cart_page['get_checkout_page'] ) ) {
			add_action(
				'woocommerce_before_checkout_form',
				function () {
					echo do_shortcode( '[woocommerce_cart]' );
					update_option( 'woocommerce_cart_page_id', null );
				},
				11
			);
			add_filter(
				'woocommerce_get_cart_url',
				function () {
					return wc_get_page_permalink( 'shop' );
				}
			);
		}

		if ( isset( $cart_page['add_content_empty_cart'] ) ) {
			add_action(
				'woocommerce_cart_is_empty',
				function () use ( $cart_page ) {
					$notice = esc_html( $cart_page['add_content_empty_cart'] );
					$this->add_notice( $notice );
				}
			);
		}

	}

	/**
	 * It checks if the user has selected the "Show ..." option in the plugin's settings page, and if so,
	 * It will load show hooks on user demand.
	 *
	 * @param array $cart_page This is the array of settings for the cart page.
	 *
	 * @return void
	 */
	private function show_hooks( array $cart_page ) {
		if ( isset( $cart_page['show_stock'] ) ) {
			add_action( 'woocommerce_after_cart_item_name', array( $this, 'show_stock' ), 9999 );
		}

		if ( isset( $cart_page['show_sku'] ) ) {
			add_filter( 'woocommerce_cart_item_name', array( $this, 'show_sku' ), 9999, 2 );
		}

		if ( isset( $cart_page['show_category'] ) ) {
			add_filter( 'woocommerce_cart_item_name', array( $this, 'show_category' ), 9999, 2 );
		}

		if ( isset( $cart_page['show_total_weight'] ) ) {
			add_action( 'woocommerce_before_cart', array( $this, 'show_total_weight' ) );
		}

		if ( isset( $cart_page['show_total_discount'] ) ) {
			add_action( 'woocommerce_before_cart', array( $this, 'show_total_discount' ), 999999 );
		}
		if ( isset( $cart_page['show_free_shipping_notice'] ) ) {
			add_action(
				'woocommerce_before_cart',
				function () use ( $cart_page ) {
					$this->show_free_shipping( $cart_page['show_free_shipping_notice'] );
				}
			);
		}

	}

	/**
	 * If the current cart total is less than the minimum amount required for free shipping, display a notice with a link to
	 * the shop page.
	 *
	 * @param string $amount The minimum amount required to get free shipping.
	 */
	public function show_free_shipping( string $amount ) {

		$min_amount = $amount;

		$current = WC()->cart->subtotal;

		if ( $min_amount && $current < $min_amount ) {
			$notice = sprintf(
				"Don't let shipping costs get you down - spend %s and enjoy free shipping today!",
				wc_price( $min_amount - $current )
			);

			$this->add_notice( $notice );
		}

	}

	/**
	 * It checks if the user has selected the "Hide ..." option in the plugin's settings page, and if so,
	 * It will load hide hooks on user demand.
	 *
	 * @param array $cart_page This is the array of settings for the cart page.
	 *
	 * @return void
	 */
	private function hide_hooks( array $cart_page ) {
		if ( isset( $cart_page['hide_product_links'] ) ) {
			add_filter( 'woocommerce_cart_item_permalink', '__return_null' );
		}

		if ( isset( $cart_page['hide_destination'] ) ) {
			add_filter(
				'gettext',
				function ( $translated, $untranslated, $domain ) {
					if ( ! is_admin() && 'woocommerce' === $domain ) {
						switch ( $translated ) {
							case 'Shipping to %s.':
								$translated = '';
								break;
						}
					}

					return $translated;
				},
				9999,
				3
			);
		}

		if ( isset( $cart_page['hide_coupon_code'] ) ) {
			add_filter(
				'woocommerce_cart_totals_coupon_label',
				function () {
					return 'Coupon';
				},
				9999,
				2
			);
		}

		if ( isset( $cart_page['hide_coupon_form'] ) ) {
			add_filter(
				'woocommerce_coupons_enabled',
				function () {
					if ( is_cart() ) {
						return false;
					}

					return true;
				}
			);
		}

		if ( isset( $cart_page['hide_calculator_state'] ) ) {
			add_filter( 'woocommerce_shipping_calculator_enable_state', '__return_false' );
		}
		if ( isset( $cart_page['hide_calculator_city'] ) ) {
			add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );
		}

		if ( isset( $cart_page['hide_calculator_postcode'] ) ) {
			add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_false' );
		}
	}

	/**
	 * If the product is in the cart, remove it
	 *
	 * @param string $product_id The ID of the product to remove from the cart.
	 */
	private function remove_product( string $product_id ) {
		if ( ! is_admin() ) {
			$product_cart_id = WC()->cart->generate_cart_id( $product_id );
			$cart_item_key   = WC()->cart->find_product_in_cart( $product_cart_id );
			if ( $cart_item_key ) {
				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}

	}

	/**
	 * If the user is on the cart page, print the notice, otherwise add it to the queue
	 *
	 * @param string notice The notice to add.
	 */
	public function add_notice( string $notice ): void {
		$return_to = wc_get_page_permalink( 'shop' );
		$notice    = sprintf(
			'<a href="%s" class="button wc-forward">%s</a> %s',
			esc_url( $return_to ),
			'Continue Shopping',
			$notice
		);
		if ( is_cart() ) {
			wc_print_notice( $notice, 'notice' );
		} else {
			wc_add_notice( $notice, 'notice' );
		}
	}

	/**
	 * It gets the category IDs of the product, and if there are any, it adds them to the product name in the cart
	 *
	 * @param string $name name of the product.
	 * @param array $cart_item cart items.
	 *
	 * @return string The name of the product.
	 */
	public function show_category( string $name, array $cart_item ): string {
		$product = $cart_item['data'];
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		$cat_ids = $product->get_category_ids();

		if ( $cat_ids ) {
			$name .= '<br/>' . wc_get_product_category_list(
				$product->get_id(),
				', ',
				'<span class="posted_in">' . _n(
					'Category:',
					'Categories:',
					count( $cat_ids ),
					'woocommerce'
				) . ' ',
				'</span>'
			);
		}

		return $name;
	}

	/**
	 * For each item in the cart, if the product is on sale, calculate the discount amount and add it to the total discount
	 * amount
	 */
	public function show_total_discount() {
		$discount_total = 0;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product = $values['data'];
			if ( $product->is_on_sale() ) {
				$regular_price   = $product->get_regular_price();
				$sale_price      = $product->get_sale_price();
				$discount        = ( (float) $regular_price - (float) $sale_price ) * (int) $values['quantity'];
				$discount_total += $discount;
			}
		}
		if ( $discount_total > 0 ) {
			$notice = sprintf( "Congratulations on your %s savings - let's see how much more you can save!", wc_price( $discount_total + WC()->cart->get_discount_total() ) );
			$this->add_notice( $notice );
		}
	}

	/**
	 * If the product is not on backorder, show the stock status
	 *
	 * @param array $cart_item The cart item array.
	 */
	public function show_stock( array $cart_item ) {
		$product = $cart_item['data'];
		if ( ! $product->backorders_require_notification() || ! $product->is_on_backorder( $cart_item['quantity'] ) ) {
			echo wp_kses( wc_get_stock_html( $product ), array( 'p' => array() ) );
		}

	}

	/**
	 * It adds the SKU to the cart item name.
	 *
	 * @param string $item_name The name of the item.
	 * @param array $cart_item The cart item array.
	 *
	 * @return string The item name with the SKU appended to it.
	 */
	public function show_sku( string $item_name, array $cart_item ): string {
		$product = $cart_item['data'];
		$sku     = $product->get_sku();
		if ( ! empty( $sku ) ) {
			$sku_text = '<br/><span>SKU: ' . $sku . '</span>';
		} else {
			$sku_text = '';
		}
		$item_name .= $sku_text;

		return $item_name;
	}

	/**
	 * If the cart page is being displayed, print the notice, otherwise add it to the notices queue
	 */
	public function show_total_weight(): void {
		$notice = sprintf( "Your cart may be %s %s lighter, but there's plenty of room for more items!", WC()->cart->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) );
		$this->add_notice( $notice );
	}

}
