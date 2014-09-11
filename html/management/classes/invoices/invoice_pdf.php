<?php
	
	//----------------------------------------------------------------------------//
	// invoice_pdf.php
	//----------------------------------------------------------------------------//
	/**
	 * invoice_pdf.php
	 *
	 * File containing Invoice_PDF Class
	 *
	 * File containing Invoice_PDF Class
	 *
	 * @file		invoice_pdf.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Invoice_PDF
	//----------------------------------------------------------------------------//
	/**
	 * Invoice_PDF
	 *
	 * An Invoice_PDF in the Database
	 *
	 * An Invoice_PDF in the Database
	 *
	 *
	 * @prefix		pdf
	 *
	 * @package		intranet_app
	 * @class		Invoice_PDF
	 * @extends		dataObject
	 */
	
	class Invoice_PDF extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Invoice_PDF
		 *
		 * Constructor for a new Invoice_PDF
		 *
		 * @param	Integer		$intId		The Id of the Invoice_PDF being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intAccount, $intYear, $intMonth, $strName)
		{
			$this->_oblintAccount	= $this->Push (new dataInteger	('Account',	$intAccount));
			$this->_oblintYear		= $this->Push (new dataInteger	('Year',	$intYear));
			$this->_oblintMonth		= $this->Push (new dataInteger	('Month',	$intMonth));
			$this->_oblstrName		= $this->Push (new dataString	('Name',	$strName));
			
			// Construct the object
			parent::__construct ('Invoice-PDF');
		}
		
		//------------------------------------------------------------------------//
		// Display
		//------------------------------------------------------------------------//
		/**
		 * Display()
		 *
		 * Display the PDF in the Browser
		 *
		 * Display the PDF in the Browser
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function Display ()
		{
			getPDF (
				$this->Pull ('Account')->getValue (),
				$this->Pull ('Year')->getValue (),
				$this->Pull ('Month')->getValue ()
			);
		}
	}
	
?>
