<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class tzj {
	private $info=array(
		'id'=>'tzj',
		'Online Judge System For TNFSH',
		'pattern'=>'[a-z]{1}[0-9]{3}',
		'url'=>'http://judge.tnfsh.tn.edu.tw:8080'
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
		$response=array('info'=>null, 'stat'=>null);
		$data=cURL_HTTP_Request("http://judge.tnfsh.tn.edu.tw:8080/ShowUserStatistic?account=".$uid)->html;
		$data=str_replace(array("\n"),"",$data);
		$data=str_replace(array("\t")," ",$data);
		$count=1;
		while ($count) {
			$data=str_replace(array("  ")," ",$data,$count);
		}
		if (preg_match('/編號:<.*?>(\d+?)<.*?>姓名:<\/div><\/td> <td><.*?>(.+?)<.*?>級數-班級: <.*?> (\d+?)-(\d+?) <.*?>來源:<\/div><\/td> <td><.*?>(.+?) <.*?>分數:<.*?>(\d+?)<.*?> 最後登入時間：<br \/> (.+?) <br.*?AC.*?>(\d+?)<.*?WA.*?>(\d+?)<.*?TLE.*?>(\d+?)<.*?MLE.*?>(\d+?)<.*?OLE.*?>(\d+?)<.*?RE.*?>(\d+?)<.*?CE.*?>(\d+?)<.*?>總共傳送 <.*?>(\d+?)<.*?> 目前排名：<.*?>(\d+?)<\/a>/', $data, $match)) {
			$response['info']['ID']=$match[1];
			$response['info']['User name']=$match[2];
			$response['info']['Grade']=$match[3];
			$response['info']['Class']=$match[4];
			$response['info']['IP address']=$match[5];
			$response['info']['Rank Point']=$match[6];
			$response['info']['Last Login']=$match[7];
			$response['info']['AC']=$match[8];
			$response['info']['WA']=$match[9];
			$response['info']['TLE']=$match[10];
			$response['info']['MLE']=$match[11];
			$response['info']['OLE']=$match[12];
			$response['info']['RE']=$match[13];
			$response['info']['CE']=$match[14];
			$response['info']['Total submit']=$match[15];
			$response['info']['Rank']=$match[16];
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
