<?php namespace Drhuy\DdosProtected;
/**
 * 
 */
class Helpers{

	public static function getTime(){
		$min = date("i");
		$hour = date("h");
		$day = date('d');
		return ['hour'=> $hour, 'min'=> $min, 'day'=> $day];
	}

	public static function getFileName($time_reset = 5, $fix_name = 'at', $dir_logs = 'ddos_protected/', $time = null){
		if(!isset($time['hour']) || !isset($time['min']) || !isset($time['day']))
			$time = self::getTime();
		$hour = $time['hour'];
		$min  = $time['min'];
		$day  = $time['day'];
		$min = $time_reset * floor($min/$time_reset);
		$dir = $dir_logs. "$day-$hour-$min/";
		if (!file_exists($dir)) {
		    mkdir($dir, 0777, true);
		}
		return [$dir . $fix_name, "$day-$hour-$min"];
	}

	public static function removeDirectory($dir) {
		if(!file_exists($dir))
			return;
	    foreach(scandir($dir) as $file) {
	        if ('.' === $file || '..' === $file) continue;
	        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
	        else unlink("$dir/$file");
	    }
	    rmdir($dir);
	}

	public static function getIpClient(){
		$ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	       $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	    return str_replace(":","",$ipaddress);;
	}

	public static function getMacClient(){
		$string=exec('getmac');
		$mac=substr($string, 0, 17); 
		return $mac;
	}

}