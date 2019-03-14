<?php

	class room
	{ 
		public $roomid = null;
		public $title = null;
		public $code = null;
		public $price = null;
		public $created = null;
		public $updated = null;

		public function __construct( $data=array() ) 
		{
			if ( isset( $data['roomid'] ) ) $this->roomid = (int) $data['roomid'];
			if ( isset( $data['title'] ) ) $this->title =  $data['title'];
			if ( isset( $data['code'] ) ) $this->code = $data['code'];
			if ( isset( $data['price'] ) ) $this->price = $data['price'];
			if ( isset( $data['created'] ) ) $this->created = (int) $data['created'];
			if ( isset( $data['updated'] ) ) $this->updated = (int) $data['updated'];
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

		public static function getById( $roomid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM rooms WHERE roomid = :roomid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":roomid", $roomid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new room( $row );
		}

		public static function getList($free = true) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			
			if ($free) $sql = "SELECT * FROM rooms ORDER BY roomid DESC";
			else $sql = 'SELECT * FROM rooms 
			INNER JOIN rooms ON rooms.roomid = bookings.roomid 
			WHERE bookings.roomid=0 ORDER BY roomid DESC';
			
			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$room = new room( $row );
				$list[] = $room;
			}

			$sql = "SELECT FOUND_ROWS() AS totalRows";
			$totalRows = $conn->query( $sql )->fetch();
			$conn = null;
			return $list;
		}

		public function insert() 
		{
			if ( !is_null( $this->roomid ) ) trigger_error ( "room::insert(): Attempt to insert an room object that already has its ID property set (to $this->roomid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO rooms ( title, code, price, created ) VALUES ( :title, :code, :price, :created)";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":title", $this->title, PDO::PARAM_STR );
			$st->bindValue( ":code", $this->code, PDO::PARAM_STR );
			$st->bindValue( ":price", $this->price, PDO::PARAM_STR );
			$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->roomid = $conn->lastInsertId();
			$conn = null;
			return $this->roomid;
		}

		public function update() 
		{
			if ( is_null( $this->roomid ) ) trigger_error ( "room::update(): Attempt to update an room object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE rooms SET title=:title, code=:code, price=:price, updated=:updated WHERE roomid = :roomid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":title", $this->title, PDO::PARAM_STR );
			$st->bindValue( ":code", $this->code, PDO::PARAM_STR );
			$st->bindValue( ":price", $this->price, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":roomid", $this->roomid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{

			if ( is_null( $this->roomid ) ) trigger_error ( "room::delete(): Attempt to delete an room object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM rooms WHERE roomid = :roomid LIMIT 1" );
			$st->bindValue( ":roomid", $this->roomid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
