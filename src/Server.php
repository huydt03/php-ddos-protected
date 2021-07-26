<?php namespace Drhuy\DdosProtected;
/**
 * 
 */
use Drhuy\DdosProtected\Helpers;

class Server
{
	// dir_logs(string) dir write log note '/'
	private $dir_logs 			= 'logs/';

	// site_name(string) group logs
	private $site_name			= '';

	// log_file(string) file record actions used for auto_remove_log 
	private $log_file 			= 'log';

	// max_request(int) limit request reset by minute_reset
	public $max_request			= 180;

	// fix_max_request(int) default max_request if run() without max_request
	public $fix_max_request		= 180;

	// minute_reset(int) reset requests after minutes
	public $minute_reset		= 1;

	// auto_remove_log(bool) auto remove logs
	public $auto_remove_log		= true;

	// n_logs_keep(int) keep n last logs
	public $n_logs_keep  		= 1;

	// block_type(string)['All', 'IP', 'MAC']
	public $block_type 			= 'All';

	// temp_block_type(string) is temp block_type
	public $temp_block_type		= 'All';

	// flag(bool) check was runned
	private $flag				= false;

	// onSuspend(function) callback when suspended
	private $onSuspend;

	// onAccept(function) callback when accept
	private $onAccept;

	function __construct($arguments = []){
		$this-> initParams($arguments);
		$this-> dir_logs = $this-> dir_logs.$this-> site_name.'/';
		$this-> log_file = $this-> dir_logs.$this-> log_file;
		$this-> createDir($this-> dir_logs);
	}

	// global
	private function initParams($arguments){
		if(!isset($arguments['block_type']))
			$arguments['block_type'] = 'All';
		if(!isset($arguments['max_request']))
			$arguments['max_request'] = $this-> fix_max_request;
		foreach($arguments as $key => $value) {
			$this-> {$key} = $value;
		}
	}

	private function createDir($dir){
		if (!file_exists($dir)) {
		    mkdir($dir, 0777, true);
		}
	}
	
	private function getFileName($block_type, $time = null){
		$this-> temp_block_type = $block_type;
		return Helpers::getFileName($this-> minute_reset, $block_type, $this-> dir_logs, $time);
	}

	/* run
	* @arguments(object) $this-> prototypes
	* return (callback)['onAccept', 'onSuspend']
	*/
	public function run($arguments = []){
		$this-> initParams($arguments);
		$fn_name = 'blockBy'.$this-> block_type;
		if(method_exists($this, $fn_name))
			$this-> {$fn_name}();
		else 
			$this-> blockByAll();
	}

	// Action by type All
	private function blockByAll(){
		$this-> doBlock($this-> getFileName($this-> block_type));
	}

	// Action by type IP
	private function blockByIP(){
		$this-> doBlock($this-> getFileName(Helpers::getIpClient()));
	}

	// Action by type MAC
	private function blockByMAC(){
		$this-> doBlock($this-> getFileName(Helpers::getMacClient()));
	}

	private function doBlock($filename){
		$n_requests = $this-> getRequestCount($filename);
		if($n_requests >= $this-> max_request){
			if($this-> block_type != $this-> temp_block_type)
				$this-> updateRequestCount($filename, ++$n_requests);
			return $this-> supendRequest();
		}
		$this-> updateRequestCount($filename, ++$n_requests);
		return $this-> acceptRequest();
	}

	private function getRequestCount($filename){
		if(!file_exists($filename[0])){
			$this-> updateRequestCount($filename, 0);
			return 0;
		}
		$data = file_get_contents($filename[0]);
		$data = json_decode($data, true);
		if(!isset($data['requests'])){
			$this-> updateRequestCount($filename, 0);
			return 0;
		}
		return $data['requests'];
	}

	private function updateRequestCount($filename, $n_requests){
		if($n_requests == 0 && $this-> auto_remove_log && !$this-> flag){
			$this-> flag = true;
			$this-> removeLog($filename[1]);
			file_put_contents($this-> log_file, $filename[1].PHP_EOL , FILE_APPEND | LOCK_EX);
		}
		$this-> createDir($this-> dir_logs.$filename[1].'/');
		file_put_contents($filename[0], json_encode(['requests'=> $n_requests]));
	}

	private function removeLog($cur_dir){
		if(!file_exists($this-> log_file))
			return;
		$handle = fopen($this-> log_file, "r");
		if ($handle) {
			$i = 0;
			$j = 0;
			$lines = [];
		    while (($line = fgets($handle)) !== false) {
		    	$lines[$i++] = trim($line);
		    }
		    fclose($handle);
			unlink($this-> log_file);
		    foreach ($lines as $line) {
		    	if($i < ($j++ + $this-> n_logs_keep)){
		    		file_put_contents($this-> log_file, $line.PHP_EOL , FILE_APPEND | LOCK_EX);
		    		continue;
		    	}
		    	$dir = $this-> dir_logs.$line;
		        Helpers::removeDirectory($dir);
		    }
		} else {
		    // error opening the file.
		} 
	}

	private function supendRequest(){
		if(is_callable($this-> onSuspend))
			call_user_func($this-> onSuspend, $this-> temp_block_type);
		header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);
    	exit;
	}

	private function acceptRequest(){
		if(is_callable($this-> onAccept))
			call_user_func($this-> onAccept);
	}


}