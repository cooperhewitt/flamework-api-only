<?php

	$GLOBALS['postgresql_conns'] = array();

	$GLOBALS['timings']['postgresql_conns_count']	= 0;
	$GLOBALS['timings']['postgresql_conns_time']	= 0;
	$GLOBALS['timings']['postgresql_queries_count']	= 0;
	$GLOBALS['timings']['postgresql_queries_time']	= 0;
	$GLOBALS['timings']['postgresql_rows_count']	= 0;
	$GLOBALS['timings']['postgresql_rows_time']	= 0;

	$GLOBALS['timing_keys']['postgresql_conns']	= 'DB Connections';
	$GLOBALS['timing_keys']['postgresql_queries']	= 'DB Queries';
	$GLOBALS['timing_keys']['postgresql_rows']	= 'DB Rows Returned';

	#################################################################

	function postgresql_init(){

		if (!function_exists('pg_connect')){
			die("[lib_postgresql] requires the PostgreSQL PHP extension\n");
		}

		#
		# connect to the main cluster immediately so that we can show a
		# downtime notice it's it's not available? you might not want to
		# so this - depends on whether you can ever stand the main cluster
		# being down.
		#

		if ($GLOBALS['cfg']['postgresql_main']['auto_connect']){
			_postgresql_connect('main', null);
		}
	}

	#################################################################
	function postgresql_insert($tbl, $hash){			return _postgresql_insert($tbl, $hash, 'main', null); }

	function postgresql_fetch($sql){				return _postgresql_fetch($sql, 'main', null); }
	function postgresql_fetch_slave($sql){				return _postgresql_fetch_slave($sql, 'main_slaves'); }
	function postgresql_fetch_users($k, $sql){			return _postgresql_fetch($sql, 'users', $k); }

	function postgresql_fetch_paginated($sql, $args){		return _postgresql_fetch_paginated($sql, $args, 'main', null); }
	function postgresql_fetch_paginated_users($k, $sql, $args){	return _postgresql_fetch_paginated($sql, $args, 'users', $k); }

	function postgresql_write($sql){				return _postgresql_write($sql, 'main', null); }

	#################################################################


	function _postgresql_connect($cluster, $shard){

		$cluster_key = _postgresql_cluster_key($cluster, $shard);

		$host = $GLOBALS['cfg']["postgresql_{$cluster}"]["host"];
		$user = $GLOBALS['cfg']["postgresql_{$cluster}"]["user"];
		$pass = $GLOBALS['cfg']["postgresql_{$cluster}"]["pass"];
		$name = $GLOBALS['cfg']["postgresql_{$cluster}"]["name"];
		$port = $GLOBALS['cfg']["postgresql_{$cluster}"]["port"];


		if ($shard){
			$host = $host[$shard];
			$name = $name[$shard];
		}

		if (!$host){
			log_fatal("no such cluster: ".$cluster);
		}

		#
		# try to connect
		#

		$start = microtime_ms();

		$connection_string = "host=" . $host . " port=" . $port . " dbname=" . $name . " user=" . $user . " password=" . $pass;

		$GLOBALS['postgresql_conns'][$cluster_key] = pg_connect($connection_string);

		$end = microtime_ms();



		#
		# log
		#

		log_notice('postgresql', "DB-$cluster_key: Connect", $end-$start);

		if (!$GLOBALS['postgresql_conns'][$cluster_key] || $GLOBALS['cfg']['admin_flags_no_db']){

			log_fatal("Connection to database cluster '$cluster_key' failed");
		}

		$GLOBALS['timings']['postgresql_conns_count']++;
		$GLOBALS['timings']['postgresql_conns_time'] += $end-$start;


	}


	#################################################################

	function _postgresql_query($sql, $cluster, $shard){

		$cluster_key = _postgresql_cluster_key($cluster, $shard);

		if (!$GLOBALS['postgresql_conns'][$cluster_key]){
			_postgresql_connect($cluster, $shard);
		}

		#$trace = _pg_callstack();
		#$use_sql = _pg_comment_query($sql, $trace);

		$start = microtime_ms();
		$result = pg_query($GLOBALS['postgresql_conns'][$cluster_key], $sql);
		$end = microtime_ms();

		$GLOBALS['timings']['postgresql_queries_count']++;
		$GLOBALS['timings']['postgresql_queries_time'] += $end-$start;

		log_notice('postgresql', "DB-$cluster_key: $sql ($trace)", $end-$start);

		#
		# build result
		#

		if (!$result){
			#$error_msg	= mysql_error($GLOBALS['db_conns'][$cluster_key]);
			#$error_code	= mysql_errno($GLOBALS['db_conns'][$cluster_key]);

			#log_error("DB-$cluster_key: $error_code ".HtmlSpecialChars($error_msg));

			$ret = array(
				'ok'		=> 0,
				#'error'		=> $error_msg,
				#'error_code'	=> $error_code,
				'sql'		=> $sql,
				'cluster'	=> $cluster,
				'shard'		=> $shard,
			);
		}else{
			$ret = array(
				'ok'		=> 1,
				'result'	=> $result,
				'sql'		=> $sql,
				'cluster'	=> $cluster,
				'shard'		=> $shard,
			);
		}

		#if ($profile) $ret['profile'] = $profile;

		return $ret;
	}

	#################################################################

	function _postgresql_insert($tbl, $hash, $cluster, $shard){

		$fields = array_keys($hash);
		#return _postgresql_write("INSERT INTO $tbl (`".implode('`,`',$fields)."`) VALUES ('".implode("','",$hash)."')", $cluster, $shard);

		return _postgresql_write("INSERT INTO $tbl (".implode(",",$fields).") VALUES ('".implode("','",$hash)."')", $cluster, $shard);

	}

	#################################################################

	function _postgresql_fetch($sql, $cluster, $shard){

		$ret = _postgresql_query($sql, $cluster, $shard);

		if (!$ret['ok']) return $ret;

		$out = $ret;
		$out['ok'] = 1;
		$out['rows'] = array();
		unset($out['result']);

		$start = microtime_ms();
		$count = 0;

		while ($row = pg_fetch_array($ret['result'], NULL, PGSQL_ASSOC)){
			$out['rows'][] = $row;
			$count++;
		}
		$end = microtime_ms();
		$GLOBALS['timings']['postgresql_rows_count'] += $count;
		$GLOBALS['timings']['postgresql_rows_time'] += $end-$start;

		return $out;
	}


	#################################################################

	function _postgresql_fetch_paginated($sql, $args, $cluster, $shard){

		#
		# Setup some defaults
		#

		$page		= isset($args['page'])		? max(1, $args['page'])		: 1;
		$per_page	= isset($args['per_page'])	? max(1, $args['per_page'])	: $GLOBALS['cfg']['pagination_per_page'];
		$spill		= isset($args['spill'])		? max(0, $args['spill'])	: $GLOBALS['cfg']['pagination_spill'];

		if ($spill >= $per_page) $spill = $per_page - 1;


		#
		# If we're using the 2-query method, get the count first
		#

		$calc_found_rows = !!$args['calc_found_rows'];

		if (!$calc_found_rows){

			$count_sql = _postgresql_count_sql($sql, $args);
			$ret = _postgresql_fetch($count_sql, $cluster, $shard);
			if (!$ret['ok']) return $ret;

			$total_count = intval(array_pop($ret['rows'][0]));
			$page_count = ceil($total_count / $per_page);
		}


		#
		# generate limit values
		#

		$start = ($page - 1) * $per_page;
		$limit = $per_page;

		if ($calc_found_rows){

			$limit += $spill;

		}else{

			$last_page_count = $total_count - (($page_count - 1) * $per_page);

			if ($last_page_count <= $spill && $page_count > 1){
				$page_count--;
			}

			if ($page == $page_count){
				$limit += $spill;
			}

			if ($page > $page_count){
				# we do this to ensure we fetch no rows if we're asking for the
				# page after the last one, else we might end up with some spill
				# being returned.
				$start = $total_count + 1;
			}
		}


		#
		# build sql
		#

		$sql .= " LIMIT $limit OFFSET $start";

		if ($calc_found_rows){

			$sql = preg_replace('/^\s*SELECT\s+/', 'SELECT SQL_CALC_FOUND_ROWS ', $sql);
		}

		$ret = _postgresql_fetch($sql, $cluster, $shard);


		#
		# figure out paging if we're using CALC_FOUND_ROWS
		#

		if ($calc_found_rows){

			$ret2 = _postgresql_fetch("SELECT FOUND_ROWS()", $cluster, $shard);

			$total_count = intval(array_pop($ret2['rows'][0]));
			$page_count = ceil($total_count / $per_page);

			$last_page_count = $total_count - (($page_count - 1) * $per_page);

			if ($last_page_count <= $spill && $page_count > 1){
				$page_count--;
			}

			if ($page > $page_count){
				$ret['rows'] = array();
			}
			if ($page < $page_count){
				$ret['rows'] = array_slice($ret['rows'], 0, $per_page);
			}
		}


		#
		# add pagination info to result
		#

		$ret['pagination'] = array(
			'total_count'	=> $total_count,
			'page'		=> $page,
			'per_page'	=> $per_page,
			'page_count'	=> $page_count,
			'first'		=> $start+1,
			'last'		=> $start+count($ret['rows']),
		);

		if (!count($ret['rows'])){
			$ret['pagination']['first'] = 0;
			$ret['pagination']['last'] = 0;
		}

		return $ret;
	}


	#################################################################

	function _postgresql_write($sql, $cluster, $shard){

		$cluster_key = _postgresql_cluster_key($cluster, $shard);
		$ret = _postgresql_query($sql, $cluster, $shard);

		if (!$ret['ok']) return $ret;

		return array(
			'ok'		=> 1,
			#'affected_rows'	=> mysql_affected_rows($GLOBALS['db_conns'][$cluster_key]),
			#'insert_id'	=> mysql_insert_id($GLOBALS['db_conns'][$cluster_key]),
		);
	}

	#################################################################

	function _postgresql_count_sql($sql, $args){

		# remove any ORDER'ing & LIMIT'ing
		$sql = preg_replace('/ ORDER BY .*$/', '', $sql);
		$sql = preg_replace('/ LIMIT .*$/', '', $sql);

		# transform the select portion
		if (isset($args['count_fields'])){

			$sql = preg_replace('/^SELECT (.*?) FROM/i', "SELECT COUNT({$args['count_fields']}) FROM", $sql);
		}else{
			$sql = preg_replace_callback('/^SELECT (.*?) FROM/i', '_db_count_sql_from', $sql);
		}

		return $sql;
	}

	#################################################################

	function _postgresql_count_sql_from($m){

		return "SELECT COUNT($m[1]) FROM";
	}

	#################################################################

	function _postgresql_cluster_key($cluster, $shard){

		return $shard ? "{$cluster}-{$shard}" : $cluster;
	}
