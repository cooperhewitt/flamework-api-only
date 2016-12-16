<?php

  loadlib("users");

	#################################################################

	function api_users_createUser(){

		$out = array();

    $username = request_str("username");
    $email = request_str("email");
    $password = request_str("password");

    ## here we need to do all the stuff we normally do in signup

    if ((!strlen($email)) || (!strlen($password)) || (!strlen($username))){
      api_output_error(405, "Required parameters missing");
		}

		#
		# email available?
		#
		if (users_is_email_taken($email)){
      api_output_error(405, "Email is already taken");
		}

		#
		# username available?
		#
		if (users_is_username_taken($username)){
      api_output_error(405, "Username is already taken");
		}

		#
		# create account
		#

    $ret = users_create_user(array(
      'username'	=> $username,
      'email'		=> $email,
      'password'	=> $password,
    ));

		api_output_ok($ret);
	}
