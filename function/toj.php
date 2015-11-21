<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
class toj {
	private $name='TNFSH Online Judge';
	public $pattern="/^[1-9]{1}[0-9]*$/";
	private $url='http://toj.tfcis.org';
	private $api='http://toj.tfcis.org/oj/be/api';

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
		if (!preg_match($this->pattern, $pid)) throw new Exception('Prob ('.$pid.') not match pattern ('.$this->pattern.')');
	}

	private function fetch($validtime, $uid) {
		$data=$this->read($uid);
		if($data!==false&&time()-$validtime<$data['timestamp'])return $data;
		$data=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'INFO','acct_id'=>$uid))->html,true);
		$response['info']=$data;
		$data=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'AC','acct_id'=>$uid))->html,true)['ac'];
		foreach ($data as $pid) {
			$response['stat'][$pid]='AC';
		}
		$data=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'NA','acct_id'=>$uid))->html,true)['na'];
		foreach ($data as $pid) {
			$response['stat'][$pid]='NA';
		}
		$this->save($uid, $response);
		return $response;
	}

	private function save($uid, $data) {
		$data['timestamp']=time();
		file_put_contents(__DIR__.'/../cache/toj_'.$uid.'.dat', json_encode($data));
	}

	private function read($uid) {
		$data=@file_get_contents(__DIR__.'/../cache/toj_'.$uid.'.dat');
		if($data===false)return false;
		$data=json_decode(file_get_contents(__DIR__.'/../cache/toj_'.$uid.'.dat'), true);
		return $data;
	}
}
?>