<?php
require_once(__DIR__.'/../config/config.php');
$response=new stdClass;
try {
	if (!isset($_REQUEST['oj'])) {
		throw new Exception('No given oj');
	}
	if (!isset($config['available_oj'][$_REQUEST['oj']])) {
		throw new Exception('Unsupported oj');
	}
	if (!isset($_REQUEST['field'])) {
		throw new Exception('Not given field');
	}

	require(__DIR__.'/../function/'.$config['available_oj'][$_REQUEST['oj']]);
	$oj=new $_REQUEST['oj']();

	if ($_REQUEST['field']=='ojinfo') {
		$response=$oj->ojinfo();
	} else if ($_REQUEST['field']=='userinfo') {
		if (!is_array(json_decode($_REQUEST['user']))) {
			throw new Exception('User is not array');
		}
		$response=$oj->userinfo(json_decode($_REQUEST['user']));
	} else if($_REQUEST['field']=='userstat'){
		if (!is_array(json_decode($_REQUEST['user']))) {
			throw new Exception('User is not array');
		}

		if(isset($_REQUEST['validtime'])&&is_numeric($_REQUEST['validtime']))$validtime=$_REQUEST['validtime'];
		else $validtime=time()-3600;
		
		if (isset($_REQUEST['prob'])) {
			if (!is_array(json_decode($_REQUEST['prob']))) {
				throw new Exception('Prob is not array');
			}
			$response=$oj->userstat($validtime, json_decode($_REQUEST['user']), json_decode($_REQUEST['prob']));
		} else {
			$response=$oj->userstat($validtime, json_decode($_REQUEST['user']));
		}
	} else {
		throw new Exception('Unknown field');
	}
} catch (Exception $e) {
	$response->error = $e->getMessage();
}
echo json_encode($response);
?>