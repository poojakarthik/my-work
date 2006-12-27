<?php
	
	//----------------------------------------------------------------------------//
	// Charge.php
	//----------------------------------------------------------------------------//
	/**
	 * Charge.php
	 *
	 * File containing Charge Class
	 *
	 * File containing Charge Class
	 *
	 * @file		Charge.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Charge
	//----------------------------------------------------------------------------//
	/**
	 * Charge
	 *
	 * A Charge in the Database
	 *
	 * A Charge in the Database
	 *
	 *
	 * @prefix	crg
	 *
	 * @package		intranet_app
	 * @class		Charge
	 * @extends		dataObject
	 */
	
	class Charge extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Charge
		 *
		 * Constructor for a new Charge
		 *
		 * @param	Integer		$intId		The Id of the Charge being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Charge information and Store it ...
			$selCharge = new StatementSelect ('Charge', '*', 'Id = <Id>', null, 1);
			$selCharge->useObLib (TRUE);
			$selCharge->Execute (Array ('Id' => $intId));
			
			if ($selCharge->Count () <> 1)
			{
				throw new Exception ('Charge not found');
			}
			
			$selCharge->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Charge', $this->Pull ('Id')->getValue ());
			
			$this->Push (new Service ($this->Pop ('Service')->getValue ()));
		}
	}
	
?>
