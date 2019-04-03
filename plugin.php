<?php
/**
 * Plugin Name: Yoast to REST API
 * Description: Adds Yoast fields to page and post metadata to WP REST API responses. Version for composer.
 * Author: Lopatin Daniil
 * Author URI: https://github.com/mupic
 * Version: 1.1.0
 * Plugin URI: https://github.com/mupic/yoast-to-rest-api
 */

//GET params will take precedence over constants
if(!defined('YOAST_REST_META')) //false - Disable automatic meta seo input
	define('YOAST_REST_META', true);
if(!defined('YOAST_REST_OG')) //false - Disable automatic open graph input
	define('YOAST_REST_OG', false);
if(!defined('YOAST_REST_TW')) //false - Disable automatic meta twitter input
	define('YOAST_REST_TW', false);

class Yoast_To_REST_API {

	protected $keys = array(
		'yoast_wpseo_focuskw',
		'yoast_wpseo_title',
		'yoast_wpseo_metadesc',
		'yoast_wpseo_linkdex',
		'yoast_wpseo_metakeywords',
		'yoast_wpseo_meta-robots-noindex',
		'yoast_wpseo_meta-robots-nofollow',
		'yoast_wpseo_meta-robots-adv',
		'yoast_wpseo_canonical',
		'yoast_wpseo_redirect',
		'yoast_wpseo_opengraph-title',
		'yoast_wpseo_opengraph-description',
		'yoast_wpseo_opengraph-image',
		'yoast_wpseo_twitter-title',
		'yoast_wpseo_twitter-description',
		'yoast_wpseo_twitter-image'
	);

	function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_yoast_data' ) );
	}

	function add_yoast_data() {
		// Posts
		register_rest_field( 'post',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
				'update_callback' => array( $this, 'wp_api_update_yoast' ),
				'schema'          => null,
			)
		);

		// Pages
		register_rest_field( 'page',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
				'update_callback' => array( $this, 'wp_api_update_yoast' ),
				'schema'          => null,
			)
		);

		// Category
		register_rest_field( 'category',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast_tax' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Tag
		register_rest_field( 'tag',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast_tax' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		$taxonomies = get_taxonomies(array(
			'show_in_rest' => true,
			'_builtin' => false
		));

		foreach( $taxonomies as $taxonomy ) {
			register_rest_field( $taxonomy,
				'yoast_meta',
				array(
					'get_callback'    => array( $this, 'wp_api_encode_yoast_tax' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);
		}

		// Public custom post types
		$types = get_post_types( array(
			'public'   => true,
			'_builtin' => false
		) );

		foreach ( $types as $type ) {
			register_rest_field( $type,
				'yoast_meta',
				array(
					'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
					'update_callback' => array( $this, 'wp_api_update_yoast' ),
					'schema'          => null,
				)
			);
		}


		/* register route <*/

		/*register_rest_route( 'yoast_api/v1', '/home', array(
			'methods'  => 'GET',
			'callback' => 'myplug_get_post_items',
			'args' => array(
				'id' => array(
					'default' => false,
					'validate_callback' => function($param, $request, $key) {
						return is_numeric( $param );
					},
				),
				'post_type' => array(
					'default' => false,
				),
			),
		) );*/

		/*> register route */
	}

	/**
	 * Updates post meta with values from post/put request.
	 *
	 * @param array $value
	 * @param object $data
	 * @param string $field_name
	 *
	 * @return array
	 */
	function wp_api_update_yoast( $value, $data, $field_name ) {

		foreach ( $value as $k => $v ) {

			if ( in_array( $k, $this->keys ) ) {
				! empty( $k ) ? update_post_meta( $data->ID, '_' . $k, $v ) : null;
			}
		}

		return $this->wp_api_encode_yoast( $data->ID, null, null );
	}

	function wp_api_encode_yoast( $p, $field_name, $request ) {
		$yoast_meta = $request->get_param('yoast_meta');
		$allow_seo = $yoast_meta !== '' && $yoast_meta !== null? $yoast_meta : YOAST_REST_META;

		if($allow_seo == false)
			return false;

		$wpseo_frontend = WPSEO_Frontend_To_REST_API::get_instance();
		$wpseo_frontend->reset();

		query_posts( array(
			'p'         => $p['id'], // ID of a page, post, or custom type
			'post_type' => 'any',
			'no_found_rows' => true,
		) );

		the_post();

		/* big crutch for yoast */
		global $post, $wp_the_query;
		$wp_the_query->queried_object = $post;
		$wp_the_query->is_singular = true;
		if($p['type'] == 'post')
			$wp_the_query->is_single = true;
		if($p['type'] == 'page')
			$wp_the_query->is_page = true;

		$wpseo_og = new WPSEO_OpenGraph_Twitter_To_REST_API();

		$yoast_meta = array(
			'yoast_wpseo_title'     => $wpseo_frontend->get_content_title(),
			'yoast_wpseo_metadesc'  => $wpseo_frontend->metadesc( false ),
			'yoast_wpseo_canonical' => $wpseo_frontend->canonical( false ),
		);

		$og = $wpseo_og->get_meta_data($request);
		$yoast_meta = array_merge($yoast_meta, $og);

		/**
		 * Filter the returned yoast meta.
		 *
		 * @param array $yoast_meta Array of metadata to return from Yoast.
		 * @param \WP_Post $p The current post object.
		 * @param \WP_REST_Request $request The REST request.
		 * @return array $yoast_meta Filtered meta array.
		 */
		$yoast_meta = apply_filters( 'wpseo_to_api_yoast_meta', $yoast_meta, $p, $request );

		return (array) $yoast_meta;
	}

	function wp_api_encode_yoast_tax( $tax, $field_name, $request ) {
		$yoast_meta = $request->get_param('yoast_meta');
		$allow_seo = $yoast_meta !== '' && $yoast_meta !== null? $yoast_meta : YOAST_REST_META;

		if($allow_seo == false)
			return false;

		/* big crutch for yoast */
		global $wp_the_query;
		$wp_the_query->queried_object = get_term( $tax['id'], $tax['taxonomy'] );
		$wp_the_query->is_tax = true;
		$wp_the_query->tax_query->queried_terms[ $tax['taxonomy'] ]['terms'] = false;

		$res = $this->wp_api_encode_taxonomy($request);

		return $res;
	}

	private function wp_api_encode_taxonomy($request) {
		$wpseo_frontend = WPSEO_Frontend_To_REST_API::get_instance();
		$wpseo_frontend->reset();

		$wpseo_og = new WPSEO_OpenGraph_Twitter_To_REST_API();

		$yoast_meta = array(
			'yoast_wpseo_title'    => $wpseo_frontend->get_taxonomy_title(),
			'yoast_wpseo_metadesc' => $wpseo_frontend->metadesc( false ),
		);

		$og = $wpseo_og->get_meta_data($request);
		$yoast_meta = array_merge($yoast_meta, $og);

		/**
		 * Filter the returned yoast meta for a taxonomy.
		 *
		 * @param array $yoast_meta Array of metadata to return from Yoast.
		 * @return array $yoast_meta Filtered meta array.
		 */
		$yoast_meta = apply_filters( 'wpseo_to_api_yoast_taxonomy_meta', $yoast_meta );

		return (array) $yoast_meta;
	}
}

if ( class_exists( 'WPSEO_Frontend' ) ) {
	include __DIR__ . '/classes/class-wpseo-frontend-to-rest-api.php';
	include __DIR__ . '/classes/class-wpseo-opengraph-twitter-to-rest-api.php';

	new Yoast_To_REST_API();
} else {
	add_action( 'admin_notices', 'wpseo_not_loaded' );
}

function wpseo_not_loaded() {
	printf(
		'<div class="error"><p>%s</p></div>',
		__( '<b>Yoast to REST API</b> plugin not working because <b>Yoast SEO</b> plugin is not active.' )
	);
}
