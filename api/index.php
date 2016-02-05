<?php
require_once(__DIR__.'/../config/config.php');
require_once(__DIR__.'/../function/fetch/global.php');

foreach ($_REQUEST as $index => $temp) {
	$_REQUEST[$index]=urldecode($_REQUEST[$index]);
}

try {
	if (!isset($_REQUEST['oj'])) {
		throw new Exception('No given oj');
	}
	if (!isset($config['available_oj'][$_REQUEST['oj']])) {
		throw new Exception('Unsupported oj');
	}
	if (!isset($_REQUEST['node'])) {
		throw new Exception('Not given node');
	}

	require(__DIR__.'/../function/fetch/'.$config['available_oj'][$_REQUEST['oj']]);
	$oj=new $_REQUEST['oj']();

	if (isset($_REQUEST['validtime'])) {
		if (is_numeric($_REQUEST['validtime'])) {
			$validtime=$_REQUEST['validtime'];
		} else {
			throw new Exception('validtime is not a number');
		}
	} else {
		$validtime=3600;
	}
	if (isset($_REQUEST['user'])) {
		$userlist=json_decode($_REQUEST['user']);
		if (!is_array($userlist)) throw new Exception('User is not array');
		if (count($userlist)===0) throw new Exception('User is an empty array');
	}
	if (isset($_REQUEST['prob'])) {
		$problist=json_decode($_REQUEST['prob']);
		if (!is_array($problist)) throw new Exception('Prob is not array');
		if (count($problist)===0) throw new Exception('Prob is an empty array');
		foreach ($problist as $pid) {
			if (!preg_match('/^'.$oj->ojinfo()['pattern'].'$/', $pid)){
				throw new Exception('Prob ('.$pid.') not match pattern ('.$oj->ojinfo()['pattern'].')');
			}
		}
	}
	if (isset($_REQUEST['field'])) {
		$fieldlist=json_decode($_REQUEST['field']);
		if (!is_array($fieldlist)) throw new Exception('Field is not array');
		if (count($fieldlist)===0) throw new Exception('Field is an empty array');
	}

	$response=array();
	if ($_REQUEST['node']=='ojinfo') {
		$response=$oj->ojinfo();
	} else if ($_REQUEST['node']=='userinfo') {
		if (!isset($_REQUEST['user'])) throw new Exception('Not given user');
		$data=$oj->userinfo($validtime, $userlist);
		foreach ($data as $uid => $user) {
			$response[$uid]=array();
			foreach ($user as $field => $value) {
				if (isset($_REQUEST['field'])&&!in_array($field, $fieldlist)) {
				} else {
					$response[$uid][$field]=$data[$uid][$field];
				}
			}
			if (isset($_REQUEST['field'])&&!in_array('link', $fieldlist)) {
			} else {
				$response[$uid]['link']=$oj->userlink($uid);
			}
		}
	} else if($_REQUEST['node']=='userstat'){
		$data=$oj->userstat($validtime, $userlist);
		foreach ($data as $uid => $user) {
			$response[$uid]=array();
			if (isset($_REQUEST['prob'])) {
				foreach ($problist as $pid) {
					$response[$uid][$pid]['status']='';
				}
				foreach ($response[$uid] as $pid => $temp) {
					if (isset($data[$uid][$pid])) {
						foreach ($data[$uid][$pid] as $field => $value) {
							if (isset($_REQUEST['field'])&&!in_array($field, $fieldlist)) {
							} else  {
								$response[$uid][$pid][$field]=$data[$uid][$pid][$field];
							}
						}
					}
					if (isset($_REQUEST['field'])&&!in_array('link', $fieldlist)) {
					} else  {
						$response[$uid][$pid]['link']=$oj->statuslink($uid, $pid);
					}
				}
			} else {
				foreach ($response as $uid => $temp) {
					foreach ($data[$uid] as $pid => $temp2) {
						$response[$uid][$pid]=$data[$uid][$pid]["status"];
						foreach ($data[$uid][$pid] as $field => $value) {
							if (isset($_REQUEST['field'])&&!in_array($field, $fieldlist)) {
							} else  {
								$response[$uid][$pid][$field]=$data[$uid][$pid][$field];
							}
						}
					}
					if (isset($_REQUEST['field'])&&!in_array('link', $fieldlist)) {
					} else  {
						$response[$uid][$pid]['link']=$oj->statuslink($uid, $pid);
					}
				}
			}
		}
	} else if ($_REQUEST['node']=='probinfo') {
		foreach ($problist as $pid) {
			$response[$pid]['link']=$oj->problink($pid);
		}
	} else {
		throw new Exception('Unknown node');
	}
} catch (Exception $e) {
	$response['error']=$e->getMessage();
}
echo json_encode($response);
?>