<?php
/**
 * TXmlRpcService and TXmlRpcServer class file
 *
 * @author Gyger Jean-Luc <jean-luc.gyger@letux.ch>
 * @link http://www.letux.ch/
 * @copyright Copyright &copy; 2009 Letux
 * @license http://www.pradosoft.com/license/
 * @package System.Web.Services
 */

/**
 * TXmlRpcService class
 *
 * TSoapService processes XMLRPC requests for a PRADO application.
 * TSoapService requires PHP XMLRPC extension to be loaded.
 *
 *
 * Each <xmlrpc> element in the application specification actually configures
 * the properties of a XMLRPC server which defaults to {@link TXmlRpcServer}.
 * Therefore, any writable property of {@link TXmlRpcServer} may appear as an attribute
 * in the <xmlrpc> element. For example, the "provider" attribute refers to
 * the {@link TXmlRpcServer::setProvider Provider} property of {@link TXmlRpcServer}.
 * The following configuration specifies that the XMLRPC server is persistent within
 * the user session (that means a MyStockQuote object will be stored in session)
 * <code>
 *   <services>
 *     <service id="xmlrpc" class="System.Web.Services.TXmlRpcService">
 *       <xmlrpc id="stockquote" provider="MyStockQuote" SessionPersistent="true" />
 *     </service>
 *   </services>
 * </code>
 *
 * You may also use your own XMLRPC server class by specifying the "class" attribute of <xmlrpc>.
 *
 * @author Gyger Jean-Luc <jean-luc.gyger@letux.ch>
 * @package System.Web.Services
 */

require_once( 'XML/RPC/Server.php' );
require_once( 'XML/RPC.php' );

class TXmlRpcService extends TService
{
	const DEFAULT_XMLRPC_SERVER='TXmlRpcServer';
	const CONFIG_FILE_EXT='.xml';
	private $_servers=array();
	private $_configFile=null;
	private $_serverID=null;

	/**
	 * Constructor.
	 * Sets default service ID to 'xmlrpc'.
	 */
	public function __construct()
	{
		$this->setID('xmlrpc');
	}

	/**
	 * Initializes this module.
	 * This method is required by the IModule interface.
	 * @param TXmlElement configuration for this module, can be null
	 * @throws TConfigurationException if {@link getConfigFile ConfigFile} is invalid.
	 */
	public function init($config)
	{
		if($this->_configFile!==null)
		{
 			if(is_file($this->_configFile))
 			{
				$dom=new TXmlDocument;
				$dom->loadFromFile($this->_configFile);
				$this->loadConfig($dom);
			}
			else
				throw new TConfigurationException('xmlrpcservice_configfile_invalid',$this->_configFile);
		}
		$this->loadConfig($config);

		$this->resolveRequest();
	}

	/**
	 * Resolves the request parameter.
	 * It identifies the server ID.
	 * @throws THttpException if the server ID cannot be found
	 * @see getServerID
	 */
	protected function resolveRequest()
	{
		$serverID=$this->getRequest()->getServiceParameter();
		$this->_serverID=$serverID;

		if(!isset($this->_servers[$serverID]))
			throw new THttpException(400,'xmlrpcservice_request_invalid',$serverID);
	}

	/**
	 * Loads configuration from an XML element
	 * @param TXmlElement configuration node
	 * @throws TConfigurationException if soap server id is not specified or duplicated
	 */
	private function loadConfig($xml)
	{
		foreach($xml->getElementsByTagName('xmlrpc') as $serverXML)
		{
			$properties=$serverXML->getAttributes();
			if(($id=$properties->remove('id'))===null)
				throw new TConfigurationException('xmlrpcservice_serverid_required');
			if(isset($this->_servers[$id]))
				throw new TConfigurationException('xmlrpcservice_serverid_duplicated',$id);
			$this->_servers[$id]=$properties;
		}
	}

	/**
	 * @return string external configuration file. Defaults to null.
	 */
	public function getConfigFile()
	{
		return $this->_configFile;
	}

	/**
	 * @param string external configuration file in namespace format. The file
	 * must be suffixed with '.xml'.
	 * @throws TInvalidDataValueException if the file is invalid.
	 */
	public function setConfigFile($value)
	{
		if(($this->_configFile=Prado::getPathOfNamespace($value,self::CONFIG_FILE_EXT))===null)
			throw new TConfigurationException('xmlrpcservice_configfile_invalid',$value);
	}

	/**
	 * Constructs a URL with specified page path and GET parameters.
	 * @param string soap server ID
	 * @param array list of GET parameters, null if no GET parameters required
	 * @param boolean whether to encode the ampersand in URL, defaults to true.
	 * @param boolean whether to encode the GET parameters (their names and values), defaults to true.
	 * @return string URL for the page and GET parameters
	 */
	public function constructUrl($serverID,$getParams=null,$encodeAmpersand=true,$encodeGetItems=true)
	{
		return $this->getRequest()->constructUrl($this->getID(),$serverID,$getParams,$encodeAmpersand,$encodeGetItems);
	}

	/**
	 * @return string the XMLRPC server ID
	 */
	public function getServerID()
	{
		return $this->_serverID;
	}

	/**
	 * Creates the requested XMLRPC server.
	 * The XMLRPC server is initialized with the property values specified
	 * in the configuration.
	 * @return TXmlRpcServer the XMLRPC server instance
	 */
	protected function createServer()
	{ 
		$properties=$this->_servers[$this->_serverID];
        if(($serverClass=$properties->remove('class'))===null)
			$serverClass=self::DEFAULT_XMLRPC_SERVER;
		Prado::using($serverClass);
		$className=($pos=strrpos($serverClass,'.'))!==false?substr($serverClass,$pos+1):$serverClass;
		if($className!==self::DEFAULT_XMLRPC_SERVER && !is_subclass_of($className,self::DEFAULT_XMLRPC_SERVER))
			throw new TConfigurationException('xmlrpcservice_server_invalid',$serverClass);
		$server=new $className;
		$server->setID($this->_serverID);
		foreach($properties as $name=>$value)
			$server->setSubproperty($name,$value);
		return $server;
	}

	/**
	 * Runs the service.
	 */
	public function run()
	{
		Prado::trace("Running XMLRPC service",'System.Web.Services.TXmlRpcService');
		$server=$this->createServer();
		$this->getResponse()->setContentType('text/xml');
		$this->getResponse()->setCharset('UTF-8');
    	// provide XMLRPC service
		Prado::trace("Handling XMLRPC request",'System.Web.Services.TXmlRpcService');
		$server->run();
	}
}


/**
 * TXmlRpcServer class.
 *
 * TXmlRpcServer is a wrapper of the PHP XMLRPCServer class.
 *
 * @author Gyger Jean-Luc <jean-luc.gyger@letux.ch>
 * @package System.Web.Services
 * @since 3.1
 */
class TXmlRpcServer extends TApplicationComponent
{
	private $_id;
	private $_provider;
	private $_server;

	/**
	 * @return string the ID of the XMLRPC server
	 */
	public function getID()
	{
		return $this->_id;
	}

	/**
	 * @param string the ID of the XMLRPC server
	 */
	public function setID($id)
	{
		$this->_id=$id;
	}

	/**
	 * Handles the XMLRPC request.
	 */
	public function run()
	{
        $server=$this->createServer();
    }

	/**
	 * Creates the XMLRPC server instance.
	 * @return XMLRPC server
	 */
	protected function createServer()
	{
		if($this->_server===null)
		{
            $provider=$this->getProvider();
            $providerClass=($pos=strrpos($provider,'.'))!==false?substr($provider,$pos+1):$provider;
            Prado::using($provider);

            $providerClass = new $providerClass;

            $providerClass = $providerClass ? $providerClass : this;

			$this->_server =  new XML_RPC_Server($providerClass->registerFunction() , 1 );
		}
		return $this->_server;
	}

	/**
	 * @return string the XMLRPC provider class (in namespace format)
	 */
	public function getProvider()
	{
		return $this->_provider;
	}

	/**
	 * @param string the XMLRPC provider class (in namespace format)
	 */
	public function setProvider($provider)
	{
		$this->_provider=$provider;
	}


    protected function registerFunction()
    {
        return NULL;
    }

}

