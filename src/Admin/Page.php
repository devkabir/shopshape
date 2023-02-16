<?php


namespace ShopShape\Admin;

/* It prevents direct access to the file. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DOMDocument;

/**
 * Class Page
 *
 * @package ShopShape\Admin
 */
final class Page {



	/**
	 * It creates a form that submits to the options.php file, which is a WordPress core file that handles saving options
	 */
	public static function render() {
		?>
		<div class="wrap">
			<h1>
		<?php esc_html_e( 'ShopShape', 'shopshape' ); ?>
			</h1>
			<form method="post" action="options.php">
		<?php
		settings_fields( 'shopshape' );
		do_settings_sections( 'shopshape' );
		submit_button();
		?>
			</form>
		</div>
		<?php
	}


	/**
	 * It generates an HTML input element
	 *
	 * @param array $config an array of parameters that are used to generate the input.
	 */
	public static function generate_input( array $config ) {
		$doc = new DOMDocument();

		$input = $doc->createElement( 'input' );
		$type  = $config['type'];
		$name  = $config['name'];
		$value = $config['value'];

		$input->setAttribute( 'type', $type );
		$input->setAttribute( 'class', 'widefat' );
		$input->setAttribute( 'name', $name );
		$input->setAttribute( 'value', esc_attr( $value ) );

		if ( 'checkbox' === $type ) {
			$checked = $config['user_input'];
			if ( $checked ) {
				$input->setAttribute( 'checked', 'checked' );
			}
		}
		if ( in_array( $type, array( 'text', 'number' ), true ) ) {
			$placeholder = $config['placeholder'];
			if ( ! empty( $placeholder ) ) {
				$input->setAttribute( 'placeholder', $placeholder );
			}
		}
		$doc->appendChild( $input );

		$field        = $doc->saveHTML();
		$allowed_html = array(
			'input' => array(
				'type'        => array(),
				'class'       => array(),
				'name'        => array(),
				'value'       => array(),
				'checked'     => array(),
				'placeholder' => array(),
			),
		);
		echo wp_kses( $field, $allowed_html );
	}
}
