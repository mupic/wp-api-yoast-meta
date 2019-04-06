<?php

class WPSEO_Schema_To_REST_API extends WPSEO_JSON_LD{

	function parse_scripts_json($str){
		$regex = '~<script\s+type=[\'"]application/ld\+json[\'"]>([^<]*)</script>~i';
		if(!preg_match_all($regex, $str, $matches))
			return false;

		$array = array();
		foreach ($matches[1] as $value) {
			switch(true){
				case false !== stripos($value, '"BreadcrumbList"'):
					$array['breadcrumbs'] = $value;
				break;
				case false !== stripos($value, '"WebSite"'):
					$array['website'] = $value;
				break;
				case false !== stripos($value, '"Organization"'):
					$array['organization'] = $value;
				break;
				case false !== stripos($value, '"Person"'):
					$array['person'] = $value;
				break;
				default:
					$array[] = $value;
				break;
			}
		}

		return $array;
	}

	function get_schema_for_current_query(){
		ob_start();
		$this->json_ld();
		$scripts = ob_get_clean();

		return $this->parse_scripts_json($scripts);
	}
}