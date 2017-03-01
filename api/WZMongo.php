<?php


/**
 * Manage connection and requests to MongoDB
 * 
 * @access public
 * @name WZMongo
 * @author Benjamin COIFFARD
 * @version 1.0.0
 */
/*------------------------------------------------------------------------------
	@method __construct
	@method __call
	@method __get
	@method insertDoc
------------------------------------------------------------------------------*/
class WZMongo
{

// Class properties
	private $collection;
	public $connection_status = false;


/**
 * Constructor of WZMongo class
 * 
 * @access public
 * @name __construct
 * @param object $parameters
 * @return void
 */
/*----------------------------------------------------------------------------*/
	public function __construct($parameters)
	{
		
	// Initialize connection to MongoDB base
		try
		{
			$mongoCnx = new MongoClient();
			$this->collection = $mongoCnx->selectCollection($parameters['mongo_db'], $parameters['mongo_collection']);
			$this->connection_status = true;
		}
		catch (Exception $e) {throw $e;}

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
		switch(strtolower($name))
		{
			case 'connection_status': return $this->connection_status;
			default:
				throw new Exception('ERROR | Property '.__CLASS__.'.'.strtolower($name).' does not exists in parameters file.', 8888);
		}
	}


/**
 * Write doc to database, checking it's temporal hash is unique. Id document
 * does not exists, it will be created. Otherwise, it will be updated
 * 
 * @access public
 * @name insertDoc
 * @param array $newDoc
 * @param string $temporalHash
 * @return void
 */
/*----------------------------------------------------------------------------*/
	public function insertDoc($newDoc, $temporalHash)
	{
		try
		{
			$this->collection->update
			(
				array('unique_th'=>$temporalHash),
				array('$set'=>$newDoc),
				array('upsert'=>true)
				
			);
		}
		catch (Exception $e) {throw $e;}

	}
	
}
