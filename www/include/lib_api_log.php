<?php

	$GLOBALS['cfg']['api_log'] = array();

	########################################################################

	function api_log($data, $dispatch=0){

		if (! features_is_enabled("api_logging")){
			return;
		}

		$GLOBALS['cfg']['api_log'] = array_merge($GLOBALS['cfg']['api_log'], $data);

		if ($dispatch){

			$pid = getmypid();
			$note = json_encode($GLOBALS['cfg']['api_log']);
			error_log("[API][{$pid}] $note");
		}
	}

	########################################################################

	# the end
