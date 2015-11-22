<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class tioj {
	private $info=array(
		'id'=>'tioj',
		'TIOJ Infor Online Judge',
		'pattern'=>'[1-9]{1}[0-9]{3}',
		'url'=>'http://tioj.ck.tp.edu.tw'
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
		$data=cURL_HTTP_Request("http://tioj.ck.tp.edu.tw/users/".$uid)->html;
		$data=str_replace(array("\n","\t"),"",$data);
		if (preg_match('/<h5>(.+?)<\/h5> <h6>.*?AC Ratio: <\/div> <div class="col-md-5"> (\d+?)<br> (\d+?)<br> (.+?)%.*?Signed up at: (.+?)<br> Last sign in: (.+?)<br>/', $data, $match)) {
			$response['info']['name']=$match[1];
			$response['info']['totalcount']['AC']=$match[2];
			$response['info']['totalcount']['NA']=$match[3];
			$response['info']['totalcount']['Ratio']=$match[4];
			$response['info']['signup']=$match[5];
			$response['info']['lastlogin']=$match[6];
		}
		if (preg_match_all('/<a class="text-success".*?>('.$this->info['pattern'].')<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='AC';
			}
		}
		if (preg_match_all('/<a class="text-warning".*?>('.$this->info['pattern'].')<\/a>/', $data, $match)) {
			foreach ($match[1] as $pid) {
				$response['stat'][$pid]='NA';
			}
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>
