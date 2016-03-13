<?php require_once('inc/header.php');
if($lti->is_valid()) {



// RUNNING LIVE - DO NOT EDIT CODE HERE!!!!!!!!

// edit in tools-dev production vm, test fully, push to git hub and then pull to tools.


			echo '<p><span style="color:#00ea05"><b>LTI Valid</b></span> , Dev Version - <span style="color:red"><b> DO NOT USE IN LIVE COURSES </b></span> - contact UQx Technical Team</p>';
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

		$scale = 2;



		$questions = array(


			"You can identify specific cliques or sub-groups within your team that always seem to stick together on every issue.",
			"Members tend to quickly back down their positions.",
			"Members are reluctant to consider alternatives other than their own position or ideas.",
			"The same problems and issues keep coming up over and over again.",
			"Most differences of opinion are settled by voting.",
			"Members give new ideas a fair hearing and will ask questions if they don’t understand.",
			"Most of the solutions your team reaches are less than ideal.",
			"Keeping everyone happy is more important than finding the best solution to a problem.",
			"There’s a tendency to label or stereotype individual members.",
			"Your team focuses mostly on incorporating everyone’s position (as opposed to trying to identify the best possible solution) when looking for solutions for problems.",
			"Meetings typically start later than planned and/ or there is a tendency to get off the subject at once.",
			"One or two people dominate discussions and action planning.",
			"Agreement about team decisions is genuine because all team members understand the reasons for final decisions.",
			"Members frequently interrupt or talk while others are speaking.",
			"You and/ or others feel uncomfortable saying what you really think or feel.",
			"Members on the team are overly polite to one another.",
			"There is an overall lack of commitment to team decisions.",
			"Members are comfortable debating differing opinions.",
			"Members get you agitated easily (e.g. if a discussion lasts longer than expected).",
			"The team avoids vigorous debate on issues or topics.",
			"There is a general lack of enthusiasm amongst team members for the ways issues are resolved or problems are solved.",
			"Members usually accept ideas and solutions without thoroughly discussing the pros and cons.",
			"Members lecture one another to convince them they are right.",
			"Your team makes an effort to understand everyone's perspectives and opinions.",
			"Members frequently blame others when things do not go as hoped or planned.",
			"Team meetings end without members having a clear idea about what happened or what should occur next."

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
					<div class="input_container "><label class="radio-inline"><input type="radio" name="question'.$questionnum.'" class="question_input" id="question'.$questionnum.'_option_'.$scalenum.'" value="'.$scalenum.'" data-question_number="'.$questionnum.'" data-question_id="question'.$questionnum.'" data-option_num="'.$scalenum.'" '.$checked.'>'.getOptionName($scalenum).'</label></div>


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

		function getOptionName($scale_number){

			$option_name = '';

			switch ($scale_number) {
			    case 1:
			    	$option_name = "True";
			        break;
			    case 2:
			    	$option_name = "False";
			        break;

			}

			return $option_name;

		}



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

</form>

</br>
     <button id='submitButton' class="btn btn-primary btn-md">Submit</button>     <button id='resetButton' class="btn btn-default btn-md">Reset</button>
</br>
</br>



<div class='feedbackContainer'>


<h4>Avoiding</h4>

<div class="progress">
  <div class="progress-bar progress-bar-danger" role="progressbar"  id='avoiding' aria-valuenow="40"
  aria-valuemin="0" aria-valuemax="100" >
  </div>
</div>

<h4>Accommodating</h4>

<div class="progress">

  <div class="progress-bar progress-bar-info" role="progressbar"  id='accommodate' aria-valuenow="50"
  aria-valuemin="0" aria-valuemax="100"></div>
</div>

<h4>Competing</h4>

<div class="progress">

  <div class="progress-bar progress-bar-warning" role="progressbar"  id='competing' aria-valuenow="60"
  aria-valuemin="0" aria-valuemax="100" ></div>
</div>

<h4>Compromise</h4>


<div class="progress">

  <div class="progress-bar" role="progressbar"  id='compromise' aria-valuenow="70"
  aria-valuemin="0" aria-valuemax="100" >
  </div>
</div>
</div>

<h4>Collaborating</h4>

<div class="progress">

  <div class="progress-bar progress-bar-success" role="progressbar"  id='collaborating' aria-valuenow="70"
  aria-valuemin="0" aria-valuemax="100" >
  </div>
</div>
</div>




<style>
	.input_container{

/* 		width:55px; */
		height:35px;
/* 		float:left; */
/* 		display:inline-block; */
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
	    background-color: #f7f7f7;

	}

	.question_page {
		font-family:Arial,Times New Roman, serif;
		height:150px;
		width:700px;
		float:left;
		overflow-y: hidden;



	    background-color: #e5e5e5;
	    border-radius: 30px;
	    padding-left: 20px;
	    padding-right: 20px;
	    padding-top: 20px;
	    padding-bottom: 20px;


	    margin:10px;
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



	.feedback_text{
		margin-top: 50px;
		margin-bottom: 50px;
	}

	.feedbackContainer{

		margin-top: 10px;

	}

	.loadingicon{

		font-size: 18px;
		margin-top: 20px;
/* 		display:inline-block; */


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

// 			constructScoreFeedback(current_score);
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
			$('div.question_page').width(pagewidth-60);
		}



		var currentPage = 1;
		var total_pages = <?php echo count($questions); ?>;

		var opts = {
		    totalPages: total_pages,
		    visiblePages: 5,
		    startPage:currentPage,
		    onPageClick: function (event, page) {
		        //console.log('Page change event. Page = ' + page);
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

		});

		function showpage(page){

			//console.log(page);
			page = page-1;

			leftm = fullwidth*page*-1;

			$( ".page_scroller" ).animate({
			    marginLeft: leftm,
			}, 400);

			currentPage = page;

			//$('.currentpage_status').text((currentPage+1)+'/'+total_pages);


		}


		var showprevious = '<?php echo $pre_qresponse_showanswer ?>';
		var currentStatus = 'unfinished';
		var survey_score = null;

		$('#submitButton').click(function(event){

			$(this).addClass('disabled');

				  $(this).prop("disabled", true);
 				 $(this).empty().append("Submitting <i class='fa fa-spinner fa-pulse'></i>");

			var data = check();
			save(data);

		});

		$('#resetButton').click(function(event){

			reset();

		});

		function check(){

			var status = {};
			var statusString;
			var qcount = 0;
			var answeredcount = 0;



			if(showprevious){
				//console.log('fsdfdsaf'+showprevious);

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

			status["score"] = currentScore;

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

			return statusString;

		}

		function save(data_to_save){



			var data = {'data':{}};
			data['sid'] = '<?php echo $sid ?>';
			data['user_id'] = '<?php echo $lti->user_id(); ?>';
			data['form'] = $('#questionnaire_form').serialize();
			data['data'] = data_to_save;

			  data['lti_id'] = '<?php echo $lti->lti_id(); ?>';
			  data['lis_outcome_service_url'] = '<?php echo $lti->grade_url(); ?>';
			  data['lis_result_sourcedid'] = '<?php echo $lti->result_sourcedid(); ?>';

			//console.log(data)
			$.ajax({
			  type: "POST",
			  url: "savedata.php",
			  data: data,
			  success: function(response) {

				  console.log(response);
				  //console.log('blue');

				  $("#submitButton").removeClass('disabled');
				  $("#submitButton").prop("disabled", false);
 				 $("#submitButton").empty().append("Submit");










				var currentscore = calculateScore();
				update_feedbackBars(currentscore);

			  },
			  error: function(error){
				  	console.log('red');

				  console.log(error);
			  }
			});




		}

		function reset(){

			//console.log('reset');

			showpage(1);

			$('.questions_pagination').twbsPagination('destroy');
			$('.questions_pagination').twbsPagination($.extend(opts, {
			          startPage: 1
			}));



			$('.question_container').each(function(){
				$(this).find('.question_input').each(function(ind, obj){
					$(obj).removeAttr('checked');
				});
			});

			var blank_data = check();

			save(blank_data);

			//console.log('reset');

			update_feedbackBars(calculateScore());

		}



		var previous_response_status = '<?php echo $pre_responsestatus; ?>';

		showpreviousresponse();


		function showpreviousresponse(){

// 			//console.log('SHOW MEEE!!' + previous_response_status + '--'+currentStatus + '==='+showprevious);

			if(previous_response_status == "finished" && current_response_status == showprevious){

					//console.log('RED 4: '+showprevious);

				var json_response = '<?php echo json_encode($pre_jsonresponse) ?>';

				json_response = $.parseJSON(json_response);

				var values = [];

				$.each(json_response, function(key, val){

					//console.log(key+' : '+val);

					values.push(key+"_option_"+val);

				});

				$.each(values,function(ind,obj){


					//console.log(obj);

					$('#'+obj).parent().parent().css({
						'background-color':'lightgreen',
						'border':'2px solid green',
						'color':'green'

					});

				});

			}


		}


		update_feedbackBars(calculateScore());


		function calculateScore(){

			var score_competing = 0;
			var score_accommodate = 0;
			var score_avoiding = 0;
			var score_compromise = 0;
			var score_collaborating = 0;


			var score_status = {};

			$('.question_container').each(function(){
				$(this).find('.question_input').each(function(ind, obj){
					if($(obj).is(':checked')){
						score_status["question"+$(obj).data('question_number')] = parseInt($(obj).attr('data-option_num'));
						return false;
					}else{
						score_status["question"+$(obj).data('question_number')] = 0;
					}

				});
			});

			$.each(score_status, function(key, val){

				if(val == 1){
					switch (parseInt(key.replace('question',''))) {
					    case 1:
					    	score_competing += 1;
					    	break;
					    case 2:
					    	score_accommodate += 1;
					    	break;
					    case 3:
					    	score_competing += 1;
					    	break;
					    case 4:
					    	score_avoiding += 1;
					    	break;
					    case 5:
					    	score_compromise += 1;
					    	break;
					    case 6:
					    	score_collaborating += 1;
					    	break;
					    case 7:
					    	score_compromise += 1;
					    	break;
					    case 8:
					    	score_accommodate += 1;
					    	break;
					    case 9:
					    	score_competing += 1;
					    	break;
					    case 10:
					    	score_compromise += 1;
					    	break;
					    case 11:
					    	score_avoiding += 1;
					    	break;
					    case 12:
					    	score_accommodate += 1;
					    	break;
					    case 13:
					    	score_collaborating += 1;
					    	break;
					    case 14:
					    	score_competing += 1;
					    	break;
					    case 15:
					    	score_accommodate += 1;
					    	break;
					    case 16:
					    	score_accommodate += 1;
					    	break;
					    case 17:
					    	score_compromise += 1;
					    	break;
					    case 18:
					    	score_collaborating += 1;
					    	break;
					    case 19:
					    	score_competing += 1;
					    	break;
					    case 20:
					    	score_compromise += 1;
					    	break;
					    case 21:
					    	score_compromise += 1;
					    	break;
					    case 22:
					    	score_avoiding += 1;
					    	break;
					    case 23:
					    	score_competing += 1;
					    	break;
					    case 24:
					    	score_collaborating += 1;
					    	break;
					    case 25:
					    	score_competing += 1;
					    	break;
					    case 26:
					    	score_avoiding += 1;
					    	break;
					}
				}

			});


			return {
				competing:score_competing,
				accommodate:score_accommodate,
				avoiding:score_avoiding,
				compromise:score_compromise,
				collaborating:score_collaborating
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


		function update_feedbackBars(score){




			var sumOfTotals = score.competing + score.accommodate + score.avoiding + score.compromise + score.collaborating;


/*
				competing = score.competing/sumOfTotals
				accommodate = score.accommodate/sumOfTotals
				avoiding = score.avoiding/sumOfTotals
				compromise score.compromise/sumOfTotals
				collaborating = score.collaborating/sumOfTotals
*/

			$.each(score,function(key, val){

				//console.log(key+'----'+val+'---'+sumOfTotals);

				if(sumOfTotals == 0){

					sumOfTotals = 1;

				}
				$('#'+key).css({width:((val/sumOfTotals)*100)+'%'});

				$('#compromise').css({'background-color':'yellow'});


			});


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
