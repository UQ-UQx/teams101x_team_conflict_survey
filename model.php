<?php
	
	
	function setResponse($sid, $response){
		global $lti;
		$db = Db::instance();
		date_default_timezone_set('Australia/Brisbane');
		$modified = date('Y-m-d H:i:s');
		$existing = getResponse($sid);
		error_log($lti->user_id());
		if(!$existing) {
			$db->create('responses', array('survey_id'=>$sid,'user_id'=>$lti->user_id(), 'response'=>$response,'created'=>$modified,'updated'=>$modified));
		} else {
 			$db->update('responses', array('response'=>$response,'updated'=>$modified), $sid, 'survey_id');
		}
	}
	
	function getResponse($sid){
		global $lti;
		$db = Db::instance();
		$select = $db->query( 'SELECT * FROM responses WHERE survey_id = :survey_id', array( 'survey_id' => $sid ) );
		while ( $row = $select->fetch() ) {
			return $row;
		}
		return null;
	}
	

	
?>