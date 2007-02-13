<?php
	
	//----------------------------------------------------------------------------//
	// ServiceTotal.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceTotal.php
	 *
	 * File containing ServiceTotal Class
	 *
	 * File containing ServiceTotal Class
	 *
	 * @file		ServiceTotal.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceTotal
	//----------------------------------------------------------------------------//
	/**
	 * ServiceTotal
	 *
	 * A ServiceTotal in the Database
	 *
	 * A ServiceTotal in the Database
	 *
	 *
	 * @prefix	srv
	 *
	 * @package		intranet_app
	 * @class		ServiceTotal
	 * @extends		dataObject
	 */
	
	class ServiceTotal extends dataObject
	{
		
		private $_srvService;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new ServiceTotal
		 *
		 * Constructor for a new ServiceTotal
		 *
		 * @param	Integer		$intId		The Id of the ServiceTotal being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the ServiceTotal information and Store it ...
			$selServiceTotal = new StatementSelect ('ServiceTotal', '*', 'Id = <Id>', null, '1');
			$selServiceTotal->useObLib (TRUE);
			$selServiceTotal->Execute (Array ('Id' => $intId));
			
			if ($selServiceTotal->Count () <> 1)
			{
				throw new Exception ('ServiceTotal Not Found');
			}
			
			$selServiceTotal->Fetch ($this);
			
			// Construct the object
			parent::__construct ('ServiceTotal', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Service
		//------------------------------------------------------------------------//
		/**
		 * Service()
		 *
		 * Returns the Associated Service
		 *
		 * Returns the Associated Service
		 *
		 * @return	Service
		 *
		 * @method
		 */
		
		public function Service ()
		{
			if (!$this->_srvService)
			{
				$this->_srvService = new Service ($this->Pull ('Service')->getValue ());
			}
			
			return $this->_srvService;
		}
	}
	
?>
