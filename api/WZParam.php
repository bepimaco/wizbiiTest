<?php


/**
 * Management API parameters
 * 
 * @access public
 * @name WZParam
 * @author Benjamin COIFFARD
 * @version 1.0.0
 */
/*------------------------------------------------------------------------------
	@method __construct
	@method __call
	@method __get
	@method loadParameters
	@method findEnvironment
	@method getParameters
------------------------------------------------------------------------------*/
class WZParam
{

// Set class properties
	private $paramSet;
	private $environment;


/**
 * Constructor of WZParam Class
 * 
 * The constructor build each instance of this object with the complete set of
 * parameters.
 * 
 * @access public
 * @name __construct
 * @param none
 * @return void
 */
/*----------------------------------------------------------------------------*/
	public function __construct()
	{
		
	// Define environment to load appropriate config file
		$this->environment = $this->findEnvironment();

	// Load parameters (timestamps can be used to load only once during a defined period)
		try{$this->loadParameters();}
		catch(Exception $e){throw $e;}

	}


/**
 * Magic __call function
 * 
 * @access public
 * @name __call
 * @param string $name name of the called function
 * @param array $arguments list of arguments send with the call
 * @return bool false
 */
/*----------------------------------------------------------------------------*/
	public function __call($name, $arguments)
	{
		throw new Exception('ERROR | Function '.__CLASS__.'.'.$name.'() does not exists. Arguments received: ['.serialize($arguments).']', 8888);
	}


/**
 * Magic __get function
 * 
 * @access public
 * @name __get
 * @param string $name name of the requested local property
 * @return bool false
 */
/*----------------------------------------------------------------------------*/
	public function __get($name)
	{
		switch(strtolower($name))
		{
			case 'environment': return $this->environment;
			case 'queuetimevalue': return $this->paramSet['queue_time_value'];
			default:
				throw new Exception('ERROR | Property '.__CLASS__.'.'.$name.' does not exists in parameters file.', 8888);
		}
	}


/**
 * Load config file
 * 
 * Configuration can be store in any available support, and then loaded. They
 * also can be stored in PHP session file, and load only once each hour.
 * 
 * @access private
 * @name loadParameters
 * @param none
 * @return none
 */
/*----------------------------------------------------------------------------*/
	private function loadParameters()
	{

	// Mocked parameters properties
		$configFile = array
		(
			'local' =>array
			(
				'project' => 'WizBiiTest',
				'url' => 'http://localhost/wizbii',
				'path' => 'D:\\Programmes\\wamp\\www\\wizbii\\',
				'mongo_host' => '127.0.0.1',
				'mongo_db' => 'wizbii',
				'mongo_collection' => 'analytics',
				'log_folder'=> 'D:\\Programmes\\wamp\\www\\wizbii\\logs\\',
				'log_file'=> 'traceLog',
				'log_extension' => 'txt',
				'queue_time_value' => 3600
			),
			'prod' =>array
			(
				'project' => 'WizBiiTest',
				'url' => 'http://analytics.wizbii.com',
				'path' => '/analytics',
				'mongo_host' => '1.1.1.1',
				'mongo_db' => 'wizbii',
				'mongo_collection' => 'analytics',
				'log_folder'=> '/analytics/logs/',
				'log_file'=> 'traceLog',
				'log_extension' => 'txt',
				'queue_time_value' => 3600
			),
		);
		
	// Check config file: must be a non empty array
		if ( (!is_array($configFile[$this->environment])) || (empty($configFile[$this->environment])) )
			throw new Exception ('Config file does not exists');
		
	// Copy datas from config file (some datas could be analyzed befor)
		$this->paramSet = $configFile[$this->environment];

	}


/**
 * Find environment from server datas
 * 
 * @access private
 * @name findEnvironment
 * @param none
 * @return string environment
 */
/*----------------------------------------------------------------------------*/
	private function findEnvironment()
	{
	
	// Use HTTP_HOST to define environment
		if (isset($_SERVER['HTTP_HOST']))
		{
				
		// Case 1: local
			if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
				$this->environment = 'local';
				
		// Case 2: prod
			else $this->environment = 'prod';

		}
		
	// Default environment is 'prod'
		else $this->environment = 'prod';
		
	// Return environement
		return $this->environment;	
	
	}


/**
 * Get parameters values
 * 
 * @access private
 * @name getParameters
 * @param string $paramName
 * @param boolean $securityCheck
 * @return string parameter value
 */
/*----------------------------------------------------------------------------*/
	public function getParameters($paramName, $securityCheck=false)
	{

	// Give mongo grants, after security check
		if ($paramName == 'mongo_grants')
		{
			
		// Check security
			if (!$securityCheck)
				throw new Exception('Mongo parameters can not be read without valid security check');
			
		// Build mongo grants
			return array
			(
				'mongo_host'=>$this->paramSet['mongo_host'],
				'mongo_db'=>$this->paramSet['mongo_db'],
				'mongo_collection'=>$this->paramSet['mongo_collection']
			);
		}
		
	// Give mongo grants, after security check
		if ($paramName == 'log_parameters')
		{
			return array
			(
				'log_folder'=>$this->paramSet['log_folder'],
				'log_file'=>$this->paramSet['log_file'],
				'log_extension'=>$this->paramSet['log_extension']
			);
		}

	// Select parameter to get
		switch($paramName)
		{
			
		// Some parameters can not be read
			case 'mongo_host':
			case 'mongo_db':
			case 'mongo_collection':
				throw new Exception('Mongo parameters can not be read without valid security check');
				
		// All other parameters can be read
			default: return $this->paramSet[$paramName];
		}

	}


}
