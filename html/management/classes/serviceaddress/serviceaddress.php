<?php
	
	//----------------------------------------------------------------------------//
	// serviceaddress.php
	//----------------------------------------------------------------------------//
	/**
	 * serviceaddress.php
	 *
	 * File containing Service Address Class
	 *
	 * File containing Service Address Class
	 *
	 * @file		serviceaddress.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceAddress
	//----------------------------------------------------------------------------//
	/**
	 * ServiceAddress
	 *
	 * A Service Address in the Database
	 *
	 * A Service Address in the Database
	 *
	 *
	 * @prefix	sad
	 *
	 * @package		intranet_app
	 * @class		ServiceAddress
	 * @extends		dataObject
	 */
	
	class ServiceAddress extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Service Address
		 *
		 * Constructor for a new Service Address
		 *
		 * @param	Integer		$intId		The Id of the Service Address being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Service information and Store it ...
			$selServiceAddr = new StatementSelect ('ServiceAddress', '*', 'Id = <Id>', null, '1');
			$selServiceAddr->useObLib (TRUE);
			$selServiceAddr->Execute (Array ('Id' => $intId));
			
			if ($selServiceAddr->Count () <> 1)
			{
				throw new Exception ('Service Address Not Found');
			}
			
			$selServiceAddr->Fetch ($this);
			
			// Construct the object
			parent::__construct ('ServiceAddress', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
