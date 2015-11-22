<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class bzoj {
	private $info=array(
		'id'=>'bzoj',
		'name'=>'大视野在线测评',
		'pattern'=>'[1-9]{1}[0-9]{3}',
		'url'=>'http://www.lydsy.com/JudgeOnline',
	);

	public function ojinfo() {
		return $this->info;
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
		$data=(new cache)->read($this->info['id'], $uid);
		if ($data!==false&&time()-$validtime<$data['timestamp']) return $data;
		$response=$data;
		$data=cURL_HTTP_Request('http://www.lydsy.com/JudgeOnline/userinfo.php?user='.$uid)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/<caption>.*?--(.+?)<\/caption><.*?>No\.<.*?>(\d+?)<.*?>Solved<.*?>(\d+?)<\/a><.*?>Submit<.*?>(\d+?)<.*?>School:<.*?>(.*?)<\/tr><.*?>Email:<.*?>(.*?)<\/tr>/', $data, $match)) {
			$response['info']['Nickname']=$match[1];
			$response['info']['No']=$match[2];
			$response['info']['Solved']=$match[3];
			$response['info']['Submit']=$match[4];
			$response['info']['School']=$match[5];
			$response['info']['Email']=$match[6];
		}
		if (preg_match_all('/p\(('.$this->info['pattern'].')\)/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='AC';
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>
