<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class tzj {
	private $ojid='tzj';
	private $name='Online Judge System For TNFSH';
	public $pattern='[a-z]{1}[0-9]{3}';
	private $url='http://judge.tnfsh.tn.edu.tw:8080';

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
		$data=cURL_HTTP_Request("http://judge.tnfsh.tn.edu.tw:8080/ShowUserStatistic?account=".$uid)->html;
		$data=str_replace(array("\n"),"",$data);
		$data=str_replace(array("\t")," ",$data);
		$count=1;
		while ($count) {
			$data=str_replace(array("  ")," ",$data,$count);
		}
		if (preg_match('/編號:<.*?>(\d+?)<.*?>姓名:<\/div><\/td> <td><.*?>(.+?)<.*?>級數-班級: <.*?> (\d+?)-(\d+?) <.*?>來源:<\/div><\/td> <td><.*?>(.+?) <.*?>分數:<.*?>(\d+?)<.*?> 最後登入時間：<br \/> (.+?) <br.*?AC.*?>(\d+?)<.*?WA.*?>(\d+?)<.*?TLE.*?>(\d+?)<.*?MLE.*?>(\d+?)<.*?OLE.*?>(\d+?)<.*?RE.*?>(\d+?)<.*?CE.*?>(\d+?)<.*?>總共傳送 <.*?>(\d+?)<.*?> 目前排名：<.*?>(\d+?)<\/a>/', $data, $match)) {
			$response['info']['id']=$match[1];
			$response['info']['name']=$match[2];
			$response['info']['grade']=$match[3];
			$response['info']['class']=$match[4];
			$response['info']['ip']=$match[5];
			$response['info']['totalcount']['score']=$match[6];
			$response['info']['lastlogin']=$match[7];
			$response['info']['totalcount']['AC']=$match[8];
			$response['info']['totalcount']['WA']=$match[9];
			$response['info']['totalcount']['TLE']=$match[10];
			$response['info']['totalcount']['MLE']=$match[11];
			$response['info']['totalcount']['OLE']=$match[12];
			$response['info']['totalcount']['RE']=$match[13];
			$response['info']['totalcount']['CE']=$match[14];
			$response['info']['totalcount']['submit']=$match[15];
			$response['info']['rank']=$match[16];
		}
		if (preg_match_all('/<a.*?id="acstyle".*?>('.$this->pattern.')<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='AC';
			}
		}
		if (preg_match_all('/<a.*?style="color: #666666; font-weight: bold;".*?>('.$this->pattern.')<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='NA';
			}
		}
		(new cache)->write($this->ojid, $uid, $response);
		return $response;
	}
}
?>
