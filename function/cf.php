<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class cf {
	private $ojid='cf';
	private $name='Codeforces';
	public $pattern="/^[0-9]+[A-Z]{1}$/";
	private $url='http://codeforces.com/';
	private $api='http://codeforces.com/api/';

	public function ojinfo() {
		$response['name']=$this->name;
		$response['url']=$this->url;
		return $response;
	}

	public function userinfo($validtime, $users) {
		foreach ($users as $uid) {
			$data=$this->fetch($validtime, $uid)['info'];
			$response[$uid]=$data;
		}
		return $response;
	}

	public function userstat($validtime, $users, $probs=NULL) {
		foreach ($users as $uid) {
			$data=$this->fetch($validtime, $uid)['stat'];
			if (is_array($probs)) {
				foreach ($probs as $pid) {
					$response[$uid][$pid]=$data[$pid];
					if ($response[$uid][$pid]===null) $response[$uid][$pid]='';
				}
			} else {
				$response[$uid]=$data;
			}
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
		$data=(new cache)->read($this->ojid, $uid);
		if($data!==false&&time()-$validtime<$data['timestamp'])return $data;
		$data=json_decode(cURL_HTTP_Request($this->api.'user.info?handles='.$uid)->html,true)['result'][0];
		$response['info']=$data;
		$data=json_decode(cURL_HTTP_Request($this->api.'user.status?handle='.$uid)->html,true)['result'];
		foreach ($data as $temp) {
			$pid=$temp['problem']['contestId'].$temp['problem']['index'];
			$response['stat'][$pid]=$this->changestat($response['stat'][$pid],$temp['verdict']);
		}
		(new cache)->write($this->ojid, $uid, $response);
		return $response;
	}
}
?>