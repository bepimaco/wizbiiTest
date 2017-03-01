<?php

	try
	{

	// Import API classes
		require_once 'api/WZMongo.php';
		require_once 'api/WZParam.php';
		require_once 'api/WZStat.php';
		require_once 'api/WZTrace.php';
	
	// Build API instance
		$WZStat = new WZStat();
		
	// Get parameters
		$WZStat->extractParameters();
		
	// Check rules
		$WZStat->applyRules();
		
	// Check user
		$WZStat->checkUser();
		
	// Check errors
		$WZStat->checkErrors();
		
	// Connect to MongoDB
		$WZStat->connectToMongoDB();
		
	// Add document to database
		$WZStat->sendAnalyticsToMongoBase();
		
		echo 'success';
	
	}
	catch (Exception $e)
	{
		
	// Error intercepted
		if ($e->getCode() == 5123)
		{
			echo $WZStat->displayErrors();
			exit();
		}
		
	// Unknown errors
		else
		{
			echo 'ERROR : '.$e->getMessage(); exit();
		}
	
	}
	