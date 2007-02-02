<?php
	
	//----------------------------------------------------------------------------//
	// rateplan.php
	//----------------------------------------------------------------------------//
	/**
	 * rateplan.php
	 *
	 * File containing Rate Plan Information
	 *
	 * File containing Rate Plan Information
	 *
	 * @file		rateplan.php
	 * @language	PHP
	 * @package		client_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// RatePlan
	//----------------------------------------------------------------------------//
	/**
	 * RatePlan
	 *
	 * Class for Rate Plan Information
	 *
	 * Class for Rate Plan Information
	 *
	 *
	 * @prefix		rrp
	 *
	 * @package		intranet_app
	 * @class		RatePlan
	 * @extends		dataObject
	 */
	
	class RatePlan extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Pulls Rate Plan Inforamtion
		 *
		 * Pulls Rate Plan Inforamtion
		 *
		 * @param	Integer		$intId		The Id of the Rate Plan Requested
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('RatePlan', $intId);
			
			$selRatePlan = new StatementSelect ('RatePlan', '*', 'Id = <Id>');
			$selRatePlan->useObLib (TRUE);
			$selRatePlan->Execute (Array ('Id' => $intId));
			
			if ($selRatePlan->Count () <> 1)
			{
				throw new Exception ('Rate Plan Not Found: ' . $intId);
			}
			
			$selRatePlan->Fetch ($this);
		}
	}
	
?>
