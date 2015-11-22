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

	public function problink($pid) {
		return 'http://www.tcgs.tc.edu.tw:1218/ShowProblem?problemid='.$pid;
	}

	public function userlink($uid) {
		return 'http://www.tcgs.tc.edu.tw:1218/ShowUserStatistic?account='.$uid;
	}

	public function statuslink($uid, $pid) {
		return 'http://www.tcgs.tc.edu.tw:1218/RealtimeStatus?problemid='.$pid.'&account='.$uid;
	}

	public function userinfo($validtime, $users) {
		foreach ($users as $uid) {
			$response[$uid]=$this->fetch($validtime, $uid)['info'];
		}
		return $response;
	}

	public function userstat($validtime, $users, $probs=NULL) {
		foreach ($users as $uid) {
			$response[$uid]=$this->fetch($validtime, $uid)['stat'];
		}
		return $response;
	}

	private function fetch($validtime, $uid) {
		$data=(new cache)->read($this->info['id'], $uid);
		if ($data!==false&&time()-$validtime<$data['timestamp']) return $data;
		$response=array('info'=>array(), 'stat'=>array());
		$data=cURL_HTTP_Request("http://www.tcgs.tc.edu.tw:1218/ShowUserStatistic?account=".$uid)->html;
		$data=str_replace(array("\n"),"",$data);
		$data=str_replace(array("\t")," ",$data);
		$count=1;
		while ($count) {
			$data=str_replace(array("  ")," ",$data,$count);
		}
		if (preg_match('/ID:<.*?>(\d+?)<.*?>User name:<\/td> <td align="left">(.+?)<.*?>School:<\/td> <td> (.+?) <.*?>IP address:<\/td> <td>(.+?)<.*?>Rank Point:<.*?>(\d+?)<.*?> Last Login：<br \/> (.+?) <.*?> AC <.*?>(\d+?)<.*?> 不通過\(NA\) <.*?>(\d+?)<.*? WA <.*?>(\d+?)<.*?TLE <.*?>(\d+?)<.*? MLE <.*?>(\d+?)<.*? OLE <.*?>(\d+?)<.*? RE <.*?">(\d+?)<.*? CE <.*?>(\d+?)<.*?Total submit <.*?>(\d+?)<.*? Rank：<.*?>(\d+?)<\/a>/', $data, $match)) {
			$response['info']['ID']=$match[1];
			$response['info']['User name']=$match[2];
			$response['info']['School']=$match[3];
			$response['info']['IP address']=$match[4];
			$response['info']['Rank Point']=$match[5];
			$response['info']['Last Login']=$match[6];
			$response['info']['AC']=$match[7];
			$response['info']['NA']=$match[8];
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
				$response['stat'][$pid]['status']='AC';
			}
		}
		if (preg_match_all('/<a.*?style="color: #666666; font-weight: bold;".*?>('.$this->info['pattern'].')<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]['status']='NA';
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>
