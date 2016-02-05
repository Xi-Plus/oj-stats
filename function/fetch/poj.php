<?php
require_once(__DIR__.'/../../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class poj {
	private $info=array(
		'id'=>'poj',
		'name'=>'PKU JudgeOnline',
		'pattern'=>'[1-9]{1}[0-9]{3}',
		'url'=>'http://poj.org'
	);

	public function ojinfo() {
		return $this->info;
	}

	public function problink($pid) {
		return 'http://poj.org/problem?id='.$pid;
	}

	public function userlink($uid) {
		return 'http://poj.org/userstatus?user_id='.$uid;
	}

	public function statuslink($uid, $pid) {
		return 'http://poj.org/status?problem_id='.$pid.'&user_id='.$uid;
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
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/'.$uid.'--(.+?)  <\/a>.*?Last Loginned Time:(.+?)<br>.*?Solved:<\/td>.*?>(\d+?)<\/a>.*?Submissions:<\/td>.*?>(\d+?)<\/a>.*?School:<\/td>.*?>(.+?) <\/td>.*?Email:<\/td>.*?>(.+?) <\/td>/', $data, $match)) {
			$response['info']['Nickname']=$match[1];
			$response['info']['Last Loginned Time']=$match[2];
			$response['info']['Solved']=$match[3];
			$response['info']['Submissions']=$match[4];
			$response['info']['School']=$match[5];
			$response['info']['Email']=$match[6];
		}
		$data=cURL_HTTP_Request('http://poj.org/usercmp?uid1='.$uid.'&uid2='.$uid)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/Problems both.*?accepted(.+?)Problems only.*?tried but failed/', $data, $match)) {
			if (preg_match_all('/<a href.*?>('.$this->info['pattern'].') <\/a>/', $match[1], $match2)) {
				foreach ($match2[1] as $pid) {
					$response['stat'][$pid]['status']='AC';
				}
			}
		}
		if (preg_match('/Problems both.*?tried but failed(.+?)Home Page/', $data, $match)) {
			if (preg_match_all('/<a href.*?>('.$this->info['pattern'].') <\/a>/', $match[1], $match2)) {
				foreach ($match2[1] as $pid) {
					$response['stat'][$pid]['status']='NA';
				}
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>