<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class hdu {
	private $info=array(
		'id'=>'hdu',
		'HDU Online Judge',
		'pattern'=>'[1-9]{1}[0-9]{3}',
		'url'=>'http://acm.hdu.edu.cn'
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
		$data=cURL_HTTP_Request('http://acm.hdu.edu.cn/userstatus.php?user='.$uid)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/<h1.*?>(.+?)<\/h1>.*?>from: (.+?)&nbsp;.*?registered on (.+?)<.*?>Rank<.*?>(\d+?)<.*?>Problems Submitted<.*?>(\d+?)<.*?>Problems Solved<.*?>(\d+?)<.*?>Submissions<.*?>(\d+?)<.*?>Accepted<.*?>(\d+?)<\/td>/', $data, $match)) {
			$response['info']['Nickname']=$match[1];
			$response['info']['from']=$match[2];
			$response['info']['registered on']=$match[3];
			$response['info']['Rank']=$match[4];
			$response['info']['Problems Submitted']=$match[5];
			$response['info']['Problems Solved']=$match[6];
			$response['info']['Submissions']=$match[7];
			$response['info']['Accepted']=$match[8];
		}
		if (preg_match('/List of solved problems(.+?)List of unsolved problems/', $data, $match)) {
			if (preg_match_all('/p\(('.$this->info['pattern'].'),\d+,\d+\)/', $match[1], $match2)) {
				foreach ($match2[1] as $pid) {
					$response['stat'][$pid]='AC';
				}
			}
		}
		if (preg_match('/List of unsolved problems(.+?)Neighbours/', $data, $match)) {
			if (preg_match_all('/p\(('.$this->info['pattern'].'),\d+,\d+\)/', $match[1], $match2)) {
				foreach ($match2[1] as $pid) {
					$response['stat'][$pid]='NA';
				}
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>
