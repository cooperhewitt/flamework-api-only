<?php

	function utf8_headers($mimetype = 'text/html'){

		header("Content-Type: $mimetype; charset=utf-8");

		if ($GLOBALS['cfg']['no_cache']){

			# Date in the past
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

			# always modified
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

			# HTTP/1.1
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);

			# HTTP/1.0
			header("Pragma: no-cache");
		}else{

			if ($GLOBALS['cfg']['user']['id']){

				  header("Cache-Control: private");
			}
		}
	}
