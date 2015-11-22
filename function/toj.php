<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class toj {
	private $info=array(
		'id'=>'toj',
		'name'=>'TNFSH Online Judge',
		'pattern'=>'[1-9]{1}[0-9]*',
		'url'=>'http://toj.tfcis.org',
	);
	private $api='http://toj.tfcis.org/oj/be/api';

	public function ojinfo() {
		return $this->info;
	}

	public function problink($pid) {
		return 'http://toj.tfcis.org/oj/pro/'.$pid.'/';
	}

	public function userlink($uid) {
		return 'http://toj.tfcis.org/oj/acct/'.$uid.'/';
	}

	public function statuslink($uid, $pid) {
		return 'http://toj.tfcis.org/oj/chal/?proid='.$pid.'&acctid='.$uid;
	}

	public function userinfo($validtime, $users) {
		foreach ($users as $uid) {
			$response[$uid]=$this->fetch($validtime, $uid)['info'];
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
		if($data!==false&&time()-$validtime<$data['timestamp'])return $data;
		$data=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'INFO','acct_id'=>$uid))->html,true);
		$response['info']=$data;
		$data=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'AC','acct_id'=>$uid))->html,true)['ac'];
		foreach ($data as $pid) {
			$response['stat'][$pid]['status']='AC';
		}
		$data=json_decode(cURL_HTTP_Request($this->api,array('reqtype'=>'NA','acct_id'=>$uid))->html,true)['na'];
		foreach ($data as $pid) {
			$response['stat'][$pid]['status']='NA';
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>