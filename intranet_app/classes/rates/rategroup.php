<?php
	
	//----------------------------------------------------------------------------//
	// rategroup.php
	//----------------------------------------------------------------------------//
	/**
	 * rategroup.php
	 *
	 * File containing Rate Group Class
	 *
	 * File containing Rate Group Class
	 *
	 * @file		rategroup.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RateGroup
	//----------------------------------------------------------------------------//
	/**
	 * RateGroup
	 *
	 * Class that Holds Rate Group Information
	 *
	 * Class that Holds Rate Group Information
	 *
	 *
	 * @prefix		rgp
	 *
	 * @package		intranet_app
	 * @class		RateGroup
	 * @extends		dataObject
	 */
	
	class RateGroup extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a new RateGroup with its Information Contained
		 *
		 * Constructs a new RateGroup with its Information Contained
		 *
		 * @param	Integer		$intId			The Id of the RateGroup
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('RateGroup', $intId);
			
			$selRateGroup = new StatementSelect ('RateGroup', '*', 'Id = <Id>');
			$selRateGroup->useObLib (TRUE);
			$selRateGroup->Execute (Array ('Id' => $intId));
			$selRateGroup->Fetch ($this);
			
			if ($selRateGroup->Count () <> 1)
			{
				throw new Exception ('Rate Group Not Found: ' . $intId);
			}
			
			$this->Push (new ServiceTypes ($this->Pull ('ServiceType')->getValue ()));
			
			$intRecordType = $this->Pop ("RecordType")->getValue ();
			$this->Push (new RecordType ($intRecordType));
		}
	}
	
?>
