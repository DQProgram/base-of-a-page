<?php
/* This is the class that represents a user from a web application
 * It has mainly getter and setter methods, as well as a constructor
 * that is able to differentiate if the new object is representing a
 * new user (that is going to be registered in the web application) or
 * an existent user obtained from e.g. the database.
 */
 
class User{

    private $_userID;
    private $_registerDate;
    private $_username;
    private $_email;
    private $_nif;
    private $_elementsNew = array('username','email','nif');
    private $_elementsExisting = array('username','email','nif','id_user','register_date');
	 private $_allowedOperations = array('new', 'existingUser');    
    
    public function __construct($data,$type){

		//please check that the requested operation is valid
		if ( !in_array($type,$this->_allowedOperations) ){
			//invalid operation requested
			throw new Exception ("Please read the manual to use this method correctly.");
		}	
	
	  	//check if $data has all required elements -- _elements -- to proceed
		if ( $type == "new"){
        	if ( count(array_intersect_key( $data, array_flip($this->_elementsNew))) === count($this->_elementsNew) ){
            	//update properties
            	$this->_username = $data['username'];
            	$this->_email = $data['email'];
            	$this->_nif = $data['nif'];
        	}
        	else{
            	throw new InvalidArgumentException("Wrong parameters. Please check your form.");
			}
		}
		elseif( $type == "existingUser"){
			if ( count(array_intersect_key($data, array_flip($this->_elementsExisting))) === count($this->_elementsExisting) ){
				//update properties
				$this->_username = $data['username'];
				$this->_email = $data['email'];
				$this->_nif = $data['nif'];
				$this->_registerData = $data['register_date'];
				$this->_userId = $data['id_user'];
			}
			else{
				throw new InvalidArgumentException("Wrong parameters. Please check your database.");
			}

		}	
    }

    public function getUsername(){
        return($this->_username);
    }
    
    public function getEmail(){
        return ($this->_email);
    }
    
    public function getNif(){
        return ($this->_nif);
    }

    public function getUserId(){
			return ( $this->_userId);
    }

	 public function getRegisterDate(){
			return ( $this->_registerDate);
    }    
    
    public function setUsername($username){
        $this->_username = $username;
    }
    
    public function setEmail($email){
        $this->_email = $email;
    }
    
    public function setNif($nif){
        $this->_nif = $nif;
    }

}//end class
?>