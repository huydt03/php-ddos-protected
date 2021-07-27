<?php require 'vendor/autoload.php';

use Drhuy\DdosProtected\Server;

$t = new Server([
	'fix_max_request'	=> 15,
	'max_request'		=> 10,
	'minute_reset'		=> 1,
	'n_logs_keep'		=> 3,
	'auto_remove_log'	=> true,
	'block_type'		=> '',
	'site_name'			=> '',
	'onSuspend'			=> function($client){
	},
	'onAccept'			=> function(){
	}
]);
// $t = new Server;
$t-> run(['block_type'=> 'IP', 'max_request'=> 5]);
$t-> run();
?>
