 <!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>PDO Example</title>
</head>
<body>
<!-- Include PHP files and try the PDO example -->
<?php	
/*require_once('classes/User.php');
require_once('classes/UserManager.php');
require_once('classes/Validation.php');
*/
// autoload classes, when needed.
spl_autoload_register(function ($class) {
  require_once 'classes/' . $class . '.php';
});

//test user class
try{
	$data = array('username' => 'Maria', 'email' => 'maria1@aki.com', 'nif'=> '222222222' );
	$myUser = new User($data,'new');
}
catch(Exception $e){
	echo $e->getMessage();
}

//test insert user, search user and delete user
try{
	$password = "12345678"; //test password as if it came from a form
	$myUserManager = new UserManager();
	
	//before registering the user, we must check if any of the fundamental identifiers exist in the database: email, username and nif.

	if ( is_array($result= $myUserManager->checkUser($myUser)) ){
		//this means that one or more identifiers exist. We can show exactly what is wrong to the user trying to register
		$result = array_keys($result,true);
		foreach ( $result as $field => $value){
			echo "This " . $value. " exists: please change it" . "<br />";
		}
	}
	else{
		$result = $myUserManager->registerUser($myUser,$password);
		if ($result){
			echo ("User registered successfully");
		}
		else{
			echo "Error: " . $result;
		}
	}

	//erase user only if it exists
	$idToErase = 9; //change it to test it
	if ( $myUserManager->userExists($idToErase)){
		$data = array('username' => 'Maria', 'email' => 'maria@aki.com', 'nif'=> '222222222', 'register_date' => "123456765432", 'id_user'=> $idToErase);
		$myUser = new User($data,'existingUser');
		$result = $myUserManager->deleteUser($myUser);
		if( $result ){
			echo "User account deleted.";
		}
		else{
			echo "Internal error: please contact us as soon as possible.";
		}	
	}
	else{
		echo "It is not possible to erase that user as it does not exist. Please check the id." . "<br />";	
	}



	//test list all users method
	echo "<br /><br /><br />";
	$result = $myUserManager->getAllUsersData();
	if ( !$result){
		echo "No users found...";
	}
	else{
		while ($row = $result->fetch()){
			echo "username: " . $row['username'] . "       " . "email: " . $row['email'] . "<br />";
		}
	}
	
	
	
	//test update user
	$userId = 1;
	$data = $myUserManager->userExists($userId);
	$myUser = new User($data,'existingUser');

	$newData = array('nif' => '444444444','email'=>'mhorta@gmail.pt','password'=>'123456789');

	$result = $myUserManager->updateUserData($myUser,$newData);
	if ( !$result){
		echo "Nothing changed! Are you kidding me?";
	}
	else{
		if( is_a($result,'User') ){
			//data was updated
			echo ("User Data Updated! <br />");
		}		
		elseif(is_array($result)){
			print_r($result);
		}
		else{
			echo("A severe error has occurred. Please try again later.");		
		}
		
	}	

	
	//test user auth
	$data = array('username' => 'marcohorta', 'password'=>'123456789');
	$result = $myUserManager->authUser($data);
	if ($result){
		echo "User logged in!<br />";
		if (session_status() != PHP_SESSION_ACTIVE ){
			session_start();
		}

		$myUser = unserialize($_SESSION['user']);
		echo "Hello " . $myUser->getUsername();
		echo "Token: " . $_SESSION['token'];
	}
	else{
		echo "Error authenticating.";
	}

	//try logout
	$result = $myUserManager->logoutUser($_SESSION['token']);
	if ( $result){
		echo "Logout successfull. <br />";
	}
	else{
		echo "Error logging out!";
	}


	//now let's test the validation form fields class
	$email = 'teste';
	$username = 'eperes';
	$password = '12345678';
	$nif = 111111111;
	$data = array('username' => $username, 'email' => $email, 'nif' => $nif, 'password' => $password);
	$myValidator = new Validation($data);

	$result = $myValidator->validateForm('registerUser',$data);

	echo "<br /><br />";
	if (is_array($result)){
		print_r($result);
	}
	else{
		echo "Form fields are all valid.";
	}

}
catch(InvalidArgumentException $e){
	echo $e->getMessage();
	die();
}
catch(Exception $e){
	echo $e->getMessage();
 	die();
}
?>
</body>
</html> 
