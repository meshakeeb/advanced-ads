<?php

/**
 * Class handling the ad positioning and migrating values from previous solutions.
 */
class Advanced_Ads_Ad_Positioning {
	/**
	 * The instance of the current ad.
	 *
	 * @var Advanced_Ads_Ad
	 */
	private $ad;

	/**
	 * The structure of these output options.
	 *
	 * @var array
	 */
	private $positioning = array(
		'position' => 'none',
		'clearfix' => false,
		'margin'   => array(
			'top'    => 0,
			'left'   => 0,
			'bottom' => 0,
			'right'  => 0,
		),
	);

	/**
	 * Class constructor.
	 *
	 * @param Advanced_Ads_Ad $ad The current ad object.
	 */
	public function __construct( Advanced_Ads_Ad $ad ) {
		$this->ad = $ad;
		$this->migrate_values();
		$this->filter_values();
	}

	/**
	 * Migrate option from a previous solution where floating was an additional setting.
	 *
	 * @return void
	 */
	private function migrate_values() {
		$this->positioning['margin'] = array_merge(
			$this->positioning['margin'],
			array_map( 'intval', $this->ad->options( 'output.margin', array() ) )
		);

		$this->positioning['position'] = $this->ad->options( 'output.position' );
		// instead of having an empty value, set an explicit default.
		if ( empty( $this->positioning['position'] ) ) {
			$this->positioning['position'] = 'none';
			$this->positioning['clearfix'] = false;
		}

		// left, center, right are the old values, if it's none of these we've already migrated.
		if ( ! in_array( $this->positioning['position'], array( 'left', 'center', 'right' ), true ) ) {
			return;
		}

		$this->positioning['clearfix'] = $this->ad->options( 'output.clearfix' );
		$this->positioning['position'] .= $this->positioning['clearfix'] ? '_nofloat' : '_float';
	}

	/**
	 * Filter the option value for Advanced_Ads_Ad.
	 * This ensures we don't have to update the whole positioning process but can change only the wp-admin side of things.
	 *
	 * @return void
	 */
	private function filter_values() {
		foreach ( $this->positioning as $key => $value ) {
			add_filter( "advanced-ads-ad-option-output.{$key}", function() use ( $value ) {
				return $value;
			} );

			if ( is_array( $value ) ) {
				foreach ( $value as $sub_key => $sub_value ) {
					add_filter( "advanced-ads-ad-option-output.{$sub_key}", function() use ( $sub_value ) {
						return $sub_value;
					} );
				}
			}
		}
	}

	/**
	 * Set up the positioning options with title, description and icon.
	 *
	 * @return array
	 */
	private function setup_positioning_options() {
		return array(
			'default' => array(
				'title'       => __( "Theme’s Default", 'advanced-ads' ),
				'description' => __( 'The ad will behave as predefined by the theme.', 'advanced-ads' ),
				'options'     => array(
					'none' => array(),
				),
			),
			'float'   => array(
				'title'       => _x( 'Float', 'Layout options "Text Flow" heading', 'advanced-ads' ),
				'description' => __( 'Text will wrap around the ad and its margin.', 'advanced-ads' ),
				'options'     => array(
					'left_float'  => array(),
					'right_float' => array(),
				),
			),
			'block'   => array(
				'title'       => _x( 'Block', 'Layout options "Text Flow" heading', 'advanced-ads' ),
				'description' => __( 'Text will continue after the ad and its margin.', 'advanced-ads' ),
				'options'     => array(
					'left_nofloat'   => array(
						'img' => 'block-lr',
					),
					'center_nofloat' => array(
						'img' => 'block-cntr',
					),
					'right_nofloat'  => array(
						'img' => 'block-lr',
					),
				),
			),
		);
	}

	/**
	 * Concatenate the templates and prepare inline styles and scripts.
	 *
	 * @return string
	 */
	public function return_admin_view() {
		return $this->positioning_admin_view() . $this->spacing_admin_view();
	}

	/**
	 * Include the positioning view.
	 *
	 * @return string
	 */
	private function positioning_admin_view() {
		$positioning         = $this->positioning['position'];
		$positioning_options = $this->setup_positioning_options();

		ob_start();
		include_once __DIR__ . '/../views/ad-positioning.php';

		return ob_get_clean();
	}

	/**
	 * Include the spacing/margin view.
	 *
	 * @return string
	 */
	private function spacing_admin_view() {
		$is_centered = explode( '_', $this->positioning['position'] )[0] === 'center';
		$spacings    = array(
			'top'    => array(
				'label' => _x( 'Top', 'Ad positioning spacing label', 'advanced-ads' ),
			),
			'right'  => array(
				'label' => _x( 'Right', 'Ad positioning spacing label', 'advanced-ads' ),
			),
			'bottom' => array(
				'label' => _x( 'Bottom', 'Ad positioning spacing label', 'advanced-ads' ),
			),
			'left'   => array(
				'label' => _x( 'Left', 'Ad positioning spacing label', 'advanced-ads' ),
			),
		);
		foreach ( $spacings as $direction => $item ) {
			$spacings[ $direction ]['value'] = (int) $this->positioning['margin'][ $direction ];
		}

		ob_start();
		include_once __DIR__ . '/../views/ad-spacing.php';

		return ob_get_clean();
	}
}
