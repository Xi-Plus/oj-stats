<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
class zj {
	private $name='ZeroJudge';
	private $pattern="/^[a-z]{1}[0-9]{3}$/";
	private $url='http://zerojudge.tw';

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

	public function checkpid($prob){
		foreach ($prob as $pid) {
			if (!preg_match($this->pattern, $pid)) throw new Exception('Prob ('.$pid.') not match pattern ('.$this->pattern.')');
		}
	}

	private function login() {
		$data=cURL_HTTP_Request('http://zerojudge.tw/UserStatistic',null,false,true);
		if ($data===false) {
			$data=cURL_HTTP_Request('http://zerojudge.tw/Login',null,false,true)->html;
			if (preg_match('/name="token" value="([^"]+)/',$data,$res)) {
                $token=$res[1];
                $data=cURL_HTTP_Request('http://zerojudge.tw/Login',array('account'=>'tester123123','passwd'=>'123123','returnPage'=>'/','token'=>$token),false,true);
                if ($data===false) {
                	throw new Exception('Zerojudge login fail');
                }
            } else {
            	throw new Exception('Zerojudge login fail');
            }
		}
	}

	public function fetch($validtime, $uid) {
		$this->login();
		$data=$this->read($uid);
		if ($data!==false&&$validtime<$data['timestamp']) return $data;
		$response=$data;
		$data=cURL_HTTP_Request("http://zerojudge.tw/UserStatistic?account=".$uid,null,false,true)->html;
		$data=str_replace(array("\r\n"),"",$data);
		$data=str_replace(array("\t")," ",$data);
		$count=1;
		while ($count) {
			$data=str_replace(array("  ")," ",$data,$count);
		}
		if (preg_match('/編號 : (\d+)<.*?姓名 : (.+?)<br \/> 學校 : (.+?) <!--.*?來源 : \[(.+?)\] <br \/> 最後登入時間 ：<br \/> (.+?) <br \/>.*?共通過.*?>(\d+?)<\/a> 題.*?錯誤.*?>(\d+?)<\/a> 次 <br \/> 逾時.*?>(\d+?)<\/a> 次 <br \/> 記憶體過量.*?>(\d+?)<\/a> 次 <br \/> 輸出檔過大.*?>(\d+?)<\/a> 次 <br \/> 執行錯誤.*?>(\d+?)<\/a> 次 <br \/> 編譯錯誤.*?>(\d+?)<\/a> 次/', $data, $match)) {
			$response['info']['id']=$match[1];
			$response['info']['name']=$match[2];
			$response['info']['school']=$match[3];
			$response['info']['ip']=$match[4];
			$response['info']['lastlogin']=$match[5];
			$response['info']['totalcount']['AC']=$match[6];
			$response['info']['totalcount']['WA']=$match[7];
			$response['info']['totalcount']['TLE']=$match[8];
			$response['info']['totalcount']['MLE']=$match[9];
			$response['info']['totalcount']['OLE']=$match[10];
			$response['info']['totalcount']['RE']=$match[11];
			$response['info']['totalcount']['CE']=$match[12];
		}
		if (preg_match_all('/class="acstyle" .*?>(.+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='AC';
			}
		}
		if (preg_match_all('/style="color: #666666; font-weight: bold;".*?>(.+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='NA';
			}
		}
		if (preg_match_all('/style="color: #666666".*?>(.+?)<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='';
			}
		}
		$this->save($uid, $response);
		return $response;
	}

	private function save($uid, $data) {
		$data['timestamp']=time();
		file_put_contents(__DIR__.'/../cache/zj_'.$uid.'.dat', json_encode($data));
	}

	private function read($uid) {
		$data=@file_get_contents(__DIR__.'/../cache/zj_'.$uid.'.dat');
		if($data===false)return false;
		$data=json_decode($data, true);
		return $data;
	}
}
?>