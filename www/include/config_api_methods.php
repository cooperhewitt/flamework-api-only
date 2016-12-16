<?php

	########################################################################

	$GLOBALS['cfg']['api']['methods'] = array_merge(array(

		"api.spec.methods" => array (
			"description" => "Return the list of available API response methods.",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_spec"
		),

		"api.spec.formats" => array(
			"description" => "Return the list of valid API response formats, including the default format",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_spec"
		),

		"test.echo" => array(
			"description" => "A testing method which echo's all parameters back in the response.",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_test"
		),

		"test.error" => array(
			"description" => "Return a test error from the API",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_test"
		),

		#### Users

		"users.createUser" => array(
			"description" => "Create a new user account",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_users",
			"parameters" => array(
				array(
					"name" => "username",
					"description" => "Your desired username",
					"required" => 1,
					"documented" => 1
				),
				array(
					"name" => "email",
					"description" => "Your email address",
					"required" => 1,
					"documented" => 1
				),
				array(
					"name" => "password",
					"description" => "Your desired password",
					"required" => 1,
					"documented" => 1
				),
			),
		),


	), $GLOBALS['cfg']['api']['methods']);

	########################################################################

	# the end
