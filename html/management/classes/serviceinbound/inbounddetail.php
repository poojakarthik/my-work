<?php
	
	//----------------------------------------------------------------------------//
	// inboundddetail.php
	//----------------------------------------------------------------------------//
	/**
	 * inbounddetail.php
	 *
	 * File containing Inbound Detail Class
	 *
	 * @file		inbounddetail.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Nathan 'nate' Abussi
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// InboundDetail
	//----------------------------------------------------------------------------//
	/**
	 * InboundDetail
	 *
	 * Details for an inbound calling number in the Database
	 *
	 *
	 * @prefix		mod
	 *
	 * @package		intranet_app
	 * @class		InboundDetail
	 * @extends		dataObject
	 */
	
	class InboundDetail extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Inbound number set of details
		 *
		 * @param	Integer		$intId		The Id of the Record being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Service information and Store it ...
			$selServiceAddr = new StatementSelect ('ServiceInboundDetail', '*', 'Id = <Id>', null, '1');
			$selServiceAddr->useObLib (TRUE);
			$selServiceAddr->Execute (Array ('Id' => $intId));
			
			if ($selServiceAddr->Count () <> 1)
			{
				throw new Exception ('Inbound Number Details Not Found');
			}
			
			$selServiceAddr->Fetch ($this);
			
			// Construct the object
			parent::__construct ('InboundDetail', $this->Pull ('Service')->getValue ());
		}
	}
	
?>
