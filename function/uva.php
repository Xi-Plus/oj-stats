<?php
require_once(__DIR__.'/../config/config.php');
require_once($config["curl_path"]);
require_once(__DIR__.'/global.php');
class uva {
	private $info=array(
		'id'=>'uva',
		'UVa Online Judge',
		'pattern'=>'[1-9]+[0-9]*',
		'url'=>'https://uva.onlinejudge.org'
	);
	private $api='http://uhunt.felix-halim.net/api/';

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
	
	private $verdictlist=array(
		10=>'SE',
		15=>'SE',
		20=>'Challenging',
		30=>'CE',
		35=>'RF',
		40=>'RE',
		45=>'OLE',
		50=>'TLE',
		60=>'MLE',
		70=>'WA',
		80=>'Unknown',
		90=>'AC'
	);
	
	private $verdictorder=array(
		'AC'=>0,
		'WA'=>1,
		'TLE'=>2,
		'MLE'=>3,
		'OLE'=>4,
		'RE'=>5,
		'RF'=>6,
		'CE'=>6,
		'SE'=>7,
		'Challenging'=>8,
		'Unknown'=>9
	);

	private function changestat($statold, $statnew){
		$statnew=$this->verdictlist[$statnew];
		if ($statold===null) return $statnew;
		if ($this->verdictorder[$statold]<$this->verdictorder[$statnew]) return $statold;
		return $statnew;
	}

	private function fetch($validtime, $uid) {
		$data=(new cache)->read($this->info['id'], $uid);
		if ($data!==false&&time()-$validtime<$data['timestamp']) return $data;
		$uid=cURL_HTTP_Request($this->api.'uname2uid/'.$uid);
		if ($uid===false) throw new Exception('User not found');
		$uid=$uid->html;
		$data=json_decode(cURL_HTTP_Request($this->api.'subs-user/'.$uid)->html,true);
		$response['info']['name']=$data['name'];
		foreach ($data['subs'] as $temp) {
			$pid=$temp[1];
			if (isset($response['stat'][$pid])) $this->changestat($response['stat'][$pid],$temp[2]);
			else $response['stat'][$pid]=$this->verdictlist[$temp[2]];
		}
		(new cache)->write($this->info['id'], $uid, $response);
		return $response;
	}
}
?>