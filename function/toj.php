<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
class toj {
	private $name='TNFSH Online Judge';
	private $pattern="/^[1-9]{1}[0-9]*$/i";
	private $url='http://toj.tfcis.org';
	private $api='http://toj.tfcis.org/oj/be/api';

	public function ojinfo() {
		$response['name']=$this->name;
		$response['url']=$this->url;
		return $response;
	}

	public function userinfo($users) {
		foreach ($users as $uid) {
			$data=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'INFO','acct_id'=>$uid))->html);
			$response[$uid]['name']=utf8_decode($data->nick);
			$response[$uid]['score']=$data->score;
		}
		return $response;
	}

	private function savestat($uid, $stat) {
		$data['timestamp']=time();
		$data['stat']=$stat;
		file_put_contents(__DIR__.'/../cache/toj_'.$uid, json_encode($data));
	}

	private function readstat($uid) {
		$data=json_decode(file_get_contents(__DIR__.'/../cache/toj_'.$uid), true);
		return $data;
	}

	private function fetchstat($validtime, $uid) {
		$data=$this->readstat($uid);
		if($validtime<$data['timestamp'])return $data['stat'];
		$statslist=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'AC','acct_id'=>$uid))->html)->ac;
		foreach ($statslist as $pid) {
			$response[$pid]='AC';
		}
		$statslist=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'NA','acct_id'=>$uid))->html)->na;
		foreach ($statslist as $pid) {
			$response[$pid]='NA';
		}
		$this->savestat($uid, $response);
		return $response;
	}

	public function userstat($validtime, $users, $probs=NULL) {
		foreach ($users as $uid) {
			$data=$this->fetchstat($validtime, $uid);
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
}
?>