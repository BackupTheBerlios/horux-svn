<?php
/**
 * @author Artūras Šlajus <x11@arturaz.afraid.org>
 * @license http://creativecommons.org/licenses/LGPL/2.1/ CC-GNU LGPL
 */
 
// We'll need our user class later.
Prado::using('MTUser');
 
// Every user manager should implement IUserManager.
class MTUserManager extends TModule implements IUserManager {
	
	// This basically checks if all needed properties are set and calls 
	// parent init method.
	public function init($config) {		
		if (! $this->_DBHandler)
			throw new TConfigurationException(
				'You must set DBHandler property!'
			);
		if (! $this->_UserTable)
			throw new TConfigurationException(
				'You must set UserTable property!'
			);
		if (! $this->_GroupTable)
			throw new TConfigurationException(
				'You must set GroupTable property!'
			);
		
		parent::init($config);
	}
 
	// validateUser() authentificates given $name and $password against DB.
 
	/**
	 * @see TUserManager::validateUser()
	 */
	public function validateUser($name, $password) {
		// NULL or empty values are not allowed
		if (is_null($password) || $password === '')
			return FALSE;
			
		// MD5 and cleartext are not safe! :-)
		$password = sha1($password);		
		$app = $this->getApplication();
		$db = $app->getModule($this->_DBHandler)->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        
        $db->Active = true;

	        // We get our data from DB.
		$sql = 'SELECT `id`, `name`, `password` ' .
			'FROM `'.$this->_UserTable.'` ' .
			'WHERE `name`=:name AND `password`=:password';

		$cmd = $db->createCommand($sql);
		$cmd->bindValue(':name', $name,PDO::PARAM_STR );
		$cmd->bindValue(':password', $password,PDO::PARAM_STR );
		$res = $cmd->query();	
		$row = $res->read();	

		// NULL means we have disabled account
		if (is_null($row['password']))
			return FALSE;
		$now = date("Y-n-j G:i:s");
		$sql = "UPDATE ".$this->_UserTable." SET isLogged=1, session_id='".session_id()."', lastConnection='".$now."' WHERE id=".$row['id'];
        $cmd = $db->createCommand($sql);
		$res = $cmd->execute();

		return true;
	}
 
	// After validateUser() this method is called by TAuthManager.
	// It simply instantiates and returns instance of IUser.
	/**
	 * @see TUserManager::getUser()
	 */
	public function getUser($name = NULL) {
		// If no username was given then it must be guest.
		if (is_null($name)) {
			$user = new MTUser($this);
			$user->IsGuest = TRUE;
			return $user;
		}
		else {
			$app = $this->getApplication();
    	  	$db = $app->getModule($this->_DBHandler)->DbConnection;
			
			// Get data from DB.
			$sql = 'SELECT ' .
					'`u`.`id`,' .
					'`u`.`group_id`,' .
					'`g`.`name`, ' .
					'`g`.`superAdmin`, ' .
					'`g`.`webservice` ' .
					
				'FROM `'.$this->_UserTable.'` as `u`  ' .
								
				'LEFT JOIN ' .
					'`'.$this->_GroupTable.'` as `g` ' .
				'ON ' .
					'`u`.`group_id`=`g`.`id` ' .
					
				'WHERE `u`.`name`=:name ';

			$cmd = $db->createCommand($sql);
			$cmd->bindValue(':name', $name,PDO::PARAM_STR );
			$res = $cmd->query();	
			$row = $res->read();				
			// If we have such user in DB then create new User and
			// return it.
			if ($row) {
				$user = new MTUser($this);
				$user->IsGuest = FALSE;
				$user->Name = $name;
				$user->Group = $row['name'];
				$user->superAdmin = $row['superAdmin'];
				$user->UserID = $row['id'];
				$user->GroupID = $row['group_id'];
				$user->Webservice = $row['webservice'];
				return $user;
			}
			else
				return NULL;
		}
	}
	
	// Simply replace $user with guest user instance.
 
	/**
	 * @see TUserManager::switchToGuest()
	 */
	public function switchToGuest($user) {
		$user = $this->getUser();
	}	
	
	// Just accessors below. One thing to mention: setDBHandler checks if
	// passed module name really represents application module.
 
	private $_DBHandler;
	/**
	 * @return string DB handler ID
	 */
	public function getDBHandler() {
		return $this->_DBHandler;
	}
	/**
	 * @param string DB handler ID
	 */
	public function setDBHandler($DBHandler) {
		$DBHandler = TPropertyValue::ensureString($DBHandler);
		
		if (is_null($this->Application->getModule($DBHandler)))
			throw new TConfigurationException(
				"No module with such ID: $DBHandler"
			);
		
		$this->_DBHandler = $DBHandler;
	}
	
	private $_UserTable;
	/**
	 * @return string user table
	 */
	public function getUserTable() {
		return $this->_UserTable;
	}
	/**
	 * @param string user table
	 */
	public function setUserTable($UserTable) {
		$this->_UserTable = TPropertyValue::ensureString($UserTable);
	}
	
	private $_GroupTable;
	/**
	 * @return string group table
	 */
	public function getGroupTable() {
		return $this->_GroupTable;
	}
	/**
	 * @param string group table
	 */
	public function setGroupTable($GroupTable) {
		$this->_GroupTable = TPropertyValue::ensureString($GroupTable);
	}
	
	/**
	 * @var String Default value for GuestName
	 */
	private $_GuestName = 'Guest';
	
	/**
	 * @return String GuestName
	 */
	public function getGuestName() {
		return $this->_GuestName;
	}
	/**
	 * @param String GuestName
	 */
	public function setGuestName($value) {
		$this->_GuestName = TPropertyValue::ensureString($value);
	}
	
	public function getUserFromCookie($cookie)
	{
		return null;	
	}

	public function saveUserToCookie( $cookie)
	{
	}

}
?>