<?php
	function tableCreate( $table,  $variables = array() ) 
	{
		try {
			$fields = array();
			$values = array();
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "CREATE TABLE IF NOT EXISTS ". $table;
			foreach( $variables as $field ) $fields[] = $field;
			$fields = ' (' . implode(', ', $fields) . ')';      
			$sql .= $fields;
			$conn->exec( $sql );
		} catch(PDOException $exception) {
			$as_err['errno'] = 3;
			$as_err['errtitle'] = 'Database action failed';
			$as_err['errsumm'] = 'Creating the table '. $table . ' failed';
			$as_err['errfull'] = $exception->getMessage();
		}
		$conn = null;
	}
	
	function createTables()
	{
		tableCreate( 'rooms',  
			array(//title, code, price, created, updated
				'roomid int(11) NOT NULL AUTO_INCREMENT',
				'title varchar(100) NOT NULL',
				'code varchar(100) NOT NULL',
				'price varchar(100) NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (roomid)',
				'UNIQUE row1(title)',
				'UNIQUE row2(code)',
			)
		);
		
		tableCreate( 'bookings',  
			array(//booked, clientid, roomid, created, updated
				'bookingid int(11) NOT NULL AUTO_INCREMENT',
				'booked varchar(100) NOT NULL',
				'clientid int(11) DEFAULT 0',
				'roomid int(11) DEFAULT 0',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (bookingid)',
			)
		); 
		
		tableCreate( 'options',
			array(
				'optionid int(11) NOT NULL AUTO_INCREMENT',
				'title varchar(100) NOT NULL',
				'content varchar(2000) NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (optionid)',
			)
		); 
		
		tableCreate( 'clients',  
			array(//firstname, lastname, handle, email, mobile, address, sex, password, created, updated
				'clientid int(11) NOT NULL AUTO_INCREMENT',
				'firstname varchar(50) NOT NULL',
				'lastname varchar(50) NOT NULL',
				'handle varchar(100) NOT NULL',
				'email varchar(100) NOT NULL',
				'mobile int(11) DEFAULT 0',
				'address varchar(100) NOT NULL',
				'sex int(10) NOT NULL DEFAULT 1',
				'password int(11) DEFAULT 0',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (clientid)',
				'UNIQUE email_address(email)',
			)
		);
		
		tableCreate( 'admins', 
			array(
				'adminid int(11) NOT NULL AUTO_INCREMENT',
				'handle varchar(50) NOT NULL',
				'firstname varchar(50) NOT NULL',
				'lastname varchar(50) NOT NULL',
				'mobile varchar(50) NOT NULL',
				'idnumber varchar(50) NOT NULL',
				'sex int(10) NOT NULL DEFAULT 1',
				'password text NOT NULL',
				'email varchar(200) NOT NULL',
				'level int(10) NOT NULL DEFAULT 0',
				'joined datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (adminid)',
			)
		);
		
	}
	createTables();
	
	function checkTables( $table ) 
	{
		$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
		$sql = "SELECT * FROM " . $table . " LIMIT 1";
		$st = $conn->prepare( $sql );
		$st->execute();
		$row = $st->fetch();
		$conn = null;
		if ( $row ) return 0;
		else return 1;
	}