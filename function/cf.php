<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class cf {
	private $info=array(
		'id'=>'cf',
		'name'=>'Codeforces',
		'pattern'=>'[0-9]+[A-Z]{1}',
		'url'=>'http://codeforces.com'
	);
	private $api='http://codeforces.com/api/';

	public function ojinfo() {
		return $this->info;
	}

	public function problink($pid) {
		return 'http://codeforces.com/problemset/problem/'.substr($pid, 0, strlen($pid)-1).'/'.$pid[strlen($pid)-1];
	}

	public function userlink($uid) {
		return 'http://codeforces.com/profile/'.$uid;
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

	public function userstat($validtime, $users, $probs=NULL) {
		foreach ($users as $uid) {
			$response[$uid]=$this->fetch($validtime, $uid)['stat'];
		}
		return $response;
	}
	
	private $verdictlist=array(
		'OK'=>'AC',
		'WRONG_ANSWER'=>'WA',
		'TIME_LIMIT_EXCEEDED'=>'TLE',
		'MEMORY_LIMIT_EXCEEDED'=>'MLE',
		'RUNTIME_ERROR'=>'RE',
		'COMPILATION_ERROR'=>'CE',
		'CHALLENGED'=>'Challenging'
	);
	
	private $verdictorder=array(
		'AC'=>0,
		'WA'=>1,
		'TLE'=>2,
		'MLE'=>3,
		'RE'=>4,
		'CE'=>5,
		'Challenging'=>6
	);

	private function changestat($statold, $statnew){
		$statnew=$this->verdictlist[$statnew];
		if ($statold===null) return $statnew;
		if ($this->verdictorder[$statold]<$this->verdictorder[$statnew]) return $statold;
		return $statnew;
	}

	private function fetch($validtime, $uid) {
		$data=(new cache)->read($this->info['id'], $uid);
		if($data!==false&&time()-$validtime<$data['timestamp'])return $data;
		$data=json_decode(cURL_HTTP_Request($this->api.'user.info?handles='.$uid)->html,true)['result'][0];
		$response['info']=$data;
		$data=json_decode(cURL_HTTP_Request($this->api.'user.status?handle='.$uid)->html,true)['result'];
		foreach ($data as $temp) {
			$pid=$temp['problem']['contestId'].$temp['problem']['index'];

			if (isset($response['stat'][$pid]['status'])) $this->changestat($response['stat'][$pid]['status'],$temp['verdict']);
			else $response['stat'][$pid]['status']=$this->verdictlist[$temp['verdict']];
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>