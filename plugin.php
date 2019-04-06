<?php
/**
 * Plugin Name: Yoast to REST API
 * Description: Adds Yoast fields to page and post metadata to WP REST API responses. Version for composer.
 * Author: Lopatin Daniil
 * Author URI: https://github.com/mupic
 * Version: 2.0.0
 * Plugin URI: https://github.com/mupic/yoast-to-rest-api
 */

//GET params will take precedence over constants
if(!defined('YOAST_REST_META')) //false - Disable automatic meta seo input
	define('YOAST_REST_META', false);
if(!defined('YOAST_REST_OG')) //false - Disable automatic open graph input
	define('YOAST_REST_OG', false);
if(!defined('YOAST_REST_TW')) //false - Disable automatic meta twitter input
	define('YOAST_REST_TW', false);
if(!defined('YOAST_REST_BC')) //true - Return json breadcrumbs. "html" - Return html generated breadcrumbs.
	define('YOAST_REST_BC', false);
if(!defined('YOAST_REST_SCHEMA')) //false - Disable automatic microdata input.
	define('YOAST_REST_SCHEMA', false);

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

	protected $allow = array();

	function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_yoast_data' ) );
	}

	private function classes_init(){
		$this->wpseo_frontend = WPSEO_Frontend_To_REST_API::get_instance();
		$this->wpseo_og = WPSEO_OpenGraph_Twitter_To_REST_API::get_instance();
		$this->schema_rest = new WPSEO_Schema_To_REST_API();
	}

	private function allowed(){
		$this->allow['meta'] = isset($_GET['yoast_meta']) && ($this->is_bool($_GET['yoast_meta']) || $_GET['yoast_meta'])? $this->parse_bool($_GET['yoast_meta']) : YOAST_REST_META;
		$this->allow['opengraph'] = isset($_GET['opengraph']) && ($this->is_bool($_GET['opengraph']) || $_GET['opengraph'])? $this->parse_bool($_GET['opengraph']) : YOAST_REST_META;
		$this->allow['twitter'] = isset($_GET['twitter']) && ($this->is_bool($_GET['twitter']) || $_GET['twitter'])? $this->parse_bool($_GET['twitter']) : YOAST_REST_META;
		$this->allow['breadcrumbs'] = isset($_GET['breadcrumbs'])? ($this->is_bool($_GET['breadcrumbs'])? $this->parse_bool($_GET['breadcrumbs']) : ($_GET['breadcrumbs']? $_GET['breadcrumbs'] : YOAST_REST_META)) : YOAST_REST_META;
		$this->allow['schema'] = isset($_GET['schema']) && ($this->is_bool($_GET['schema']) || $_GET['schema'])? $this->parse_bool($_GET['schema']) : YOAST_REST_META;
	}

	function add_yoast_data() {
		$this->allowed();
		$this->classes_init();

		// Posts
		register_rest_field( 'post',
			'yoast',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
				'update_callback' => array( $this, 'wp_api_update_yoast' ),
				'schema'          => null,
			)
		);

		// Pages
		register_rest_field( 'page',
			'yoast',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
				'update_callback' => array( $this, 'wp_api_update_yoast' ),
				'schema'          => null,
			)
		);

		// Category
		register_rest_field( 'category',
			'yoast',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast_tax' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Tag
		register_rest_field( 'tag',
			'yoast',
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
				'yoast',
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
				'yoast',
				array(
					'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
					'update_callback' => array( $this, 'wp_api_update_yoast' ),
					'schema'          => null,
				)
			);
		}


		/* register route <*/

		register_rest_route( 'yoast_api/v1', '/home', array(
			'methods'  => 'GET',
			'callback' => array($this, 'wp_api_encode_yoast_home'),
		) );

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

	function wp_api_encode_yoast_home( $request ) {

		$this->wpseo_frontend->reset();

		/* big crutch for yoast */
		global $post, $wp_the_query;
		$ID = get_option( 'page_on_front' );
		if($ID){

			query_posts( array(
				'p'         => $ID, // ID of a page, post, or custom type
				'post_type' => 'page',
				'no_found_rows' => true,
			) );

			the_post();

			$wp_the_query->queried_object = $post;
			$wp_the_query->is_page = true;
		}else{
			// $wp_the_query->queried_object = (object) array();
			$wp_the_query->is_home = true;
		}
		wp_reset_query();

		$yoast_meta = array(
			'title'     => $this->wpseo_frontend->title(''),
			'description'  => $this->wpseo_frontend->metadesc( false ),
			'canonical' => $this->wpseo_frontend->canonical( false ),
		);

		$og = $this->wpseo_og->get_og_data($this->allow);
		$yoast_meta = array_merge($yoast_meta, $og);

		/**
		 * Filter the returned yoast meta.
		 *
		 * @param array $yoast_meta Array of metadata to return from Yoast.
		 * @param \WP_REST_Request $request The REST request.
		 */
		$yoast_meta = apply_filters( 'wpseo_to_api_yoast_home_meta', $yoast_meta, $request );

		$breadcrumbs = $this->wp_api_get_breadcrumbs(null, $request, 'home');

		$schema = $this->wp_api_get_schema(null, $request, 'home');

		$result = array();
		$result['meta'] = $yoast_meta;
		if($breadcrumbs)
			$result['breadcrumbs'] = $breadcrumbs;
		if($schema)
			$result['schema'] = $schema;

		return $result;
	}

	function wp_api_encode_yoast( $p, $field_name, $request ) {

		if(!$this->allow['meta'])
			return false;

		$this->wpseo_frontend->reset();

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
		wp_reset_query();


		$yoast_meta = array(
			'title'     => $this->wpseo_frontend->title(''),
			'description'  => $this->wpseo_frontend->metadesc( false ),
			'canonical' => $this->wpseo_frontend->canonical( false ),
		);

		$og = $this->wpseo_og->get_og_data($this->allow);
		$yoast_meta = array_merge($yoast_meta, $og);

		/**
		 * Filter the returned yoast meta.
		 *
		 * @param array $yoast_meta Array of metadata to return from Yoast.
		 * @param \WP_Post $p The current post object.
		 * @param \WP_REST_Request $request The REST request.
		 */
		$yoast_meta = apply_filters( 'wpseo_to_api_yoast_meta', $yoast_meta, $p, $request );

		$breadcrumbs = $this->wp_api_get_breadcrumbs($p, $request, 'post');

		$schema = $this->wp_api_get_schema($p, $request, 'post');

		$result = array();
		$result['meta'] = $yoast_meta;
		if($breadcrumbs)
			$result['breadcrumbs'] = $breadcrumbs;
		if($schema)
			$result['schema'] = $schema;

		return $result;
	}

	function wp_api_encode_yoast_tax( $tax, $field_name, $request ) {

		if(!$this->allow['meta'])
			return false;

		/* big crutch for yoast */
		global $wp_the_query;
		$wp_the_query->queried_object = get_term( $tax['id'], $tax['taxonomy'] );
		$wp_the_query->is_tax = true;
		$wp_the_query->tax_query->queried_terms[ $tax['taxonomy'] ]['terms'] = false;
		wp_reset_query();

		$res = $this->wp_api_encode_taxonomy($tax, $request);

		return $res;
	}

	private function wp_api_encode_taxonomy($tax, $request) {
		$this->wpseo_frontend->reset();

		$yoast_meta = array(
			'title'    => $this->wpseo_frontend->title(''),
			'description' => $this->wpseo_frontend->metadesc( false ),
		);

		$og = $this->wpseo_og->get_og_data($this->allow);
		$yoast_meta = array_merge($yoast_meta, $og);

		/**
		 * Filter the returned yoast meta for a taxonomy.
		 *
		 * @param array $yoast_meta Array of metadata to return from Yoast.
		 * @return array $yoast_meta Filtered meta array.
		 */
		$yoast_meta = apply_filters( 'wpseo_to_api_yoast_taxonomy_meta', $yoast_meta );

		$breadcrumbs = $this->wp_api_get_breadcrumbs($tax, $request, 'tax');

		$schema = $this->wp_api_get_schema($tax, $request, 'tax');

		$result = array();
		$result['meta'] = $yoast_meta;
		if($breadcrumbs)
			$result['breadcrumbs'] = $breadcrumbs;
		if($schema)
			$result['schema'] = $schema;

		return $result;
	}

	private function wp_api_get_breadcrumbs($object = null, $request, $type) {

		if(!$this->allow['breadcrumbs'])
			return false;

		$breadcrumbs = array();
		if($this->allow['breadcrumbs'] === 'html'){

			$breadcrumbs['html'] = WPSEO_Breadcrumbs::breadcrumb( '', '', false );

			/**
			 * Filter the returned breadcrumbs html.
			 *
			 * @param string $breadcrumbs Generated breadcrumbs.
			 * @param \WP_{object} $object The current post object or taxonomy.
			 * @param \WP_REST_Request $request The REST request.
			 */
			if($type == 'post'){
				$breadcrumbs['html'] = apply_filters( 'wpseo_to_api_yoast_breadcrumbs_html', $breadcrumbs['html'], $object, $request );
			}elseif($type == 'tax'){
				$breadcrumbs['html'] = apply_filters( 'wpseo_to_api_yoast_taxonomy_breadcrumbs_html', $breadcrumbs['html'], $object, $request );
			}elseif($type == 'home'){
				$breadcrumbs['html'] = apply_filters( 'wpseo_to_api_yoast_home_breadcrumbs_html', $breadcrumbs['html'], $request );
			}

		}else{

			$WPSEO_Breadcrumbs = WPSEO_Breadcrumbs::get_instance();
			$breadcrumbs['links'] = $WPSEO_Breadcrumbs->get_links();
			$breadcrumbs['separator'] = apply_filters( 'wpseo_breadcrumb_separator', WPSEO_Options::get( 'breadcrumbs-sep' ) );

			/**
			 * Filter the returned breadcrumbs array.
			 *
			 * @param array $breadcrumbs Array of metadata to return from Yoast.
			 * @param \WP_{object} $object The current post object or taxonomy.
			 * @param \WP_REST_Request $request The REST request.
			 */
			if($type == 'post'){
				$breadcrumbs['links'] = apply_filters( 'wpseo_to_api_yoast_breadcrumbs', $breadcrumbs['links'], $object, $request );
			}elseif($type == 'tax'){
				$breadcrumbs['links'] = apply_filters( 'wpseo_to_api_yoast_taxonomy_breadcrumbs', $breadcrumbs['links'], $object, $request );
			}elseif($type == 'home'){
				$breadcrumbs['links'] = apply_filters( 'wpseo_to_api_yoast_home_breadcrumbs', $breadcrumbs['links'], $request );
			}

		}

		return $breadcrumbs;
	}

	private function wp_api_get_schema($object = null, $request, $type) {

		if(!$this->allow['schema'])
			return false;

		$schema = $this->schema_rest->get_schema_for_current_query();

		/**
		 * Filter the returned microdata json string.
		 *
		 * @param string $schema String of json microdata.
		 * @param \WP_Post $p The current post object.
		 * @param \WP_REST_Request $request The REST request.
		 */
		if($type == 'post'){
			$schema = apply_filters( 'wpseo_to_api_yoast_schema', $schema, $object, $request );
		}elseif($type == 'tax'){
			$schema = apply_filters( 'wpseo_to_api_yoast_taxonomy_schema', $schema, $object, $request );
		}elseif($type == 'home'){
			$schema = apply_filters( 'wpseo_to_api_yoast_home_schema', $schema, $request );
		}

		return $schema;
	}

	function is_bool($var){
		if(is_bool($var))
			return true;
		if($var === 'false')
			return true;
		if($var === 'true')
			return true;

		return false;
	}

	function parse_bool($str){
		if(is_bool($str))
			return $str;
		if($str === 'false')
			return false;
		if($str === 'true')
			return true;

		return !!$str;
	}
}

if (
	   class_exists( 'WPSEO_Frontend' )
	&& class_exists( 'WPSEO_OpenGraph' )
	&& class_exists( 'WPSEO_Twitter' )
	&& class_exists( 'WPSEO_Breadcrumbs' )
	&& class_exists( 'WPSEO_JSON_LD' )
) {
	include __DIR__ . '/classes/class-wpseo-frontend-to-rest-api.php';
	include __DIR__ . '/classes/class-wpseo-opengraph-twitter-to-rest-api.php';
	include __DIR__ . '/classes/class-wpseo-schema-to-rest-api.php';

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
