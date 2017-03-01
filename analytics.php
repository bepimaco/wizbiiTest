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
		echo 'Initialisation de la classe WZStat: OK<br>';
		
	// Get parameters
		$WZStat->extractParameters();
		echo 'Paramètres récupérés: OK<br>';
		
	// Check rules
		$WZStat->applyRules();
		echo 'Règles appliqués: OK<br>';
		
	// Check user
		$WZStat->checkUser();
		echo 'Vérification de l\'utilisateur: OK<br>';
		
	// Check errors
		$WZStat->checkErrors();
		echo 'Nombre d\'erreurs : 0<br>';
		
	// Connect to MongoDB
		$WZStat->connectToMongoDB();
		echo 'Connexion à la base Mongo: OK<br>';
		
	// Add document to database
		$WZStat->sendAnalyticsToMongoBase();
		echo 'Ajout du document dans la base Mongo: OK<br>';
		
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
	