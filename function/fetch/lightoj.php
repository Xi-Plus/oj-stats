<?php
require_once(__DIR__.'/../../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class lightoj {
	private $info=array(
		'id'=>'lightoj',
		'name'=>'Jan\'s LightOJ',
		'pattern'=>'[1-9]{1}[0-9]{3}',
		'url'=>'http://lightoj.com'
	);
	private $cookiefile='lightoj_cookie.txt';

	public function ojinfo() {
		return $this->info;
	}

	public function problink($pid) {
		return 'http://lightoj.com/volume_showproblem.php?problem='.$pid;
	}

	public function userlink($uid) {
		return 'http://lightoj.com/volume_userstat.php?user_id='.$uid;
	}

	public function statuslink($uid, $pid) {
		return '';
	}

	public function userinfo($validtime, $users) {
		foreach ($users as $uid) {
			$response[$uid]=$this->fetch($validtime, $uid)['info'];
		}
		return $response;
	}

	public function userstat($validtime, $users) {
		foreach ($users as $uid) {
			$response[$uid]=$this->fetch($validtime, $uid)['stat'];
		}
		return $response;
	}

	private function fetch($validtime, $uid) {
		$data=(new cache)->read($this->info['id'], $uid);
		if ($data!==false&&time()-$validtime<$data['timestamp']) return $data;
		$response=array('info'=>array(), 'stat'=>array());
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>