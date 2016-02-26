<?php require_once('inc/header.php');
if($lti->is_valid()) {
			echo '<p>LTI Valid, Dev Version - DO NOT USE IN COURSES - contact UQx Technical Team</p>';
		} else {
			echo '<p>LTI Invalid - contact UQx Technical Team</p>';
			die();
		}
		
		
		
//require_once('savedata.php');
		
?>

<style>
	

	body{
		
	}
	
	
	
	
	.radio-inline{
		
		margin-right:60px;
		margin-bottom:50px;
		
	}
	
	
</style>



</head> 
<body>
		<?php

	
		//get all the variables from the LTI post
		//user class for getting constants and calldata to customs
		
		$view = '';
		
		// options:
		/**
			complete: full score and explanation
			simple: just the score
			none: no score
			
			*/
		$showscore = 'complete';
		
		$ltivars = $lti->calldata();
		
		//get lti id first as defined by edx
		
		$lti_id = $lti->resource_id();
		
		//open a container for a previous survey id;
		$previous_survey_id = null;
		
		//check to see if custom_survey_id is set, if so then the survey is sending data and NOT recieveing anything and the lti id should be set to the custom one
		
		if(isset($ltivars{'custom_survey_id'})){
			//set id to custom 
			$lti_id = $ltivars{'custom_survey_id'};
		}elseif(isset($ltivars{'custom_pre_survey_id'})){
			
			$previous_survey_id = $ltivars{'custom_pre_survey_id'}.'-lti-uid-'.$lti->user_id();
		}
		
		if(isset($ltivars{'custom_view'})){
			$view = $ltivars{'custom_view'};
			
		}
		
		if(isset($ltivars{'custom_showscore'})){
			$showscore = $ltivars{'custom_showscore'};
		}
		
		
				
		
		//construct survey id, by combining lti id and user id
		
		$sid = $lti_id.'-lti-uid-'.$lti->user_id();

		
		//define scale of the survey and questions
		
		$scale = 4;
		$questions = array(	
		
						'Question 1. I offer information and opinions', 
						'Question 2. I summarise what is happening in the group.', 
						'Question 3. When there is a problem I try to identify what is happening.',
						'Question 4. I start the group working.',
						'Question 5. I suggest directions the group can take.',
						'Question 6. I listen actively.',
						'Question 7. I give positive feedback to other members of the group.',
						'Question 8. I compromise.',
						'Question 9. I help relieve tension.',
						'Question 10. I talk.',
						'Question 11. I ensure that meeting times and places are arranged.',
						'Question 12. I try to observe what is happening in the group.',
						'Question 13. I try to help solve problems.',
						'Question 14. I take responsibility for ensuring that tasks are completed.',
						'Question 15. I like the group to be having a good time.'
					);

		
	?>



<?php
		if($view === 'horizontal'){
			echo "<div class='questions_pagination_container'><ul class='questions_pagination'></ul></div><div class='currentpage_status' ></div>";
		}
?>





<form id="questionnaire_form" action="javascript:void(0);" method="POST">
	

	

	<?php
		if($view === 'horizontal'){
			echo '<div class="page_container"><div class="page_scroller">';
		}
				
		
		//check to see if the user has already submitted answers for the inputs and the status
		require_once('model.php');
		$qrespone = getResponse($sid);
		
		$json_response = null;
		$currentresponsestatus = 'unfinished';		
				
		if($qrespone){
			$json_response = json_decode($qrespone->response);
			$currentresponsestatus= $json_response->{'status'};
		}
		//variable to count how many questions have been attempted 
		$numOfAttempted = 0;
		$finished = false;
		
		
		
		echo '<div class="questions_container">';
		
		//for each of the question above contruct the questions and options
		foreach($questions as $questionnum=>$question){
			$questionnum = $questionnum +1;
			$response = null;

		
			if($view === 'horizontal'){
				echo '<div class="question_page">';
			}
		
			echo '<div id="question'.$questionnum.'_container" class="question_container"><h5>'.$question.'</h5>';

			if($json_response){
				$response = $json_response->{'question'.$questionnum};
				
			}
			
			for($i=0; $i<$scale; $i++){
				$scalenum = $i+1;
				$checked = '';
				
				

				if($response == $scalenum){
					$checked = 'checked';
					$attempted = true;
					$numOfAttempted++;
				}	
				
				
				echo '
					<div class="input_container "><label class="radio-inline"><input type="radio" name="question'.$questionnum.'" class="question_input" id="question'.$questionnum.'_option_'.$scalenum.'" value="'.$scalenum.'" data-question_number="'.$questionnum.'" data-question_id="question'.$questionnum.'" data-option_num="'.$scalenum.'" '.$checked.'>'.$i.'</label></div>
					
				
				';
			}
			echo '</div>'; // question_container div close
			if($view === 'horizontal'){
				echo '</div>'; // question_page div close
			}
			
			if($numOfAttempted == count($questions)){
				$finished = true;
			}

		}
		
		echo '</div>'; // questions_container div close

		
		
		//Getting previous response if it exists and form needs to show it.
		$pre_qresponse = null;
		$pre_jsonresponse = null;
		$pre_responsestatus = 'unfinished';
		$pre_qresponse_showanswer = null;

		if($previous_survey_id){
			$pre_qresponse = getResponse($previous_survey_id);
		}
		
		if($pre_qresponse){
			$pre_jsonresponse = json_decode($pre_qresponse->response);
			$pre_responsestatus = $pre_jsonresponse->{'status'};
			$pre_qresponse_showanswer = $ltivars{'custom_showprevious'};
		}


		if($view === 'horizontal'){

			echo '</div></div>'; //page_container and page_scroller close

		}


	?>
     <button id='submitButton' type="submit" class="btn btn-primary btn-md">Submit</button>     <button id='feedbackButton' class="btn btn-default btn-md">Show Feedback</button>

</form>

<div class='score_text'>
	<div class='leadership_score_text'></div>
	<div class='team_score_text'></div>
</div>



<style>
	.input_container{
		
		width:55px;
		height:35px;
/* 		float:left; */
		display:inline-block;
		margin-right:50px;
		padding-top:5px;
		padding-left: 12px;
		
		
	}
	
	.question_container{
		
		width:100%;
		float:left;
/* 		height:150px; */
/* 		padding:10px; */
	}
	
	.question_page:nth-child(odd){

	}
	
	.question_page {
		font-family:Arial,Times New Roman, serif;
		height:150px;
		width:700px;
		float:left;
	}
	
	.page_scroller{
		
		width:99999px;
		overflow: scroll !important;

	}
	
	.questions_container{
		
		width:auto;
		
		margin-bottom: 20px;		
	}
	
	.questions_pagination_container{
		text-align: center;
	}
	
	body{
				overflow:hidden;
				overflow-y: scroll;

	}
</style>

<script type='text/javascript'>
	
	
		
	
	$(document).ready(function(){
		
		resize();
		
		var fullwidth, pagewidth;
	
		var current_response_status = '<?php echo $currentresponsestatus; ?>';
	
		$('#feedbackButton').hide();
		$('.score_text').hide();
		var showpage_feature = true;
		
		if(current_response_status == 'finished'){
			
			
			var current_score = calculateScore();
			
			constructScoreFeedback(current_score);
			showpage_feature = false;
			$('#feedbackButton').show();

			

			
		}
	
		$( window ).resize(function() {
			resize();
		});
		function resize() {
			fullwidth = $('body').width();
			$('div.page_container').width(fullwidth);
			pagewidth = fullwidth;
			$('div.question_page').width(pagewidth);
		}
		
		
		
		var currentPage = 1;
		var total_pages = <?php echo count($questions); ?>;
		
		var opts = {
		    totalPages: total_pages,
		    visiblePages: 5,
		    startPage:currentPage,
		    onPageClick: function (event, page) {
		        console.log('Page change event. Page = ' + page);
		        $('.pagination').data('currentPage', page);
		        showpage(page);
		    }
		};
		
		$('.questions_pagination').twbsPagination(opts);
		
		$('.question_input').change(function(){
			
			if(showpage_feature){
			
				var pageto = currentPage+1;
				
			    if(pageto < opts['totalPages']){
			      $('.questions_pagination').twbsPagination('destroy');
			      $('.questions_pagination').twbsPagination($.extend(opts, {
			          startPage: pageto+1
			      }));   
			    }
			}

			calculateScore();
				
			
		});
		
		function showpage(page){
			
			console.log(page);
			page = page-1;
			
			leftm = fullwidth*page*-1;
			
			$( ".page_scroller" ).animate({
			    marginLeft: leftm,
			}, 400);
		
			currentPage = page;
			
			$('.currentpage_status').text((currentPage+1)+'/'+total_pages);
			
			
		}
		
	
		var showprevious = '<?php echo $pre_qresponse_showanswer ?>';
		var currentStatus = 'unfinished';
		var survey_score = null;
		
		$('#submitButton').click(function(event){
			
			var status = {};
			var statusString;
			var qcount = 0;
			var answeredcount = 0;
			
			
			
			if(showprevious){
				console.log('fsdfdsaf'+showprevious);

			}
			
			
			$('.question_container').each(function(){
				qcount++;
				$(this).find('.question_input').each(function(ind, obj){
					
					if($(obj).is(':checked')){
						status["question"+$(obj).data('question_number')] = $(obj).attr('data-option_num');
						answeredcount++;
						return false;
					}else{
						status["question"+$(obj).data('question_number')] = null;
					}
					
				});				
			});
			
			var currentScore = calculateScore();
			
			survey_score = currentScore;
			
			status["leadership_score"] = currentScore.leadership;
			status["team_score"] = currentScore.team;

			
			status["questions_answered_count"] = answeredcount;
			
			status["status"] = 'unfinished';
			
			if(qcount == answeredcount){
				status["status"] = 'finished';
				currentStatus = 'finished';
			}else if(answeredcount > 0){
				status["status"] = 'attempted';
				currentStatus = 'attempted';

			}
			
			statusString = JSON.stringify(status);
			
			var data = {'data':{}};
			data['sid'] = '<?php echo $sid ?>';
			data['user_id'] = '<?php echo $lti->user_id(); ?>';
			data['data'] = statusString;
			$.ajax({
			  type: "POST",
			  url: "savedata.php",
			  data: data,
			  success: function(response) {
				  
				  console.log(response);
				  console.log('blue');
				  showpreviousresponse();
				  constructScoreFeedback(currentScore);
				  
				  $('#feedbackButton').show();

				 // showscore(currentScore);
				  
			  },
			  error: function(error){
				  	console.log('red');

				  console.log(error);
			  }
			});
			
			
			
			event.preventDefault();
		});
		
		
		var previous_response_status = '<?php echo $pre_responsestatus; ?>';
		
		showpreviousresponse();

		
		function showpreviousresponse(){
			
			console.log('SHOW MEEE!!' + previous_response_status + '--'+currentStatus + '==='+showprevious);
			
			if(previous_response_status == "finished" && current_response_status == showprevious){
		
					console.log('RED 4: '+showprevious);

				var json_response = '<?php echo json_encode($pre_jsonresponse) ?>';
			
				json_response = $.parseJSON(json_response);
				
				var values = [];
				
				$.each(json_response, function(key, val){
					
					console.log(key+' : '+val);
					
					values.push(key+"_option_"+val);
					
				});
				
				$.each(values,function(ind,obj){
					
					
					console.log(obj);
					
					$('#'+obj).parent().parent().css({
						'background-color':'lightgreen',
						'border':'2px solid green',
						'color':'green'
						
					});
					
				});
		
			}
			
			
		}
		
		
		function calculateScore(){
			
			var currentTeamScore = 0;
			var currentLeadershipScore = 0;

			var score_status = {};
			
			$('.question_container').each(function(){
				$(this).find('.question_input').each(function(ind, obj){
					if($(obj).is(':checked')){
						score_status["question"+$(obj).data('question_number')] = $(obj).attr('data-option_num')-1;
						return false;
					}else{
						score_status["question"+$(obj).data('question_number')] = 0;
					}
					
				});				
			});
			
			$.each(score_status, function(key, val){
				
								
				switch (parseInt(key.replace('question',''))) {
				    case 1:
				    	currentLeadershipScore += val;
				    	break;
				    case 2: 
				    	currentLeadershipScore += val;
				    	break;
				    case 3:
				    	currentLeadershipScore += val;
				    	break;
				    case 4: 
				    	currentLeadershipScore += val;
				    	break;
				    case 5:
				    	currentLeadershipScore += val;
				    	break;
				    case 6: 
				    	currentTeamScore += val;
				    	break;
				    case 7:
				    	currentTeamScore += val;
				    	break;
				    case 8: 
				    	currentTeamScore += val;
				    	break;
				    case 9:
				    	currentTeamScore += val;
				    	break;
				    case 10: 
				    	currentTeamScore += val;
				    	break;
				    case 11:
				    	currentLeadershipScore += val;
				    	break;
				    case 12: 
				    	currentLeadershipScore += val;
				    	break;
				    case 13:
				    	currentTeamScore += val;
				    	break;
				    case 14: 
				    	currentLeadershipScore += val;
				    	break;
				    case 15:
				    	currentTeamScore += val;
				    	break;
				}
				
			});
			
			
			return {
				leadership:currentLeadershipScore,
				team:currentTeamScore
			};
			
		}
		
		var answerShown = false;
		$('#feedbackButton').click(function(event){
			
			
			if(answerShown){
				
				$('.score_text').hide();
				$(this).text('Show Feedback');
				answerShown = false;
				
			}else{
				
				$('.score_text').show();
				$(this).text('Hide Feedback');

				answerShown = true;
			}
			
		});

		
		
		function constructScoreFeedback(score){
			
			
			console.log(score.leadership + '----' + score.team);
			
			var leadership_container = $('.leadership_score_text');
			var team_container = $('.team_score_text');
			
			leadership_container.empty();
			
			team_container.empty();

			
			if(score.leadership <= 13){
				
				leadership_container.append("<h5>Leadership</h5>"+"<p>You are not accustomed to providing leadership for the team.  This is acceptable as long as the team has a leader, but you should ensure that you are contributing to the decisions and directions of the team.  If you would like to increase your leadership skills, then you should begin to play an active part in decision making, help negotiate solutions when opinions differ, recognise and utilise the strengths of other team members, and set directions to ensure that tasks are completed on time and to the required standard.</p>");
				
			}else if(score.leadership >= 14 && score.leadership <= 19){
				
				leadership_container.append("<h5>Leadership</h5>"+"<p>You’ve generally been an effective team member although not always in a leadership role.  Remember that it is important to meet the needs of your team members whilst keeping the team on track.  Ensure everyone’s opinion is heard but remember the purpose/ task of the team.</p>");

			}else{
				
				leadership_container.append("<h5>Leadership</h5>"+"<p>You have taken a strong leadership role in teams: providing direction, keeping the team on track, and ensuring that targets are met.  Do remember to include all members of the team in decisions and processes, and ensure that everyone’s voice is heard.</p>");

				
			}
			
			
			if(score.team <= 8){
				
				team_container.append("<h5>Teamwork</h5>"+"<p>You need to put more work into your teamwork skills.  This will probably come with experience and hopefully this module can set you up with models for more effective teamwork.  You will need to commit to the team in terms of sharing resources, directions, and consequences.  A well functioning team can achieve more than individuals but this takes commitment and practice.</p>");

			}else if(score.team >= 9 && score.team <= 13){
				
				team_container.append("<h5>Teamwork</h5>"+"<p>You are an effective team person.  You can improve your skills by working through this online module and remembering to cooperate with all team members, share ideas and responsibilities, identify and resolve any conflict, and most importantly, display a commitment to making the team function effectively. </p>");
				
			}else{
				
				team_container.append("<h5>Teamwork</h5>"+"<p>You are a very effective team person.  Enhance your skills by working through this online module and perhaps mentoring other members of your team who are still learning to be effective team members.</p>");
				
				
			}
			
			
		}
		
		
		
		
		
		
	});
	
	
	
</script>




<!--
<span id='surveyID'></span>




<dl>
	<dt>Status</dt><dd><?php 
		if($lti->is_valid()) {
			echo 'Valid';
		} else {
			echo 'Invalid';
		}
	?></dd>
	<dt>User ID</dt><dd><?php echo $lti->user_id();?></dd>
	<dt>Call Data</dt><dd><pre><?php print_r($lti->calldata());?></pre></dd>
	<dt>Errors</dt><dd><pre><?php print_r($lti->get_errors()); ?></pre></dd>
	
</dl>
-->

</body>
</html>
