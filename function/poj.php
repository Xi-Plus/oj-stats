<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class poj {
	private $ojid='poj';
	private $name='PKU JudgeOnline';
	public $pattern="/^[1-9]{1}[0-9]{3}$/";
	private $url='http://poj.org';

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
		$data=cURL_HTTP_Request('http://poj.org/userstatus?user_id='.$uid)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/'.$uid.'--(.+?) <\/a>.*?Last Loginned Time:(.+?)<br>.*?Solved:<\/td>.*?>(\d+?)<\/a>.*?Submissions:<\/td>.*?>(\d+?)<\/a>.*?School:<\/td>.*?>(.+?) <\/td>.*?Email:<\/td>.*?>(.+?) <\/td>/', $data, $match)) {
			$response['info']['name']=$match[1];
			$response['info']['lastlogin']=$match[2];
			$response['info']['totalcount']['AC']=$match[3];
			$response['info']['totalcount']['Submission']=$match[4];
			$response['info']['school']=$match[5];
			$response['info']['email']=$match[6];
		}
		$data=cURL_HTTP_Request('http://poj.org/usercmp?uid1='.$uid.'&uid2='.$uid)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/Problems both.*?accepted(.+?)Problems only.*?tried but failed/', $data, $match)) {
			if (preg_match_all('/<a href.*?>(\d+?) <\/a>/', $match[1], $match2)) {
				foreach ($match2[1] as $pid) {
					$response['stat'][$pid]='AC';
				}
			}
		}
		if (preg_match('/Problems both.*?tried but failed(.+?)Home Page/', $data, $match)) {
			if (preg_match_all('/<a href.*?>(\d+?) <\/a>/', $match[1], $match2)) {
				foreach ($match2[1] as $pid) {
					$response['stat'][$pid]='NA';
				}
			}
		}
		(new cache)->write($this->ojid, $uid, $response);
		return $response;
	}
}
?>