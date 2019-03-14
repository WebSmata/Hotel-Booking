<?php

	require( "config.php" );
	session_start();
	$open = isset( $_GET['open'] ) ? $_GET['open'] : "";

	$content = array();
	$content['sitename'] = strlen(as_option('sitename')) ? as_option('sitename') : SITENAME;
	$userid = isset( $_SESSION['loggedin_user'] ) ? $_SESSION['loggedin_user'] : "";
	$level = isset( $_SESSION['loggedin_level'] ) ? $_SESSION['loggedin_level'] : "";
	$fullname = isset( $_SESSION['loggedin_fullname'] ) ? $_SESSION['loggedin_fullname'] : "";
		
	if ($open == 'install') {
		errMissingTables();
		exit();
	}
	
	if ( $open != "signin" && $open != "signout" && $open != "register" && !$userid ) {
		$open = 'signin';
	}

	switch ( $open ) {
		case 'signin':
			require( CORE . "client.php" );
			$content['client'] = new client;
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array( 
						'handle' => array('label' => 'Username:', 'type' => 'text'),				
						'password' => array('label' => 'Password:', 'type' => 'password'),
					),
			
					'buttons' => array('signin' => array('label' => 'Login')),			
				);
			
			$content['title'] = "Login to Your Account";
			if ( isset( $_POST['signin'] ) ) {
				$userid = client::signinuser($_POST['handle'], md5($_POST['password']));
				if ($userid) {
					header( "Location: index.php" );
				} else {
					$content['errorMessage'] = "Incorrect username or password. Please try again.";
				}
			}
			break;

		case 'register':
			require( CORE . "client.php" );
			$content['client'] = new client;			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array( 
						'firstname' => array('label' => 'First Name:', 'type' => 'text', 'tags' => 'required '),
						'lastname' => array('label' => 'Last Name:', 'type' => 'text', 'tags' => 'required '),
						'sex' => array('label' => 'Sex:', 'type' => 'radio', 
							'options' => array(
								'male' => array('name' => 'Male', 'value' => 1),
								'female' => array('name' => 'Female', 'value' => 2),
								), 'value' => 1, 'tags' => 'required '),
						'address' => array('label' => 'Physical Address:', 'type' => 'text', 'tags' => 'required '),
						'mobile' => array('label' => 'Mobile:', 'type' => 'text', 'tags' => 'required '),
						'email' => array('label' => 'Email:', 'type' => 'email', 'tags' => 'required '),
						'handle' => array('label' => 'Username:', 'type' => 'text', 'tags' => 'required '),
						'password' => array('label' => 'Password:', 'type' => 'password', 'tags' => 'required '),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array('register' => array('label' => 'Register')),
				);
			
			$content['title'] = "Register as a Client";
			if ( isset( $_POST['register'] ) ) {
				$client = new client;
				$client->storeFormValues( $_POST );
				$userid = $client->insert();
				if ($userid) {
					$_SESSION['loggedin_level'] = $_POST['level'];
					$_SESSION['loggedin_user'] = $userid;
					header( "Location: index.php" );
				} else {
					$content['errorMessage'] = "Unable to register you at the moment. Please try again later.";
				}
			}
			break;
			
		case 'account':
			require( CORE . "index.php" );
			$content['admin'] = admin::getById( (int)$_SESSION["loggedin_user"] );
			$content['title'] = $content['admin']->firstname . ' ' .$content['admin']->lastname.
			' '.($content['admin']->sex == 1 ? '(M)' : '(F)' );
			break;
			
		case 'signout';
			unset( $_SESSION['loggedin_level'] );
			unset( $_SESSION['loggedin_fullname'] );
			unset( $_SESSION['loggedin_user'] );
			header( "Location: index.php" );
			break;
				
		case 'database';
			errMissingTables();
			break;
			
		case 'booking_view':
			require( CORE . "booking.php" );
			require( CORE . "question.php" );
			$bookingid = $_GET["bookingid"];
			$adminid = $_SESSION["loggedin_user"];
			$booking = booking::getById( (int)$bookingid );
						
			$content['title'] = $booking->title;
			$content['link'] = '<a href="index.php?open=booking_take&&bookingid='.$bookingid.'" style="float:right;">TAKE TEST</a>';
			$content['page'] = array(
					'type' => 'viewer',
					'subitems' => array('Questions' => $booking->clientid, 'Attempt' => $booking->roomid),
				);
			
			break;
			
		
		case 'booking_take':
			require( CORE . "booking.php" );
			require( CORE . "question.php" );
			require( CORE . "review.php" );
			$bookingid = $_GET["bookingid"];
			$client = $_SESSION["loggedin_user"];
			$booking = booking::getById( (int)$bookingid );
			$request = 'index.php?open=booking_take&&bookingid='.$bookingid;			
			
			$clientid = question::getList($bookingid);
			$listitems = array();
			$i=1;
			foreach ( $clientid as $question ) {
				$listitems[$i] = array($i, $question->questionid, $question->title, $question->optiona, $question->optionb, $question->optionc, $question->optiond, $question->answer);
				$i++;
			}
			
			$content['review'] = new review;
			$count = count($clientid);
			$bookingid = $_GET['bookingid'];
			$reviews = review::countQuestions( $bookingid );
			$request = 'index.php?open=booking_take&&bookingid='.$bookingid;			
				
			if ($reviews != $count) {
				$currect_quiz = $reviews + 1;
				$current_question = $listitems[$currect_quiz];
				$content['page'] = array(
					'type' => 'examine',
					'action' => $request,
					'quiz' => $current_question[2],
					'answers' => array(
						'<b>A.</b> '.$current_question[3],
						'<b>B.</b> '.$current_question[4],
						'<b>C.</b> '.$current_question[5],
						'<b>D.</b> '.$current_question[6],
					),
					'hidden' => array(
						'clientid' => $client,
						'bookingid' => $bookingid,
						'title' => $current_question[2],
						'optiona' => $current_question[3],
						'optionb' => $current_question[4],
						'optionc' => $current_question[5],
						'optiond' => $current_question[6],
						'true_answer' => $current_question[7],
					),		
					'buttons' => array('submitAnswer' => array('label' => 'Submit Answer')),
				);
				
				$content['title'] = "Question ".$currect_quiz." out of ".$count;
				if ( isset( $_POST['submitAnswer'] ) ) {
					$review = new review;
					$review->storeFormValues( $_POST );
					$questionid = $review->insert();
					if ($questionid) {
						$reviews = review::countQuestions( $bookingid );
						if ( $reviews == $count ) header( "Location: index.php?open=booking_view&&bookingid=".$bookingid );
						else header( "Location: ".$request );
					} else {
						$content['errorMessage'] = "Unable to submit an answer at the moment. Please try again later.";
					}
				} 
			}
			break;
		
		case 'results_all':
			require( CORE . "booking.php" );
			$bookings = booking::getList();
			$listitems = array();
			foreach ( $bookings as $booking ) {
				$listitems[$booking->bookingid] = array($booking->title, $booking->clientid, $booking->created);
			}
			
			$content['title'] = "My Results";
			$content['page'] = array(
				'type' => 'table',
				'headers' => array( 'title', 'clientid', 'created' ), 
				'items' => $listitems,
				'onclick' => 'open=booking_view&&bookingid=',
			);
			break;
			break;
		default: 
			require( CORE . "booking.php" );
			$bookings = booking::getList();
			$listitems = array();
			foreach ( $bookings as $booking ) {
				$listitems[$booking->bookingid] = array($booking->title, $booking->clientid, $booking->created);
			}
			
			$content['title'] = "Available Bookings";
			$content['page'] = array(
				'type' => 'table',
				'headers' => array( 'title', 'clientid', 'created' ), 
				'items' => $listitems,
				'onclick' => 'open=booking_view&&bookingid=',
			);
			break;
	}
	
	require ( CORE . "page.php" );