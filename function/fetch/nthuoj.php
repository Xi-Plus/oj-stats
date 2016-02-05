<?php
require_once(__DIR__.'/../../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class nthuoj {
	private $info=array(
		'id'=>'nthuoj',
		'name'=>'National Tsing Hua University Online Judge',
		'pattern'=>'[1-9]{1}[0-9]{3}',
		'url'=>'http://acm.cs.nthu.edu.tw'
	);

	public function ojinfo() {
		return $this->info;
	}

	public function problink($pid) {
		return 'http://acm.cs.nthu.edu.tw/problem/'.$pid.'/';
	}

	public function userlink($uid) {
		return 'http://acm.cs.nthu.edu.tw/users/profile/'.$uid;
	}

	public function statuslink($uid, $pid) {
		return 'http://acm.cs.nthu.edu.tw/status/?username='.$pid.'&pid='.$uid;
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
		$data=cURL_HTTP_Request($this->userlink($uid))->html;
		if (preg_match_all('/{"value": (\d+?), "label": "(.+?)"}/', $data, $match)) {
			foreach ($match[0] as $index => $temp) {
				$response['info'][$match[2][$index]]=$match[1][$index];
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>
