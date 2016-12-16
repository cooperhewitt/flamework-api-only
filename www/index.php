<?php

	include("include/init.php");

	$method = request_str("method");

	api_dispatch($method);
	exit();
