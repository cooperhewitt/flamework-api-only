<?php

	$GLOBALS['cfg'] = array();

	# Things you may want to change in a hurry

	$GLOBALS['cfg']['site_name'] = 'flamework-api-only';
	$GLOBALS['cfg']['environment'] = 'dev';

	$GLOBALS['cfg']['site_disabled'] = 0;
	$GLOBALS['cfg']['site_disabled_retry_after'] = 0;	# seconds; if set will return HTTP Retry-After header

	# Message is displayed in the nav header in inc_head.txt

	$GLOBALS['cfg']['display_message'] = 0;
	$GLOBALS['cfg']['display_message_text'] = '';

	# Things you'll certainly need to tweak
	# See below for details about database password(s)

	$GLOBALS['cfg']['db_main'] = array(
		'host'	=> 'localhost',
		'name'	=> 'flamework',		# database name
		'user'	=> 'root',		# database username
		'auto_connect' => 0,
	);

	$GLOBALS['cfg']['db_users'] = array(

		'host' => array(
			1 => 'localhost',
			2 => 'localhost',
		),

		'user' => 'root',

		'name' => array(
			1 => 'user1',
			2 => 'user2',
		),
	);


	# hard coding this URL will ensure it works in cron mode too

	$GLOBALS['cfg']['server_scheme'] = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https' : 'http';
	$GLOBALS['cfg']['server_name'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'fake.com';

	$GLOBALS['cfg']['abs_root_url']		= "{$GLOBALS['cfg']['server_scheme']}://{$GLOBALS['cfg']['server_name']}/";
	$GLOBALS['cfg']['safe_abs_root_url']	= $GLOBALS['cfg']['abs_root_url'];

	# These should be left as-is, unless you have an existing password database not using bcrypt and
	# you need to do auto-promotion on login.

	$GLOBALS['cfg']['passwords_use_bcrypt'] = true;
	$GLOBALS['cfg']['passwords_allow_promotion'] = false;

	# Things you may need to tweak

	# Auth roles

	$GLOBALS['cfg']['enable_feature_auth_roles_autopromote_staff'] = 0;
	$GLOBALS['cfg']['enable_feature_auth_roles_autopromote_staff_dev'] = 0;
	$GLOBALS['cfg']['enable_feature_auth_roles_autopromote_staff_shell'] = 0;

	# Caching stuff

  $GLOBALS['cfg']['enable_feature_cache_prefixes'] = 1;
  $GLOBALS['cfg']['cache_prefix'] = $GLOBALS['cfg']['environment'];

  # Rewrites

	$GLOBALS['cfg']['rewrite_static_urls'] = array(
		# '/foo' => '/bar/',
	);

  # Email

	$GLOBALS['cfg']['email_from_name']	= 'flamework app';
	$GLOBALS['cfg']['email_from_email']	= 'admin@ourapp.com';
	$GLOBALS['cfg']['auto_email_args']	= '-fadmin@ourapp.com';

	# Things you can probably not worry about

	$GLOBALS['cfg']['http_timeout'] = 3;

	$GLOBALS['cfg']['check_notices'] = 1;

	$GLOBALS['cfg']['db_profiling'] = 0;

	# db_enable_poormans_*
	#
	# If enabled, then the relevant database configs and handles
	# will be automagically prepopulated using the relevant information
	# in 'db_main'. Again, see below for database passwords

	# You should enable/set these flags if you want to
	# use flamework in a setting where you only have access
	# to a single database.

	$GLOBALS['cfg']['db_enable_poormans_federation'] = 1;
	$GLOBALS['cfg']['db_enable_poormans_slaves'] = 0;
	$GLOBALS['cfg']['db_poormans_slaves_user'] = '';

	# For when you want to use tickets but can't tweak
	# your my.cnf file or set up a dedicated ticketing
	# server. flamework does not use tickets as part of
	# core (yet) so this is really only necessary if your
	# application needs db tickets.

	$GLOBALS['cfg']['db_enable_poormans_ticketing'] = 1;

	# This will assign $pagination automatically for Smarty but
	# you probably don't want to do this for anything resembling
	# a complex application...

	$GLOBALS['cfg']['pagination_per_page'] = 10;
	$GLOBALS['cfg']['pagination_spill'] = 2;

	# Feature flags

  # we will change most of this...
	$GLOBALS['cfg']['enable_feature_signup'] = 1;
	$GLOBALS['cfg']['enable_feature_signin'] = 1;
	$GLOBALS['cfg']['enable_feature_persistent_login'] = 1;
	$GLOBALS['cfg']['enable_feature_account_delete'] = 1;
	$GLOBALS['cfg']['enable_feature_password_retrieval'] = 1;

	# Enable this flag to show a full call chain (instead of just the
	# immediate caller) in database query log messages and embedded in
	# the actual SQL sent to the server.

	$GLOBALS['cfg']['db_full_callstack'] = 0;
	$GLOBALS['cfg']['allow_prefetch'] = 0;

	# Load these libraries on every page

	$GLOBALS['cfg']['autoload_libs'] = array(
		#'cache_memcache',
	);

	# API methods and "blessings" are defined at the bottom

	# API feature flags

	$GLOBALS['cfg']['enable_feature_api'] = 1;

	$GLOBALS['cfg']['enable_feature_api_logging'] = 1;
	$GLOBALS['cfg']['enable_feature_api_throttling'] = 0;

	$GLOBALS['cfg']['enable_feature_api_require_keys'] = 0;		# because oauth2...
	$GLOBALS['cfg']['enable_feature_api_register_keys'] = 1;

	$GLOBALS['cfg']['enable_feature_api_delegated_auth'] = 1;
	$GLOBALS['cfg']['enable_feature_api_authenticate_self'] = 1;

	# API URLs and endpoints

	$GLOBALS['cfg']['api_abs_root_url'] = '';	# leave blank - set in api_config_init()
	$GLOBALS['cfg']['site_abs_root_url'] = '';	# leave blank - set in api_config_init()

	$GLOBALS['cfg']['api_subdomain'] = '';
	$GLOBALS['cfg']['api_endpoint'] = '/';

	$GLOBALS['cfg']['api_require_ssl'] = 1;

	$GLOBALS['cfg']['api_auth_type'] = 'oauth2';
	$GLOBALS['cfg']['api_oauth2_require_authentication_header'] = 0;
	$GLOBALS['cfg']['api_oauth2_allow_get_parameters'] = 1;

	# API pagination

	$GLOBALS['cfg']['api_per_page_default'] = 100;
	$GLOBALS['cfg']['api_per_page_max'] = 500;

	# The actual API config

	$GLOBALS['cfg']['api'] = array(

		'formats' => array( 'json' ),
		'default_format' => 'json',

		# We're defining methods using the method_definitions
		# hooks defined below to minimize the clutter in the
		# main config file, aka this one (20130308/straup)
		'methods' => array(),

		# We are NOT doing the same for blessed API keys since
		# it's expected that their number will be small and
		# manageable (20130308/straup)

		'blessings' => array(
			'xxx-apikey' => array(
				'hosts' => array('127.0.0.1'),
				# 'tokens' => array(),
				# 'environments' => array(),
				'methods' => array(
					'foo.bar.baz' => array(
						'environments' => array('sd-931')
					)
				),
				'method_classes' => array(
					'foo.bar' => array(
						# see above
					)
				),
			),
		),
	);

	# Load api methods defined in separate PHP files whose naming
	# convention is FLAMEWORK_INCLUDE_DIR . "/config_api_{$definition}.php";
	#
	# IMPORTANT: This is syntactic sugar and helper code to keep the growing
	# number of API methods out of the main config. Stuff is loaded in to
	# memory in lib_api_config:api_config_init (20130308/straup)

	$GLOBALS['cfg']['api_method_definitions'] = array(
		'methods',
	);


	# the end
