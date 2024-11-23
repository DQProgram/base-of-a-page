<?php

/* PDO example. Database credentials are saved in an outside (non-php) file. Indeed, this file should be placed outside web root, for extra security */

class DbManagerPDO{

	private $_myDb = null;
	private $_pathToConfigFile = 'varios/dbconfig.env';

	public function __construct(){
	
		//open the database configuration file, if it exists
		if ( !file_exists( $this->_pathToConfigFile ) ){
			//the configuration file is not accessible. This means that the web application will not work. It can be a path issue. Please check it.
			throw new InvalidArgumentException("Service is unavailable right now. Please try again later.");
		}
		else{
			//let's read and parse the configuration file to obtain the database configuration elements.
			$dbConfigElements = parse_ini_file($this->_pathToConfigFile);

			//check if the reading was successful. Otherwise, kill the application. A DB connection will not be established.			
			if (!$dbConfigElements){
				throw new InvalidArgumentException("Service is unavailable right now. Please try again later.");
			}
			else{
				// all went well. Let's connect to the database by setting the host, database name and charset read from the configuration file.
				$config = "mysql:host=" . $dbConfigElements['hostname'] . 
							 ";dbname=" . $dbConfigElements['dbName'] .
							 ";charset=". $dbConfigElements['charset'];
				
				//let's define some options: Errors are send using exceptions, return values are in associative arrays and use prepared statements (if available)
				$options = array(
							 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
							 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
							 PDO::ATTR_EMULATE_PREPARES   => false,
							 );
				// because the PDO constructor can throw an exception, we must be ready to catch it				
				try {
					//if all goes well, the connection is saved on the proper attribute. 
     				$this->_myDb = new PDO($config, $dbConfigElements['username'], $dbConfigElements['password'], $options);
				} 
				catch (PDOException $e) {
     				throw new Exception('Fatal Error. The application will be unavailable for the time being. Please try again later.');
				}				
			}
		}
	} //end construct
		
	/* To execute a query benefiting from the PDO added security, we must leave our old ways and use placeholders (named, by option) in our statements. To do so in a generic
	 * way may be a little tricky. We can use a generic method and expect to have both the query and the parameters (associative array) passed on to it.
	*/	
	public function executeQuery( $query, $orderedParameters){
		//a simple check to see if both the query and the parameters are from the correct types (i.e. string & array)
		if ( !is_string($query) || !is_array($orderedParameters) ){
			//error. We must tell the programmer what it should send
			throw new InvalidArgumentException('Wrong arguments types.');
		}
		else{
			//let's go through all the necessary steps: 1) prepare the query; 2) execute the query. Again, the prepare statement can issue an exception, so be ready for it.
			try{
				$statement = $this->_myDb->prepare($query);
				if ( !$statement->execute($orderedParameters)){
					// an error executing the query has occurred. Notify the caller.
					throw new Exception('Bad writing day. Use a good notebook please.');
				}
				else{
					//success!!! You can do what you like with the results. In this case, we are going to return them (statement PDO format).				
					return($statement);
				}
			}
			catch(PDOException $e){
				throw new Exception('Our server is having a bad day with you. Let it rest, please.');
			}			
		}	
	} //end executeQuery Method
		
	//close the PDO connection
	public function closeDb() {
		// you only have to change the attribute value to null to do so.
		$this->_myDb = null;
		
		//only success may happen here.
		return(true);
	}
	
} //end class
