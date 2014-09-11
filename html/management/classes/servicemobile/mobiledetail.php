<?php
	
	//----------------------------------------------------------------------------//
	// mobiledetail.php
	//----------------------------------------------------------------------------//
	/**
	 * mobiledetail.php
	 *
	 * File containing Mobile Detail Class
	 *
	 * File containing Mobile Detail Class
	 *
	 * @file		mobiledetail.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// MobileDetail
	//----------------------------------------------------------------------------//
	/**
	 * MobileDetail
	 *
	 * A Mobile Detail in the Database
	 *
	 * A Mobile Detail in the Database
	 *
	 *
	 * @prefix		mod
	 *
	 * @package		intranet_app
	 * @class		MobileDetail
	 * @extends		dataObject
	 */
	
	class MobileDetail extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Mobile Detail
		 *
		 * Constructor for a new Mobile Detail
		 *
		 * @param	Integer		$intId		The Id of the Mobile Detail being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Service information and Store it ...
			$selServiceAddr = new StatementSelect ('ServiceMobileDetail', '*', 'Id = <Id>', null, '1');
			$selServiceAddr->useObLib (TRUE);
			$selServiceAddr->Execute (Array ('Id' => $intId));
			
			if ($selServiceAddr->Count () <> 1)
			{
				throw new Exception ('Mobile Detail Not Found');
			}
			
			$selServiceAddr->Fetch ($this);
			
			// Construct the object
			parent::__construct ('MobileDetail', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
