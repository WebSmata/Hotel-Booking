<?php

	class booking
	{ 
		public $bookingid = null;
		public $booked = null;
		public $clientid = null;
		public $roomid = null;
		public $created = null;
		public $updated = null;

		public function __construct( $data=array() ) 
		{
			if ( isset( $data['bookingid'] ) ) $this->bookingid = (int) $data['bookingid'];
			if ( isset( $data['booked'] ) ) $this->booked =  $data['booked'];
			if ( isset( $data['clientid'] ) ) $this->clientid = $data['clientid'];
			if ( isset( $data['roomid'] ) ) $this->roomid = $data['roomid'];
			if ( isset( $data['created'] ) ) $this->created = $data['created'];
			if ( isset( $data['updated'] ) ) $this->updated = $data['updated'];
		}

		public function storeFormValues ( $params ) 
		{
			$this->__construct( $params );

			if ( isset($params['created']) ) {
				$created = explode ( '-', $params['created'] );

				if ( count($created) == 3 ) {
					list ( $y, $m, $d ) = $created;
					$this->created = mktime ( 0, 0, 0, $m, $d, $y );
				}
			}
		}

		public static function getById( $bookingid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM bookings WHERE bookingid = :bookingid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":bookingid", $bookingid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new booking( $row );
		}

		public static function getList() 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = 'SELECT bookingid, booked, 
			CONCAT(clients.firstname, " ", clients.lastname) AS clientid, 
			CONCAT(rooms.title, " - ", rooms.price) AS roomid  
			FROM bookings 
			INNER JOIN clients ON clients.clientid = bookings.clientid 
			INNER JOIN rooms ON rooms.roomid = bookings.roomid 
			ORDER BY bookingid DESC';

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$booking = new booking( $row );
				$list[] = $booking;
			}

			$conn = null;
			return $list;
		}

		public static function getCancelled() 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = 'SELECT bookingid, booked, 
			CONCAT(clients.firstname, " ", clients.lastname) AS clientid
			FROM bookings 
			INNER JOIN clients ON clients.clientid = bookings.clientid 
			WHERE roomid=0 ORDER BY bookingid DESC';

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$booking = new booking( $row );
				$list[] = $booking;
			}

			$conn = null;
			return $list;
		}

		public static function searchThis( $search ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT * FROM bookings WHERE booked LIKE '%".$search."%' ORDER BY created DESC";

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$booking = new booking( $row );
				$list[] = $booking;
			}

			$conn = null;
			return $list;
		}

		public function insert() 
		{
			if ( !is_null( $this->bookingid ) ) trigger_error ( "booking::insert(): Attempt to insert an booking object that already has its ID property set (to $this->bookingid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO bookings ( booked, clientid, roomid, created ) VALUES ( :booked, :clientid, :roomid, :created)";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":booked", $this->booked, PDO::PARAM_STR );
			$st->bindValue( ":clientid", $this->clientid, PDO::PARAM_STR );
			$st->bindValue( ":roomid", $this->roomid, PDO::PARAM_STR );
			$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->bookingid = $conn->lastInsertId();
			$conn = null;
			return $this->bookingid;
		}
		
		public function update() 
		{
			if ( is_null( $this->bookingid ) ) trigger_error ( "booking::update(): Attempt to update an booking object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE bookings SET booked=:booked, clientid=:clientid, roomid=:roomid, updated=:updated WHERE bookingid = :bookingid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":booked", $this->booked, PDO::PARAM_STR );
			$st->bindValue( ":clientid", $this->clientid, PDO::PARAM_STR );
			$st->bindValue( ":roomid", $this->roomid, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":bookingid", $this->bookingid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function cancel() 
		{
			if ( is_null( $this->bookingid ) ) trigger_error ( "booking::cancel(): Attempt to update an booking object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE bookings SET roomid=:roomid, updated=:updated WHERE bookingid = :bookingid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":roomid", 0, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":bookingid", $this->bookingid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{

			if ( is_null( $this->bookingid ) ) trigger_error ( "booking::delete(): Attempt to delete an booking object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM bookings WHERE bookingid = :bookingid LIMIT 1" );
			$st->bindValue( ":bookingid", $this->bookingid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
