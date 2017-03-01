<?php


/**
 * Manage error logs
 * 
 * @access public
 * @name WZTrace
 * @author Benjamin COIFFARD
 * @version 1.0.0
 */
/*------------------------------------------------------------------------------
	@method __construct
	@method __call
	@method __get
	@method traceError
------------------------------------------------------------------------------*/
class WZTrace
{

// Class properties
	private $logFile;


/**
 * Constructor of WZTrace class
 * 
 * @access public
 * @name __construct
 * @param object $parameters
 * @return void
 */
/*----------------------------------------------------------------------------*/
	public function __construct($parameters)
	{
		
	// Import all trace parameters
		$this->logFile = $parameters['log_folder'].$parameters['log_file'].$parameters['log_extension'];
	
	// Check log folder exist
		if (!(is_dir($parameters['log_folder'])))
		{
			
		// If log folder does not exists, program try to make it (sometimes specific log folder for the project can have been forgotten)
			if (is_dir(dirname($parameters['log_folder'])))
			{
				try {mkdir($parameters['log_folder']);}
				catch (Exception $e)
				{throw new Exception('Log folder can not be found, and can not be created ['.$e->getMessage().']', 0);}
			}
			else throw new Exception('Log folder can not be found', 0);
		}

	}


/**
 * Magic __call
 * 
 * All methods unknown by the class are recovered by this function and interceped by a new
 * exception
 * 
 * @access public
 * @name __call
 * @param string $name name of the requested local function
 * @param mixed $arguments list of arguments
 * @return void
 */
/*----------------------------------------------------------------------------*/
	public function __call($name, $arguments)
	{
		throw new Exception('ERROR | Function '.__CLASS__.'.'.$name.'() does not exists. Arguments received: ['.serialize($arguments).']', 8888);
	}


/**
 * Magic __get
 * 
 * All properties unknown by the class are recovered by this function and interceped by a new
 * exception
 * 
 * @access public
 * @name __get
 * @param string $name name of the requested local property
 * @return void
 */
/*----------------------------------------------------------------------------*/
	public function __get($name)
	{
		throw new Exception('ERROR | Property '.__CLASS__.'.'.strtolower($name).' does not exists in parameters file.', 8888);
	}


/**
 * Writing a trace to save an error and its context
 * 
 * @access public
 * @name traceError
 * @param array $property numeric array of function properties
 * @param string $message description of error
 * @return void
 */
/*----------------------------------------------------------------------------*/
	public function traceError($property, $message)
	{

	// Build trace message
		$trace = '*logerror* | '.date('Y-m-d H:i:s').' | '.$property[0].' | '.$property[1].' | '.$property[2].' | '.$property[3].' | '.$message;

	// Save trace
		try
		{
			file_put_contents($this->logFile, $trace."\n", FILE_APPEND);
		}
		catch(Exception $e) {throw new Exception('Trace can not been written: log folder does not exists', 0);}

	// Return boolean true
		return true;

	}
	
}
