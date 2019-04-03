<?php
/**
 * @package WPSEO\OpenGraph
 */

class WPSEO_OpenGraph_Twitter_To_REST_API extends WPSEO_OpenGraph{

	function my_array_combine($keys, $values){
		$result = array();
		foreach ($keys as $i => $k) {
			$result[$k][] = $values[$i];
		}
		array_walk($result, function(&$v){
			$v = (count($v) == 1)? array_pop($v): $v;
		});
		return $result;
	}

	function get_meta_tags($str){
		$pattern = '
			~<\s*meta\s

			# using lookahead to capture type to $1
			(?=[^>]*?
			\b(?:name|property|http-equiv)\s*=\s*
			(?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
			([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
			)

			# capture content to $2
			[^>]*?\bcontent\s*=\s*
			(?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
			([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
			[^>]*>

			~ix';

		if(!preg_match_all($pattern, $str, $out))
			return array();

		return $this->my_array_combine($out[1], $out[2]);
	}

	public function get_meta_data($request = null){
		$opengraph = is_object($request) && get_class($request) == 'WP_REST_Request'? $request->get_param('yoast_opengraph') : null;
		$twitter = is_object($request) && get_class($request) == 'WP_REST_Request'? $request->get_param('yoast_twitter') : null;
		$opengraph = $opengraph == null? YOAST_REST_OG : $opengraph;
		$twitter = $twitter == null? YOAST_REST_TW : $twitter;

		ob_start();

		if ( $opengraph && WPSEO_Options::get( 'opengraph' ) === true ) {
			$this->opengraph();
		}

		if ( $twitter && WPSEO_Options::get( 'twitter' ) === true ) {
			new WPSEO_Twitter();
		}

		$meta_tags = ob_get_clean();

		return $this->get_meta_tags($meta_tags);
	}
}