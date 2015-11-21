<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
class tioj {
	private $name='TIOJ Infor Online Judge';
	public $pattern="/^[1-9]{1}[0-9]{3}$/";
	private $url='http://tioj.ck.tp.edu.tw';

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
					if (isset($data[$pid])) $response[$uid][$pid]=$data[$pid];
					else $response[$uid][$pid]='';
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
		if ($data!==false&&time()-$validtime<$data['timestamp']) return $data;
		$response=$data;
		$data=cURL_HTTP_Request("http://tioj.ck.tp.edu.tw/users/".$uid,null,false,true)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/<h5>(.+?)<\/h5> <h6>.*?AC Ratio: <\/div> <div class="col-md-5"> (\d+?)<br> (\d+?)<br> (.+?)%.*?Signed up at: (.+?)<br> Last sign in: (.+?)<br>/', $data, $match)) {
			$response['info']['name']=$match[1];
			$response['info']['totalcount']['AC']=$match[2];
			$response['info']['totalcount']['NA']=$match[3];
			$response['info']['totalcount']['Ratio']=$match[4];
			$response['info']['signup']=$match[5];
			$response['info']['lastlogin']=$match[6];
		}
		if (preg_match_all('/<a class="text-success".*?>(\d+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='AC';
			}
		}
		if (preg_match_all('/<a class="text-warning".*?>(\d+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='NA';
			}
		}
		if (preg_match_all('/<a class="text-muted".*?>(\d+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='';
			}
		}
		$this->save($uid, $response);
		return $response;
	}

	private function save($uid, $data) {
		$data['timestamp']=time();
		file_put_contents(__DIR__.'/../cache/tioj_'.$uid.'.dat', json_encode($data));
	}

	private function read($uid) {
		$data=@file_get_contents(__DIR__.'/../cache/tioj_'.$uid.'.dat');
		if($data===false)return false;
		$data=json_decode($data, true);
		return $data;
	}
}
?>
