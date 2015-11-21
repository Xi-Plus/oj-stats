<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
class cf {
	private $name='Codeforces';
	public $pattern="/^[0-9]+[A-Z]{1}$/";
	private $url='http://codeforces.com/';
	private $api='http://codeforces.com/api/';
	private $verdictlist=array(
		'OK'=>'AC',
		'WRONG_ANSWER'=>'WA',
		'TIME_LIMIT_EXCEEDED'=>'TLE',
		'MEMORY_LIMIT_EXCEEDED'=>'MLE',
		'RUNTIME_ERROR'=>'RE',
		'COMPILATION_ERROR'=>'CE',
		'CHALLENGED'=>'Challenging'
	);

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

	public function checkpid($pid){
		if (!preg_match($this->pattern, $pid)) return true;
		else return false;
	}

	private function fetch($validtime, $uid) {
		$data=$this->read($uid);
		if($data!==false&&time()-$validtime<$data['timestamp'])return $data;
		$data=json_decode(cURL_HTTP_Request($this->api.'user.info?handles='.$uid)->html,true)['result'][0];
		$response['info']=$data;
		$data=json_decode(cURL_HTTP_Request($this->api.'user.status?handle='.$uid)->html,true)['result'];
		foreach ($data as $temp) {
			$pid=$temp['problem']['contestId'].$temp['problem']['index'];
			$response['stat'][$pid]=$this->verdictlist[$temp['verdict']];
		}
		$this->save($uid, $response);
		return $response;
	}

	private function save($uid, $data) {
		$data['timestamp']=time();
		file_put_contents(__DIR__.'/../cache/cf_'.$uid.'.dat', json_encode($data));
	}

	private function read($uid) {
		$data=@file_get_contents(__DIR__.'/../cache/cf_'.$uid.'.dat');
		if($data===false)return false;
		$data=json_decode(file_get_contents(__DIR__.'/../cache/toj_'.$uid.'.dat'), true);
		return $data;
	}
}
?>