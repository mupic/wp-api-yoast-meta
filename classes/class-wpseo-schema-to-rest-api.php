<?php

class WPSEO_Schema_To_REST_API extends WPSEO_Schema{

	function parse_scripts_json($str){
		$regex = '~<script.*type=[\'"]application/ld\+json[\'"].*>([^<]*)</script>~i';
		if(!preg_match_all($regex, $str, $matches))
			return false;

		return $matches[1][0];
	}

	function get_schema_for_current_query(){
		ob_start();
		$this->generate();
		$scripts = ob_get_clean();

		return $this->parse_scripts_json($scripts);
	}
}