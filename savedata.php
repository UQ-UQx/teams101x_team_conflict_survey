<?php
	header('Content-Type: application/json');
	require_once('config.php');
	require_once('lib/lti.php');
	require_once('lib/grade.php');

	$lti = new Lti($config,true);
	if(isset($config['use_db']) && $config['use_db']) {
		require_once('lib/db.php');
		Db::config( 'driver',   'mysql' );
		Db::config( 'host',     $config['db']['hostname'] );
		Db::config( 'database', $config['db']['dbname'] );
		Db::config( 'user',     $config['db']['username'] );
		Db::config( 'password', $config['db']['password'] );
	}
		$vars = array('user_id'=>$_POST['user_id'],'oauth_consumer_key'=>$_POST['lti_id'], 'lis_outcome_service_url'=>$_POST['lis_outcome_service_url'], 'lis_result_sourcedid'=>$_POST['lis_result_sourcedid']);
	$lti->setltivars($vars);
	require_once('model.php');

	
	$response = $_POST['data'];
	$sid = $_POST['sid'];
	
	$params = array();
	parse_str($_POST['form'], $params);
	
	$score = count($params)/32;
	
	send_grade($score,$lti);
	
	setResponse($sid, $response);
	

	echo '{"status":"'.$sid.'"}';
?>