<?php


/**
 * Main class to manage stats for Analytics
 * 
 * @access public
 * @name WZStat
 * @author Benjamin COIFFARD
 * @version 1.0.0
 */
/*------------------------------------------------------------------------------
	@method __construct
	@method extractParameters
	@method addAnalyticError
	@method applyRules
	@method checkUser
	@method checkErrors
	@method displayErrors
	@method connectToMongoDB
	@method sendAnalyticsToMongoBase
------------------------------------------------------------------------------*/
class WZStat
{

// Set class properties
	private $WZParam;
	private $WZMongo;
	private $WZTrace;
	private $analyticsValues;
	private $analyticsError;
	
// Set properties, with boolean true for mandatory ones
	private $analyticsParameters = array
	(
		'v' => true,
		't' => true,
		'dl' => false,
		'dr' => false,
		'wct' => false,
		'wui' => false,
		'wuui' => false,
		'ec' => false,
		'ea' => false,
		'el' => false,
		'ev' => false,
		'tid' => true,
		'ds' => true,
		'cn' => false,
		'cs' => false,
		'cm' => false,
		'ck' => false,
		'cc' => false,
		'sn' => false,
		'an' => false,
		'av' => false,
		'qt' => false,
		'z' => false
	);
	


/**
 * Constructor of WZStat Class
 * 
 * @access public
 * @name __construct
 * @return void
 */
/*----------------------------------------------------------------------------*/
    public function __construct()
    {
		
	// Get parameters and trace ready
		try
		{
			$this->WZParam = new WZParam();
			$this->WZTrace = new WZTrace($this->WZParam->getParameters('log_parameters'));
		}
		catch (AlyException $e){throw $e;}
		catch (Exception $e) {throw $e;}
    }



	

/**
 * Extract parameters from $_REQUEST
 * 
 * @access public
 * @name extractParameters
 * @return void
 */
/*----------------------------------------------------------------------------*/
    public function extractParameters()
    {
		
	// Initialize
		$this->analyticsValues = [];
		$this->analyticsError = [];
		
	// Browse each value
		foreach($this->analyticsParameters as $key=>$element)
		{
			
		// Check GET or POST
			$p = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
			if ($p === NULL) $p = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
			
		// Add value 
			if ($p !== NULL)
				$this->analyticsValues[$key] = $p;
			
		// Check mandatory values
			if ( ($p === NULL) && ($element === true) )
				$this->addAnalyticError($key, 'missing');
			
		}
		
    }
	

/**
 * Register an error on analytics parameters
 * 
 * @access public
 * @name addAnalyticError
 * @return void
 */
/*----------------------------------------------------------------------------*/
    private function addAnalyticError($parameterName, $errorType, $errorDetails='')
    {
		
	// Build standard error object
		$error = array
		(
			'error' => $errorType,
			'parameter' => $parameterName,
		);
		
	// Add details if exists
		if ($errorDetails != '')
			$error['details'] = $errorDetails;
		
	// Add error to object properties
		$this->analyticsError[] = $error;
		
    }
	

/**
 * Apply rules defined in WizBii test
 * 
 * @access public
 * @name applyRules
 * @return void
 */
/*----------------------------------------------------------------------------*/
    public function applyRules()
    {
		
	// Rules depending on medium used
		if (isset($this->analyticsValues['cm']))
		{
			
		// Rules for mobiles
			if ($this->analyticsValues['cm'] == 'mobile')
			{

			// Wizbii creator type must be define
				if ( (!isset($this->analyticsValues['wct'])) || (empty($this->analyticsValues['wct'])) )
					$this->addAnalyticError('wct', 'missing');
				
			// Wizbii user ID must be define
				if ( (!isset($this->analyticsValues['wui'])) || (empty($this->analyticsValues['wui'])) )
					$this->addAnalyticError('wui', 'missing');

			// Application name type must be define
				if ( (!isset($this->analyticsValues['an'])) || (empty($this->analyticsValues['an'])) )
					$this->addAnalyticError('an', 'missing');
				
			}
			
		// Rules for web
			elseif ($this->analyticsValues['cm'] == 'web')
			{

			// Wizbii creator type must be define
				if ( (!isset($this->analyticsValues['wuui'])) || (empty($this->analyticsValues['wuui'])) )
					$this->addAnalyticError('wuui', 'missing');
				
			}
			
		// Rules for other media (if exists ...)
			else
			{

			// Application name type must be define
				if ( (!isset($this->analyticsValues['an'])) || (empty($this->analyticsValues['an'])) )
					$this->addAnalyticError('an', 'missing');
				
			}
		}
		
	// Rules depending on event hited
		if (isset($this->analyticsValues['t']))
		{
			
		// Rules for events
			if ($this->analyticsValues['t'] == 'event')
			{

			// Event category must be define
				if ( (!isset($this->analyticsValues['ec'])) || (empty($this->analyticsValues['ec'])) )
					$this->addAnalyticError('ec', 'missing');
				
			// Event action must be define
				if ( (!isset($this->analyticsValues['ea'])) || (empty($this->analyticsValues['ea'])) )
					$this->addAnalyticError('ea', 'missing');
				
			}
			
		// Rules for screenview
			elseif ($this->analyticsValues['t'] == 'screenview')
			{

			// Screen name type must be define
				if ( (!isset($this->analyticsValues['sn'])) || (empty($this->analyticsValues['sn'])) )
					$this->addAnalyticError('sn', 'missing');
				
			}
			
		}
		
	// If queue time is defined, it must stay beyhond value set in config file
		if ( isset($this->analyticsValues['qt']) || (!empty($this->analyticsValues['qt'])) )
		{
			if (intval($this->analyticsValues['qt']) > intval($this->WZParam->queueTimeValue) )
				$this->addAnalyticError('qt', 'queue_time_override', $this->WZParam->queueTimeValue);
		}
		
	// Only version 1 is supported
		if ( isset($this->analyticsValues['v']) || (!empty($this->analyticsValues['v'])) )
		{
			if (intval($this->analyticsValues['v']) != 1 )
				$this->addAnalyticError('v', 'version_not_supported');
		}
			
		
    }
	

/**
 * Check if user is registred in our database
 * 
 * @access public
 * @name checkUser
 * @param none
 * @return void
 */
/*----------------------------------------------------------------------------*/
    public function checkUser()
    {
		
	// Mocked list
		$userlist = array
		(
			'123' => array('wui'=>'123','name'=>'Jean DUPONT'),
			'456' => array('wui'=>'456','name'=>'Nicolas LEFRANC'),
			'789' => array('wui'=>'789','name'=>'Alice MARTIN'),
			'741' => array('wui'=>'741','name'=>'Pierre PECHU'),
			'852' => array('wui'=>'852','name'=>'Carine FITOU'),
			'963' => array('wui'=>'963','name'=>'Marc ROCHE'),
			'159' => array('wui'=>'159','name'=>'Stéphane VALET'),
			'357' => array('wui'=>'357','name'=>'Chloé GROLIER'),
			'emeric-wasson' => array('wui'=>'emeric-wasson','name'=>'Emeric WASSON'),
			'remi-alvado' => array('wui'=>'remi-alvado','name'=>'Rémi ALVADO'),
			'benjamin-coiffard' => array('wui'=>'benjamin-coiffard','name'=>'Benjamin COIFFARD'),
		);
		
	// Check if user is registred
		if ( (isset($this->analyticsValues['wui'])) && (!empty($this->analyticsValues['wui'])) )
		{
			if (!(isset($userlist[$this->analyticsValues['wui']])))
				$this->addAnalyticError('wui', 'unknown', 'User '.$this->analyticsValues['wui'].' is not granted to access this service.');
		}
		
    }
	

/**
 * Raise exception 5123 if error properti is not empty
 * 
 * @access public
 * @name checkError
 * @param none
 * @return void
 */
/*----------------------------------------------------------------------------*/
    public function checkErrors()
    {
		if (!empty($this->analyticsError))
			throw new Exception('', 5123);
    }
	

/**
 * Buld error message
 * 
 * @access public
 * @name displayErrors
 * @param none
 * @return string
 */
/*----------------------------------------------------------------------------*/
    public function displayErrors()
    {
		
	// Initialize errors
		$technicalMessage = '';
		$fullMessages = [];
		//echo '<pre>'; print_r($this->analyticsError); echo '</pre>';
		
	// Browse each one
		foreach($this->analyticsError as $element)
		{
			
		// Build full message and technical one depending on error
			switch ($element['error'])
			{
				case 'missing':
					$fullMessages[] = 'Parameter ['.$element['parameter'].'] is mandatory.';
					$technicalMessage .= 'missing-'.$element['parameter'].'|';
					break;
				case 'queue_time_override' :
					$fullMessages[] = 'Parameter [qt] define a too long queue time. Value must be greater and equal to 0, and lesser than '.$element['details'];
					$technicalMessage .= 'queue_time_override-qt'.'|';
					break;
				case 'version_not_supported':
					$fullMessages[] = 'Parameter [v] define an unsupported version. Only version 1 is currently supported';
					$technicalMessage .= 'version_not_supported-v|';
					break;
				case 'unknown':
					$fullMessages[] = $element['details'];
					$technicalMessage .= 'unknown_user-wui|';
					break;
			}
		}
		
	// Build error
		$error = array
		(
			'nb_error' => count($this->analyticsError),
			'full_messages' => $fullMessages,
			'technical_datas' => substr($technicalMessage, 0, -1)
		);
		
	// Return JSon
		return json_encode($error);
    }


/**
 * Use parameters to connect to MongoDB
 * 
 * @access public
 * @name connectToMongoDB
 * @return void
 */
/*----------------------------------------------------------------------------*/
    public function connectToMongoDB()
    {
		
	// Connect 
		try
		{
			$this->WZMongo = new WZMongo($this->WZParam->getParameters('mongo_grants', true));
			if (!$this->WZMongo->connection_status)
				throw new Exception('ERROR | MongoDB can not be reached', 8888);
		}
		catch (AlyException $e){throw $e;}
		catch (Exception $e) {throw $e;}
    }
	

/**
 * Write document into MongoDB.
 * 
 * Timestamp is added to document properties. Unique TH is an hash of the complete
 * document. While this element stay unique, same event can not be save more than
 * 1 time
 * 
 * @access public
 * @name sendAnalyticsToMongoBase
 * @return void
 */
/*----------------------------------------------------------------------------*/
    public function sendAnalyticsToMongoBase()
    {
		
	// Get current timestamp
		$ts = time();
		
	// Add timestamp to document
		$newDoc = $this->analyticsValues;
		$newDoc['timestamp'] = $ts;
		
	// Calculate hash with timestamp
		$temporalHash = md5(serialize($newDoc));
		$newDoc['unique_th'] = $temporalHash;
		
	// Write document 
		try
		{
			$this->WZMongo->insertDoc($newDoc, $temporalHash);
		}
		catch (AlyException $e){throw $e;}
		catch (Exception $e) {throw $e;}
    }
	
}
