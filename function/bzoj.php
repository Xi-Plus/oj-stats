<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class bzoj {
	private $ojid='bzoj';
	private $name='大视野在线测评';
	public $pattern='[1-9]{1}[0-9]{3}';
	private $url='http://www.lydsy.com/JudgeOnline';

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

	private function fetch($validtime, $uid) {
		$data=(new cache)->read($this->ojid, $uid);
		if ($data!==false&&time()-$validtime<$data['timestamp']) return $data;
		$response=$data;
		$data=cURL_HTTP_Request('http://www.lydsy.com/JudgeOnline/userinfo.php?user='.$uid)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/<caption>.*?--(.+?)<\/caption><.*?>No\.<.*?>(\d+?)<.*?>Solved<.*?>(\d+?)<\/a><.*?>Submit<.*?>(\d+?)<.*?>School:<.*?>(.*?)<\/tr><.*?>Email:<.*?>(.*?)<\/tr>/', $data, $match)) {
			$response['info']['name']=$match[1];
			$response['info']['id']=$match[2];
			$response['info']['totalcount']['solved']=$match[3];
			$response['info']['totalcount']['submit']=$match[4];
			$response['info']['school']=$match[5];
			$response['info']['email']=$match[6];
		}
		if (preg_match_all('/p\(('.$this->pattern.')\)/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='AC';
			}
		}
		(new cache)->write($this->ojid, $uid, $response);
		return $response;
	}
}
?>
