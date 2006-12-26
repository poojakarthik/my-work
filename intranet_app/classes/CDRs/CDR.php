<?php
	
	//----------------------------------------------------------------------------//
	// CDR.php
	//----------------------------------------------------------------------------//
	/**
	 * CDR.php
	 *
	 * File containing CDR Class
	 *
	 * File containing CDR Class
	 *
	 * @file		CDR.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CDR
	//----------------------------------------------------------------------------//
	/**
	 * CDR
	 *
	 * A CDR in the Database
	 *
	 * A CDR in the Database
	 *
	 *
	 * @prefix	cdr
	 *
	 * @package		intranet_app
	 * @class		CDR
	 * @extends		dataObject
	 */
	
	class CDR extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new CDR
		 *
		 * Constructor for a new CDR
		 *
		 * @param	Integer		$intId		The Id of the CDR being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the CDR information and Store it ...
			$selCDR = new StatementSelect ('CDR', '*', 'Id = <Id>', null, 1);
			$selCDR->useObLib (TRUE);
			$selCDR->Execute (Array ('Id' => $intId));
			
			if ($selCDR->Count () <> 1)
			{
				throw new Exception ('CDR not found');
			}
			
			$selCDR->Fetch ($this);
			
			// Construct the object
			parent::__construct ('CDR', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
