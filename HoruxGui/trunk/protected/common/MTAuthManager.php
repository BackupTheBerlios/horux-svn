<?php
/**
 * @author Artūras Šlajus <x11@arturaz.afraid.org>
 * @license http://creativecommons.org/licenses/LGPL/2.1/ CC-GNU LGPL
 */
 
Prado::using('System.Security.TAuthManager');
 
/**
 * MTAuthManager class.
 */
class MTAuthManager extends TAuthManager {
 
	public function init($config) {		
		if (! $this->_DBHandler)
			throw new TConfigurationException(
				'You must set DBHandler property!'
			);
		if (! $this->_PermissionsTable)
			throw new TConfigurationException(
				'You must set PermissionsTable property!'
			);

		parent::init($config);
	}
	
	/**
	 * Authorize with database
	 */
	public function OnAuthorize($param) {
		$app = $this->getApplication();

        if( $app->getService()->getID() == 'xmlrpc' ) return true;


        // if the soap request is done by the server himself, do not check the password
        if( $app->getService()->getID() == 'soap' &&
            $_SERVER[SERVER_ADDR] === $_SERVER[REMOTE_ADDR])
        {
            return;
        }

        if( $app->getService()->getID() == 'soap' )
        {

            if($app->getUser()->getUserID() == null)
            {

                $authManager=$app->getModule('Auth');
		$username="";
		$password="";

		if(!isset($this->Request['username']) && !isset($this->Request['password']))
		{
		  if($fp = fopen("./tmp/soapcache", "r"))
		  {
		      $line = fgets($fp);
		      $line = explode(",", $line);
		      $username = $line[0];
		      $password = $line[1];

		      fclose($fp);
		      unlink("./tmp/soapcache");
		  }
		}
		else
		{
		    $username = $this->Request['username'];
		    $password = $this->Request['password'];

		}

                //Check if the user has access
                if(!$authManager->login(strtolower($username),$password ))
                {
                    $this->DenyRequest();
                    return false;
                }
                else
                {
                    $isWebservice = $app->getUser()->getWebservice();

                    if($isWebservice == 0)
                    {
                        $this->DenyRequest();
                        return false;
                    }
                    else
		    {
			//if wsdl, else not
			if(isset($this->Request['soap']) &&  substr($this->Request['soap'], -5,5) == ".wsdl")
			{
			  if($fp = fopen("./tmp/soapcache", "w"))
			  {
			      fwrite($fp, $this->Request['username'].",".$this->Request['password'].",");
			      fclose($fp);
			  }
			}
                        return true;
		    }
                }
            }
            else
            {
                $isWebservice = $app->getUser()->getWebservice();

                if($isWebservice == 0)
                {
                    $this->DenyRequest();
                    return false;
                }
                else
                    return true;
            }
        }

        if('controlPanel.ControlPanel' == $app->getService()->getRequestedPagePath() &&  $app->getUser()->getUserID() != null) return true;
        if('help.About' == $app->getService()->getRequestedPagePath() &&  $app->getUser()->getUserID() != null) return true;

		if($app->getService()->getRequestedPagePath() != "install.install")
		{
	
	      	$db = $app->getModule($this->_DBHandler)->DbConnection;
	      	$db->Active=true;
		
			$usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID(); 
			$groupId = $app->getUser()->getGroupID() == null ? 0 : $app->getUser()->getGroupID(); 
			
			$sql = 	'SELECT `allowed` FROM '.$this->_PermissionsTable.' WHERE ' .
					'(`page`=\''.$app->getService()->getRequestedPagePath().'\' OR `page` IS NULL) ' .
					"AND (" .
						"(`selector`='user_id' AND `value`=".$usedId.") " .
						"OR (`selector`='group_id' AND `value`=".$groupId.") " .
					")" .
				'ORDER BY `page` DESC';
	
			$cmd = $db->createCommand($sql);
			$res = $cmd->query();	
			$res = $res->readAll();	
			// If there were no results
			if (! $res)
				// And default is deny
				if (! $this->_Default)
					$this->DenyRequest();
			else
				// Traverse results
				foreach ($res as $allowed)
					// If we get deny here
					if (! $allowed)
						$this->DenyRequest();
	
		}

	}
	
	/**
	 * Deny request.
	 */
	private function DenyRequest() {
		$this->getApplication()->getResponse()->setStatusCode(401);
		$this->getApplication()->completeRequest();
	}
	
	/**
	 * @var String Default value for DBHandler
	 */
	private $_DBHandler = NULL;
	
	/**
	 * @return String DBHandler
	 */
	public function getDBHandler() {
		return $this->_DBHandler;
	}
	/**
	 * @param String DBHandler
	 */
	public function setDBHandler($DBHandler) {
		$DBHandler = TPropertyValue::ensureString($DBHandler);
		
		if (is_null($this->Application->getModule($DBHandler)))
			throw new TConfigurationException(
				"No module with such ID: $DBHandler"
			);
		
		$this->_DBHandler = $DBHandler;
	}
 
	/**
	 * @var String Default value for PermissionsTable
	 */
	private $_PermissionsTable = NULL;
	
	/**
	 * @return String PermissionsTable
	 */
	public function getPermissionsTable() {
		return $this->_PermissionsTable;
	}
	/**
	 * @param String PermissionsTable
	 */
	public function setPermissionsTable($value) {
		$this->_PermissionsTable = TPropertyValue::ensureString($value);
	}
	
	/**
	 * @var Boolean Default value for Default
	 */
	private $_Default = FALSE;
	
	/**
	 * @return Boolean Default
	 */
	public function getDefault() {
		return $this->_Default;
	}
	/**
	 * @param Boolean Default
	 */
	public function setDefault($value) {
		$this->_Default = TPropertyValue::ensureBoolean($value);
	}
	
	
}
 
?>