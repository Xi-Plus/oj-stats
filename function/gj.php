<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class gj {
	private $info=array(
		'id'=>'gj',
		'name'=>'Green Judge, An Online Judge System for TCGS',
		'pattern'=>'[a-z]{1}[0-9]{3}',
		'url'=>'http://www.tcgs.tc.edu.tw:1218'
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
		$data=cURL_HTTP_Request("http://www.tcgs.tc.edu.tw:1218/ShowUserStatistic?account=".$uid)->html;
		$data=str_replace(array("\n"),"",$data);
		$data=str_replace(array("\t")," ",$data);
		$count=1;
		while ($count) {
			$data=str_replace(array("  ")," ",$data,$count);
		}
		if (preg_match('/ID:<.*?>(\d+?)<.*?>User name:<\/td> <td align="left">(.+?)<.*?>School:<\/td> <td> (.+?) <.*?>IP address:<\/td> <td>(.+?)<.*?>Rank Point:<.*?>(\d+?)<.*?> Last Login：<br \/> (.+?) <.*?> AC <.*?>(\d+?)<.*?> 不通過\(NA\) <.*?>(\d+?)<.*? WA <.*?>(\d+?)<.*?TLE <.*?>(\d+?)<.*? MLE <.*?>(\d+?)<.*? OLE <.*?>(\d+?)<.*? RE <.*?">(\d+?)<.*? CE <.*?>(\d+?)<.*?Total submit <.*?>(\d+?)<.*? Rank：<.*?>(\d+?)<\/a>/', $data, $match)) {
			$response['info']['id']=$match[1];
			$response['info']['name']=$match[2];
			$response['info']['school']=$match[3];
			$response['info']['ip']=$match[4];
			$response['info']['totalcount']['score']=$match[5];
			$response['info']['lastlogin']=$match[6];
			$response['info']['totalcount']['AC']=$match[7];
			$response['info']['totalcount']['NA']=$match[8];
			$response['info']['totalcount']['WA']=$match[9];
			$response['info']['totalcount']['TLE']=$match[10];
			$response['info']['totalcount']['MLE']=$match[11];
			$response['info']['totalcount']['OLE']=$match[12];
			$response['info']['totalcount']['RE']=$match[13];
			$response['info']['totalcount']['CE']=$match[14];
			$response['info']['totalcount']['submit']=$match[15];
			$response['info']['rank']=$match[16];
		}
		if (preg_match_all('/<a.*?id="acstyle".*?>('.$this->info['pattern'].')<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='AC';
			}
		}
		if (preg_match_all('/<a.*?style="color: #666666; font-weight: bold;".*?>('.$this->info['pattern'].')<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='NA';
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>
