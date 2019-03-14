<?php

	class admin
	{
		public $adminid = null;
		public $handle = null;
		public $firstname = null;
		public $lastname = null;
		public $mobile = null;
		public $sex = null;
		public $password = null;
		public $email = null;
		public $level = null;
		public $joined = null;
		public $updated = null;

		public function __construct( $data=array() ) 
		{ 
			if ( isset( $data['adminid'] ) ) $this->adminid = (int) $data['adminid'];
			if ( isset( $data['handle'] ) ) $this->handle = $data['handle'];
			if ( isset( $data['firstname'] ) ) $this->firstname = $data['firstname'];
			if ( isset( $data['lastname'] ) ) $this->lastname = $data['lastname'];
			if ( isset( $data['mobile'] ) ) $this->mobile = $data['mobile'];
			if ( isset( $data['sex'] ) ) $this->sex = (int) $data['sex'];
			if ( isset( $data['password'] ) ) $this->password = md5($data['password']);
			if ( isset( $data['email'] ) ) $this->email = $data['email'];
			if ( isset( $data['dobirth'] ) ) $this->dobirth = $data['dobirth'];
			if ( isset( $data['level'] ) ) $this->level = (int) $data['level'];
			if ( isset( $data['joined'] ) ) $this->joined = (int) $data['joined'];
			if ( isset( $data['updated'] ) ) $this->updated = (int) $data['updated'];
		}

		public function storeFormValues ( $params ) 
		{
			$this->__construct( $params );

			if ( isset($params['joined']) ) {
				$joined = explode ( '-', $params['joined'] );

				if ( count($joined) == 3 ) {
					list ( $y, $m, $d ) = $joined;
					$this->joined = mktime ( 0, 0, 0, $m, $d, $y );
				}
			}
		}

		public static function getById( $adminid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(joined) AS joined FROM admins WHERE adminid = :adminid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":adminid", $adminid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new admin( $row );
		}

		public static function signinuser( $handle, $password ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT * FROM admins WHERE handle = :handle AND password = :password";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":handle", $handle, PDO::PARAM_INT );
			$st->bindValue( ":password", $password, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) {
				$_SESSION['loggedin_level'] = $row['level'];
				$_SESSION['loggedin_adminame'] = $row['firstname'] . ' ' . $row['lastname'];
				$_SESSION['loggedin_admin'] = $row['adminid'];
				return true;
			}	else return false;
		}

		public static function getList( $level ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM admins WHERE level = :level ORDER BY joined DESC";

			$st = $conn->prepare( $sql );
			$st->bindValue( ":level", $level, PDO::PARAM_INT );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$admin = new admin( $row );
				$list[] = $admin;
			}

			$conn = null;
			return $list;
		}

		public function insert() 
		{
			if ( !is_null( $this->adminid ) ) trigger_error ( "admin::insert(): Attempt to insert an admin object that already has its ID property set (to $this->adminid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO admins ( handle, firstname, lastname, mobile, sex, password, email, level, joined ) VALUES ( :handle, :firstname, :lastname, :mobile, :sex, :password, :email, :level, :joined )";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":handle", $this->handle, PDO::PARAM_STR );
			$st->bindValue( ":firstname", $this->firstname, PDO::PARAM_STR );
			$st->bindValue( ":lastname", $this->lastname, PDO::PARAM_STR );
			$st->bindValue( ":mobile", $this->mobile, PDO::PARAM_STR );
			$st->bindValue( ":sex", $this->sex, PDO::PARAM_STR );
			$st->bindValue( ":password", $this->password, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":level", $this->level, PDO::PARAM_STR );
			$st->bindValue( ":joined", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->adminid = $conn->lastInsertId();
			$conn = null;
			return $this->adminid;
		}

		public function update() 
		{
			if ( is_null( $this->adminid ) ) trigger_error ( "admin::update(): Attempt to update an admin object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE admins SET handle=:handle, firstname=:firstname, lastname=:lastname, mobile=:mobile,  sex=:sex, email=:email, level=:level, updated=:updated WHERE adminid =:adminid";
			
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":handle", $this->handle, PDO::PARAM_STR );
			$st->bindValue( ":firstname", $this->firstname, PDO::PARAM_STR );
			$st->bindValue( ":lastname", $this->lastname, PDO::PARAM_STR );
			$st->bindValue( ":sex", $this->sex, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":level", $this->level, PDO::PARAM_STR );
			$st->bindValue( ":mobile", $this->mobile, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{
			if ( is_null( $this->adminid ) ) trigger_error ( "admin::delete(): Attempt to delete an admin object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM admins WHERE adminid = :adminid LIMIT 1" );
			$st->bindValue( ":adminid", $this->adminid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
