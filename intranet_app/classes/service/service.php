<?php
	
	//----------------------------------------------------------------------------//
	// service.php
	//----------------------------------------------------------------------------//
	/**
	 * service.php
	 *
	 * File containing Service Class
	 *
	 * File containing Service Class
	 *
	 * @file		service.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Service
	//----------------------------------------------------------------------------//
	/**
	 * Service
	 *
	 * A service in the Database
	 *
	 * A service in the Database
	 *
	 *
	 * @prefix	srv
	 *
	 * @package		intranet_app
	 * @class		Service
	 * @extends		dataObject
	 */
	
	class Service extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Service
		 *
		 * Constructor for a new Service
		 *
		 * @param	Integer		$intId		The Id of the Service being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Service information and Store it ...
			$selService = new StatementSelect ('Service', '*', 'Id = <Id>');
			$selService->useObLib (TRUE);
			$selService->Execute (Array ('Id' => $intId));
			$selService->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Service', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
