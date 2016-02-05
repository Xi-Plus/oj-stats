<?php
class cache {
	public function read($ojid, $uid) {
		$uid=strtolower($uid);
		$data=@file_get_contents(__DIR__.'/../../cache/'.$ojid.'_'.$uid.'.dat');
		if($data===false)return false;
		$data=json_decode($data, true);
		return $data;
	}

	public function write($ojid, $uid, $data) {
		$uid=strtolower($uid);
		$data['timestamp']=time();
		file_put_contents(__DIR__.'/../../cache/'.$ojid.'_'.$uid.'.dat', json_encode($data));
	}
}
?>