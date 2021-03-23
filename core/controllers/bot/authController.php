<?php

	class authController
	{
		public $id;
		public $user_id;
		public $username;
		public $firstname;
		public $lastname;

		function __construct($id,$username,$firstname,$lastname)
		{
			$this->id = $id;
			if ($username) {
				$this->username = $username;
			}else{
				$this->username = "unnamed";
			}

			if ($firstname) {
				$this->firstname = $firstname;
			}else{
				$this->firstname = "-";
			}

			if ($lastname) {
				$this->lastname = $lastname;
			}else{
				$this->lastname = "-";
			}
		}

		public function authUser() {
		    global $db,$dbconnection;

			if ($this->id < 0) exit();
			$user_data = $db->select("SELECT * FROM accounts WHERE chat_id='".$this->id."'");
			if (isset($user_data[0]['id'])) {

				// Update username and first_name and last action
				if ($user_data[0]['username'] != $this->username) {
					$db->update("UPDATE accounts SET username='".$this->username."' WHERE chat_id='".$this->id."'");
				}
				if ($user_data[0]['first_name'] != $this->firstname) {
					$db->update("UPDATE accounts SET first_name='".$this->firstname."' WHERE chat_id='".$this->id."'");
				}
				if ($user_data[0]['last_name'] != $this->lastname) {
					$db->update("UPDATE accounts SET last_name='".$this->lastname."' WHERE chat_id='".$this->id."'");
				}

				$db->update("UPDATE accounts SET last_action=NOW() WHERE chat_id='".$this->id."'");
				
				return $user_data;
				
			}else{
				// New user
				if (isset($this->id)) {

					$this->firstname = mysqli_real_escape_string($dbconnection,$this->firstname);
					$this->lastname = mysqli_real_escape_string($dbconnection,$this->lastname);

					$insert_id = $db->insert("INSERT INTO accounts(
						username,
						first_name,
						last_name,
						chat_id
						)VALUES(
						'".$this->username."',
						'".$this->firstname."',
						'".$this->lastname."',
						'".$this->id."'
					)",1);

					$user_data = $db->select("SELECT * FROM accounts WHERE id='".$insert_id."'");
					return $user_data;
				}
			}
		}
	}