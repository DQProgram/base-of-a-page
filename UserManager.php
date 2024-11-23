<?php

require_once('User.php');
require_once('DbManagerPDO.php');

class UserManager{

	private $_myDbManager;

	public function __construct(){
		try{
			$this->_myDbManager = new DbManagerPDO();
		}
		catch(Exception $e){
			throw $e;
		}
	}

	public function registerUser($user, $password){

		//again, one can verify if the object received is as expected. Both previous methods can also implement this (I will do so later on)
		if ( !is_a($user,'User') ){
			throw new InvalidArgumentException("Invalid argument received.");
		}

		//my assumption is that all data has already been validated and the user does not exist in the database
		$password = md5($password);

		$data = array('username' => $user->getUsername(), 'email' => $user->getEmail(), 'nif' => $user->getNif(), 'password' => $password);

		$query = "INSERT INTO users (username, email, nif, password) VALUES(:username,:email,:nif,:password)";

		//execute query
		try{
			$result = $this->_myDbManager->executeQuery($query,$data);

			if($result){
				//success
				return(true);
			}
			else{
				//error
				return($result->errorCode());
			}
		}
		catch(InvalidArgumentException $e){
			throw new Exception("Check your data form.");
		}
		catch(Exception $e){
			throw $e;
		}
	}

	//check if a user exists on the database (email, nif and username must be unique)
	public function checkUser($user){
	
		//again, one can verify if the object received is as expected. Both previous methods can also implement this (I will do so later on)
		if ( !is_a($user,'User') ){
			throw new InvalidArgumentException("Invalid argument received.");
		}	
	
		$data = array('username' => $user->getUsername(), 'email' => $user->getEmail(), 'nif' => $user->getNif());

		$query = "SELECT * FROM users where username=:username OR email=:email OR nif=:nif";

		try{
			$result = $this->_myDbManager->executeQuery($query,$data);

			if ($result->rowCount() == 0){
				//none of the data inserted exists in the database
				return(false);
			}
			else{
				$existingElements = array('username' => false, 'email' => false, 'nif' => false);
				while ( $row = $result->fetch() ){
					//which elements do exist in the database
					if( $row['username'] == $data['username'] ){
						$existingElements['username'] = true;
					}
					if($row['email'] == $data['email'] ){
						$existingElements['email'] = true;
					}
					if($row['nif'] == $data['nif'] ){
						$existingElements['nif'] = true;
					}
				}
				return($existingElements);
			}
		}
		catch(InvalidArgumentException $e){
			throw new Exception("Check your data request.");
		}
		catch(Exception $e){
			throw $e;
		}
	}

	//delete a user from the database - there are not other tables related (for now). However, one could opt for disabling the user (while keeping its records) or erasing all related records from other tables before deleting its account
	public function deleteUser($user){
		
		//again, one can verify if the object received is as expected. Both previous methods can also implement this (I will do so later on)
		if ( !is_a($user,'User') ){
			throw new InvalidArgumentException("Invalid argument received.");
		}

		//get the user ID
		$data = array( 'userId' => $user->getUserId() );
		$query = "DELETE FROM users WHERE id_user=:userId";

		try{
			$result = $this->_myDbManager->executeQuery($query,$data);
			if ( $result->rowCount() == 1){
				return(true);
			}
			else{
				//something went terribly wrong
				return(false);
			}
		}
		catch(InvalidArgumentException $e){
			throw new Exception("Please check your request: invalid data.");
		}
		catch(Exception $e){
			throw $e;
		}
	}
		
	//get all Users and print their Usernames & Email (you can, however, retrieve all data available)
	public function getAllUsersData(){
	
		$data = array();
		$query = "SELECT username,email FROM users";

		try{
			$result = $this->_myDbManager->executeQuery($query,$data);

			if ($result->rowCount() == 0){
				//no users were found
				return(false);
			}
			else{
				//return users list
				return($result);
			}
		}
		catch(InvalidArgumentException $e){
			throw new Exception("Check your data request.");
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	//check if a given user exists
	public function userExists($userId){
	
		$data = array('userId' => $userId);
		$query = "SELECT * FROM users WHERE id_user=:userId";

		try{
			$result = $this->_myDbManager->executeQuery($query,$data);

			if ($result->rowCount() == 0){
				//user was not found
				return(false);
			}
			else{
				//yes, it exists
				return($result->fetch());
			}
		}
		catch(InvalidArgumentException $e){
			throw new Exception("Check your data request.");
		}
		catch(Exception $e){
			throw $e;
		}
	}
	
	//update (some) user data
	public function updateUserData($user, $newData){

		if (!is_a($user,'User')){
			throw new InvalidArgumentException("Please read the documentation.");
		}

		//assuming that newData contents are already validated
		//assuming that only a few data fields maybe changed (email, password, nif) and all are sent to this method at the same time
		
		/*first one needs to check if any field sent is different from the one that already exists in the database
		 *because if it is, we need to check if it already exists in the database (similar email, for example) 		
		*/

		//get the user password from the database
		$password = ($this->userExists($user->getUserId()))['password']; 

		$toCheck = array();		
		
		//email, nif & password. Are they different?
		if ( $user->getNif() != $newData['nif']){
			//user is trying to update this field - let's update our user object copy to see if it can be done
			$user->setNif($newData['nif']);
			$toCheck[] = 'nif';
 		}
		if( $user->getEmail() != $newData['email']){
			//user is trying to update this field - let's update our user object copy to see if it can be done
			$user->setEmail($newData['email']);
			$toCheck[] = 'email';
		}
		if( $password != md5($newData['password'])){
			//user is trying to update this field - this can be done
			$toCheck[] = 'password';
		}

		//do we need to make changes in the database?
		if (!empty($toCheck)){			
			//now, use the checkUser method, while maintaining the username (it is not important here, because we are not going to change it)
			$result = $this->checkUser($user);

			//remove the username because it is, indeed, always repeated
			unset($result['username']);

			foreach ($result as $field => $value) {
  				if (!in_array($field, $toCheck) || !$value) {
    					unset($result[$field]);
				}
			}

			if ( count($result) != 0 ){
				//there are elements that the user wants to change but are already in the database
				return($result);
			}
			else{
				//the changes to the database may proceed
				$data = array('email' => $user->GetEmail(), 'password' => md5($newData['password']), 'nif' => $user->getNif() );
				$query = "UPDATE users SET email=:email, nif=:nif, password=:password WHERE id_user=" . $user->getUserId();
 
				try{
					$result = $this->_myDbManager->executeQuery($query,$data);

					if ($result->rowCount() == 1){
						//user was updated - let's return the new and updated user object
						return($user);
					}
					else{
						//something went terribly wrong!
						return('Error');
					}
				}
				catch(InvalidArgumentException $e){
					throw new Exception("Check your data request.");
				}
				catch(Exception $e){
					throw $e;
				}		
			}										
		}					
		else{
			//all fields are the same to what already exists in the database - no changes needed
			return(false);		
		}
	}

	//now let's authenticate a user
	public function authUser($authData){

		/* $data contains both the username and password introduced in a login form.
		 * Again, they are considered to be valid before being sent here.
		*/

		$data = array('username' => $authData['username'],'password' => md5($authData['password']));
		$query = "SELECT * FROM users WHERE username=:username AND password=:password";
		
		try{
			$result = $this->_myDbManager->executeQuery($query,$data);
			if ($result->rowCount() == 1){
				//user was authenticated - initiate session
				$userData = $result->fetch();
				$authUser = new User($userData,'existingUser');				
				session_start();

				//generate a secure token to be saved in the session (protects against CRSF and can be used to many other things).
				$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(24)); 
				$_SESSION['user'] = serialize($authUser);
				return(true);
			}
			else{
				//something went terribly wrong!
				return(false);
			}
		}
		catch(InvalidArgumentException $e){
			throw new Exception("Check your data request.");
		}
		catch(Exception $e){
			throw $e;
		}
	}

	//method to logout a user
	public function logoutUser($token){
		
		//check if a session exists and if so, if the session token is equal to the one provided
		if ( session_status() == PHP_SESSION_ACTIVE && $_SESSION['token'] == $token ){
			//let's wipe out the session
			session_destroy();
			$_SESSION[]=array();
			return(true);
		}
		else{
			//something is wrong: this function should not be called under these circunstances
			return (false);
		}
	}

}//end class

?>
