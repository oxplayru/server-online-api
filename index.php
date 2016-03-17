<?php
$f3 = require('./vendor/bcosca/fatfree-core/base.php');
require './vendor/xpaw/php-source-query-class/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;

$f3->config('config.ini');

function getServers($servers){
	$ret = array();
	
	foreach($servers as $server ){
		$Query = new SourceQuery( );
		try
		{
			for ($i = 0; $i < 3;$i++ ){
				if(!$got_sv_info){
					$Query->Connect( $server["ip"], $server["port"], 1, SourceQuery :: SOURCE );
					$v = $Query->GetInfo();
					if (is_array($v)) {
						array_push($ret, array($server["ip"], $server["port"], $server["name"], $v["Players"], $v["MaxPlayers"], $server["number"]));
						$got_sv_info = true;
					}
					$Query->Disconnect();
				}else{
					break;
				}
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
			echo 'nothing to show';
		}else{
			echo json_encode($sv_list);
		}
    }
);

$f3->route('GET /update/@api_key',
    function($f3) {
		if ($f3->get('PARAMS.api_key') == $f3->get('api_key')){
			$cache = Cache::instance();
			$cache->load(true);
	
			$sv_list = getServers($f3->get('servers'));
			$cache->set('servers_list', $sv_list, 10*60);
			
	        echo '{ "acknowledged" : true }';
		}else{
			echo json_encode(array("acknowledged" => false, "error" => "Wrong API key"));
		}
    }
);

$f3->run();