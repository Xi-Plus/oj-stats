<?php
require_once(__DIR__.'/../../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class zj {
	private $info=array(
		'id'=>'zj',
		'name'=>'ZeroJudge',
		'pattern'=>'[a-z]{1}[0-9]{3}',
		'url'=>'http://zerojudge.tw'
	);
	private $cookiefile='zj_cookie.txt';

	public function ojinfo() {
		return $this->info;
	}

	public function problink($pid) {
		return 'http://zerojudge.tw/ShowProblem?problemid='.$pid;
	}

	public function userlink($uid) {
		return 'http://zerojudge.tw/UserStatistic?account='.$uid;
	}

	public function statuslink($uid, $pid) {
		return 'http://zerojudge.tw/Submissions?problemid='.$pid.'&account='.$uid;
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

	private function login() {
		global $config;
		$data=cURL_HTTP_Request('http://zerojudge.tw/UserStatistic',null,false,$this->cookiefile);
		if ($data===false) {
			$data=cURL_HTTP_Request('http://zerojudge.tw/Login',null,false,$this->cookiefile)->html;
			if (preg_match('/name="token" value="([^"]+)/',$data,$res)) {
                $token=$res[1];
                $data=cURL_HTTP_Request('http://zerojudge.tw/Login',array('account'=>$config['login']['zj']['acct'],'passwd'=>$config['login']['zj']['pass'],'returnPage'=>'/','token'=>$token),false,$this->cookiefile);
                if ($data===false) {
                	throw new Exception('Zerojudge login fail');
                }
            } else {
            	throw new Exception('Zerojudge login fail');
            }
		}
	}

	private function fetch($validtime, $uid) {
		$this->login();
		$data=(new cache)->read($this->info['id'], $uid);
		if ($data!==false&&time()-$validtime<$data['timestamp']) return $data;
		$response=array('info'=>array(), 'stat'=>array());
		$data=cURL_HTTP_Request($this->userlink($uid),null,false,$this->cookiefile)->html;
		$data=str_replace(array("\r\n"),"",$data);
		$data=str_replace(array("\t")," ",$data);
		$count=1;
		while ($count) {
			$data=str_replace(array("  ")," ",$data,$count);
		}
		if (preg_match('/編號 : (\d+)<.*?姓名 : (.+?)<br \/> 學校 : (.+?) <!--.*?來源 : \[(.+?)\] <br \/> 最後登入時間 ：<br \/> (.+?) <br \/>.*?共通過.*?>(\d+?)<\/a> 題.*?錯誤.*?>(\d+?)<\/a> 次 <br \/> 逾時.*?>(\d+?)<\/a> 次 <br \/> 記憶體過量.*?>(\d+?)<\/a> 次 <br \/> 輸出檔過大.*?>(\d+?)<\/a> 次 <br \/> 執行錯誤.*?>(\d+?)<\/a> 次 <br \/> 編譯錯誤.*?>(\d+?)<\/a> 次/', $data, $match)) {
			$response['info']['ID']=$match[1];
			$response['info']['User name']=$match[2];
			$response['info']['School']=$match[3];
			$response['info']['IP address']=$match[4];
			$response['info']['Last Login']=$match[5];
			$response['info']['AC']=$match[6];
			$response['info']['WA']=$match[7];
			$response['info']['TLE']=$match[8];
			$response['info']['MLE']=$match[9];
			$response['info']['OLE']=$match[10];
			$response['info']['RE']=$match[11];
			$response['info']['CE']=$match[12];
		}
		if (preg_match_all('/class="acstyle" .*?>(.+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]['status']='AC';
			}
		}
		if (preg_match_all('/style="color: #666666; font-weight: bold;".*?>(.+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]['status']='NA';
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>