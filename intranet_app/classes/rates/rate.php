<?php
	
	//----------------------------------------------------------------------------//
	// rate.php
	//----------------------------------------------------------------------------//
	/**
	 * rate.php
	 *
	 * File containing Rate Class
	 *
	 * File containing Rate Class
	 *
	 * @file		rate.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Rate
	//----------------------------------------------------------------------------//
	/**
	 * Rate
	 *
	 * Holds Rate Information
	 *
	 * Holds Rate Information
	 *
	 *
	 * @prefix		rte
	 *
	 * @package		intranet_app
	 * @class		Rate
	 * @extends		dataObject
	 */
	
	class Rate extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a new Rate with its information
		 *
		 * Constructs a new Rate with its information
		 *
		 * @param	Integer		$intId		The Id of the Rate being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('Rate', $intId);
			
			$selRate = new StatementSelect ('Rate', '*', 'Id = <Id>');
			$selRate->useObLib (TRUE);
			$selRate->Execute (Array ('Id' => $intId));
			$selRate->Fetch ($this);
			
			$this->Push (new ServiceTypes ($this->Pull ('ServiceType')->getValue ()));
			
			$intRecordType = $this->Pop ("RecordType")->getValue ();
			$this->Push (new RecordType ($intRecordType));
		}
	}
	
?>
