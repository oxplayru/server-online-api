<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$f3 = require('./vendor/bcosca/fatfree-core/base.php');
require './vendor/xpaw/php-source-query-class/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;

$f3->config('config.ini');

function getServers($servers, $reconnect_attempts){
	$ret = array();
	
	foreach($servers as $server ){
		$Query = new SourceQuery( );
		try
		{
			for ($i = 0; $i < $reconnect_attempts;$i++ ){
				if(!$got_sv_info){
					$Query->Connect( $server["ip"], $server["port"], 1, SourceQuery :: SOURCE );
					$v = $Query->GetInfo();
					
					if (is_array($v)) {
						array_push($ret, array(
							"ip" => $server["ip"],
							"port" => $server["port"],
							"name" => $server["name"],
							"players" => $v["Players"],
							"max_players" => $v["MaxPlayers"],
							"number" => $server["number"],
							"map" => $v["Map"],
							"status" => true)
						);
						$got_sv_info = true;
					}
					$Query->Disconnect();
				}else{
					break;
				}
			}
			
			if(!$got_sv_info){
				array_push($ret, array(
					"ip" => $server["ip"],
					"port" => $server["port"],
					"name" => $server["name"],
					"players" => 0,
					"max_players" => 0,
					"number" => $server["number"],
					"map" => "N/A",
					"status" => false)
				);
			}
			
			$got_sv_info = false;
		}
		catch( Exception $e )
		{
		}
	}

	return $ret;
}


$f3->route('GET /',
    function($f3) {

		$cache = Cache::instance();
		$cache->load(true);


		if (!$cache->exists('servers_list', $sv_list)) {
			$sv_list = array();
			foreach($f3->get('servers') as $server ){
				array_push($sv_list, array(
					"ip" => $server["ip"],
					"port" => $server["port"],
					"name" => $server["name"],
					"players" => 0,
					"max_players" => 0,
					"number" => $server["number"],
					"map" => "N/A",
					"status" => false)
				);
			}
			$sv_list = array("last_update" => false, "servers" => $sv_list);
		}

		echo json_encode($sv_list, JSON_PRETTY_PRINT);
    }
);

$f3->route('GET /update/@api_key',
    function($f3) {
		if ($f3->get('PARAMS.api_key') == $f3->get('api_key')){
			$cache = Cache::instance();
			$cache->load(true);
	
			$sv_list = array("last_update" => time() , "servers" => getServers($f3->get('servers'), $f3->get('reconnect_attempts')));
			$cache->clear('servers_list'); //Because function 'set' don't update cache time.
			$cache->set('servers_list', $sv_list, $f3->get("cache_clear_time"));
			
	        echo json_encode(array("acknowledged" => true));
		}else{
			echo json_encode(array("acknowledged" => false, "error" => "Wrong API key"));
		}
    }
);

$f3->route('GET /clear/@api_key',
    function($f3) {
		if ($f3->get('PARAMS.api_key') == $f3->get('api_key')){
			$cache = Cache::instance();
			$cache->load(true);
	
			$cache->clear('servers_list');
			
	        echo json_encode(array("acknowledged" => true));
		}else{
			echo json_encode(array("acknowledged" => false, "error" => "Wrong API key"));
		}
    }
);


$f3->run();