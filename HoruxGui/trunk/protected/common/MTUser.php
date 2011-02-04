<?php
 
class MTUser extends TComponent implements IUser {
	/**
	 * @var TUserManager user manager
	 */
	private $_Manager;
	
	/**
	 * Constructor.
	 * @param TUserManager user manager
	 */
	public function __construct($Manager=NULL) {
		$this->_Manager = $Manager;
	}
 
	/**
	 * @return TUserManager user manager
	 */
	public function getManager() {
		return $this->_Manager;
	}
	
	// Following 2 functions makes most work in this class. They are 
	// responsible of saving and restoring user instance to/from session.
 
	/**
	 * @return string user data that is serialized and will be stored in session
	 */
	public function saveToString() {
		return serialize(
			array(
				$this->_Name,				
				$this->_SuperAdmin,				
				$this->_Group,
				$this->_IsGuest,
				$this->_UserID,
				$this->_GroupID,
                $this->_Webservice
			)
		);
	}
 
	/**
	 * @param string user data that is serialized and restored from session
	 * @return IUser the user object
	 */
	public function loadFromString($data) {
		if (!empty($data)) {
			$array = unserialize($data);
			$this->_Name = $array[0];			
			$this->_SuperAdmin = $array[1];			
			$this->_Group = $array[2];
			$this->_IsGuest = $array[3];
			$this->_UserID = $array[4];
			$this->_GroupID = $array[5];
            $this->_Webservice = $array[6];
		}
		return $this;
	}
	
	// Fake methods! We don't really need these but IUser insists on 'em.
	public function getRoles() {}
	public function setRoles($value) {}	
	public function isInRole($value) {}
 
	// Just accessors below.
 
	/**
	 * @var String Default value for Name
	 */
	private $_Name = NULL;
	
	/**
	 * @return String Name
	 */
	public function getName() {
		return $this->_Name;
	}
	/**
	 * @param String Name
	 */
	public function setName($value) {
		$this->_Name = TPropertyValue::ensureString($value);
	}

	/**
	 * @var String Default value for SuperAdmin
	 */
	private $_SuperAdmin = 0;
	
	/**
	 * @return Int SuperAdmin
	 */
	public function getSuperAdmin() {
		return $this->_SuperAdmin;
	}
	/**
	 * @param Int SuperAdmin
	 */
	public function setSuperAdmin($value) {
		$this->_SuperAdmin = TPropertyValue::ensureBoolean($value);
	}

	
	/**
	 * @var String Default value for Group
	 */
	private $_Group = NULL;
	
	/**
	 * @return String Group
	 */
	public function getGroup() {
		return $this->_Group;
	}
	/**
	 * @param String Group
	 */
	public function setGroup($value) {
		$this->_Group = TPropertyValue::ensureString($value);
	}
	
	/**
	 * @var Integer Default value for UserID
	 */
	private $_UserID = NULL;
	
	/**
	 * @return Integer UserID
	 */
	public function getUserID() {
		return $this->_UserID;
	}
	/**
	 * @param Integer UserID
	 */
	public function setUserID($value) {
		$this->_UserID = TPropertyValue::ensureInteger($value);
	}
	
	/**
	 * @var Integer Default value for GroupID
	 */
	private $_GroupID = NULL;
	
	/**
	 * @return Integer GroupID
	 */
	public function getGroupID() {
		return $this->_GroupID;
	}
	/**
	 * @param Integer GroupID
	 */
	public function setGroupID($value) {
		$this->_GroupID = TPropertyValue::ensureInteger($value);
	}
	
	/**
	 * @var Boolean Default value for IsGuest
	 */
	private $_IsGuest = TRUE;
	
	/**
	 * @return Boolean IsGuest
	 */
	public function getIsGuest() {
		return $this->_IsGuest;
	}
	/**
	 * @param Boolean IsGuest
	 */
	public function setIsGuest($value) {
		$this->_IsGuest = TPropertyValue::ensureBoolean($value);
	}

	/**
	 * @var String Default value for webservice
	 */
	private $_Webservice = 0;

	/**
	 * @return Bool Webservice
	 */
	public function getWebservice() {
		return $this->_Webservice;
	}
	/**
	 * @param Bool Webservice
	 */
	public function setWebservice($value) {
		$this->_Webservice = TPropertyValue::ensureBoolean($value);
	}

}
 
?>