<?php
	
	//----------------------------------------------------------------------------//
	// tip.php
	//----------------------------------------------------------------------------//
	/**
	 * tip.php
	 *
	 * File containing Tip Class
	 *
	 * File containing Tip Class
	 *
	 * @file		tip.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Tip
	//----------------------------------------------------------------------------//
	/**
	 * Tip
	 *
	 * An Tip in the Database
	 *
	 * An Tip in the Database
	 *
	 *
	 * @prefix		tip
	 *
	 * @package		intranet_app
	 * @class		Tip
	 * @extends		dataObject
	 */
	
	class Tip extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Tip
		 *
		 * Constructor for a new Tip
		 *
		 * @param	Integer		$intId		The Id of the Tip being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Tip information and Store it ...
			$selTip = new StatementSelect ('Tip', '*', 'Id = <Id>', null, 1);
			$selTip->useObLib (TRUE);
			$selTip->Execute (Array ('Id' => $intId));
			
			if ($selTip->Count () <> 1)
			{
				throw new Exception ('Tip does not exist.');
			}
			
			$selTip->Fetch ($this);
			
			$this->Push (new dataString ('TipText', nl2br (htmlentities ($this->Pop ('TipText')->getValue ()))));
			
			// Construct the object
			parent::__construct ('Tip', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
