<?php
/* This is the class that was designed to deal with form validation
 * processes.
 */
 
class Validation{

	//forms that this class is ready to deal with
	private $_allowedForms = array('registerUser');
	private $_lengthsByFieldType = array('username' => array('min' => 4,'max'=> 30), 
													 'nif' => 9, 
													 'password' => array('min' => 8,'max'=> 64)
													 ); # these values can be updated in the constructor by reading then from a external file.
	
	public function validateForm($formName,$formData){
		
		//is the form to be validated recognized?
		if ( !in_array($formName,$this->_allowedForms) ){
			throw new InvalidArgumentException('This class is not ready to deal with ' . $formName . " at the moment.");
		}		
		
		//choose which method to call by considering the formName passed on		
		switch($formName){
		
			case 'registerUser': $result = $this->registerUserForm($formData);
										return($result);
										break;
										
			default: return(false);
						break;		
		}
	}

	//validate the user register form
	private function registerUserForm($formData){
		
		$errorsArray = array('username' => array('error' => false, 'errorMessage' => 'Invalid Username. It must have between ' . $this->_lengthsByFieldType['username']['min'] . 'and ' . $this->_lengthsByFieldType['username']['max'] . ' chars'),
								   'password' => array('error' => false,'errorMessage' => 'Invalid Password. It must have between ' . $this->_lengthsByFieldType['password']['min'] . 'and ' . $this->_lengthsByFieldType['password']['max'] . ' chars, including at least one lowercase and one uppercase letter, one number and a special char !@#%&*.'),
								   'nif' => array('error' => false,'errorMessage' => 'This field must have ' . $this->_lengthsByFieldType['nif'] . ' numbers.'),
								   'email' => array('error' => false,'errorMessage' => 'Invalid email.')
								  );

		//check if the data array sent has all the needed fields
		if ( count(array_diff(array_keys($errorsArray), array_keys($formData))) != 0){
			//the arrays are not the same. Something is wrong and both the required fields, errors array and from data sent may need to be corrected 		
			throw new InvalidArgumentException('Form data mismatches. Please correct it.');
		}

		//now check each field using this class methods
		$formDataError = false;
		
		if ( !$this->checkEmail($formData['email']) ){
			$errorsArray['email']['error'] = true;
			$formDataError = true;
		}
	
		if ( !$this->checkPassword($formData['password'], $this->_lengthsByFieldType['password'] )){
			$errorsArray['password']['error'] = true;
			$formDataError = true;
		}
	
		if ( !$this->checkInt($formData['nif'],$this->_lengthsByFieldType['nif']) ){
			$errorsArray['nif']['error'] = true;
			$formDataError = true;
		}
		
		if ( !$this->checkUsername($formData['username'], $this->_lengthsByFieldType['username']) ){
			$errorsArray['username']['error'] = true;
			$formDataError = true;
		}
	
		//if there is an error, informs the caller passing on the errors array. Otherwise, it returns true.
		if ( $formDataError ){
			return($errorsArray );
		}
		else{
			//all fields are valid
			return(true);		
		}
	}
	
	//methods to check individual fields and that can be used on multiple forms
	/* ----------------------------------------------------------------------------------------------------------- */
	private function checkEmail($email){

		//begin by removing all illegal chars from the value passed on
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		//check if the field has a valid email
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)){
			return(false);
		}		
		else{
			return(true);		
		}
	}	
	
	private function checkInt($nif,$length){
	
		$expression = '/^[0-9]{'. $length . '}$/';

		if ( !preg_match($expression,$nif) ){
			return(false);
		}
		else{
			return(true);		
		}
	}
	
	private function checkPassword($password,$length){

		//retrieved and modified from https://www.thepolyglotdeveloper.com/2015/05/use-regex-to-test-password-strength-in-javascript/	
		$expression = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#%&\*])(?=.{'. $length['min'] . ',' . $length['max'] . '})/';
		
		if ( !preg_match($expression,$password) ){
			return(false);
		}
		else{
			return(true);		
		}
	}
	
	private function checkUsername($username,$length){

		$expression = '/^([a-z0-9]{'. $length['min'] . ',' . $length['max'] . '})$/';
		
		if ( !preg_match($expression,$username) ){
			return(false);
		}
		else{
			return(true);		
		}
	}
	
	/* ----------------------------------------------------------------------------------------------------------- */
	
	
}//end class
?>