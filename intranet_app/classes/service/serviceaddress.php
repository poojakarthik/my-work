<?php
	
	//----------------------------------------------------------------------------//
	// serviceaddress.php
	//----------------------------------------------------------------------------//
	/**
	 * serviceaddress.php
	 *
	 * File containing Service Address Class
	 *
	 * File containing Service Address Class
	 *
	 * @file		serviceaddress.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceAddress
	//----------------------------------------------------------------------------//
	/**
	 * ServiceAddress
	 *
	 * A Service Address in the Database
	 *
	 * A Service Address in the Database
	 *
	 *
	 * @prefix	sad
	 *
	 * @package		intranet_app
	 * @class		ServiceAddress
	 * @extends		dataObject
	 */
	
	class ServiceAddress extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Service Address
		 *
		 * Constructor for a new Service Address
		 *
		 * @param	Integer		$intId		The Id of the Service Address being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Service information and Store it ...
			$selServiceAddr = new StatementSelect ('ServiceAddress', '*', 'Id = <Id>', null, '1');
			$selServiceAddr->useObLib (TRUE);
			$selServiceAddr->Execute (Array ('Id' => $intId));
			
			if ($selServiceAddr->Count () <> 1)
			{
				throw new Exception ('Service Address Not Found');
			}
			
			$selServiceAddr->Fetch ($this);
			
			// Construct the object
			parent::__construct ('ServiceAddress', $this->Pull ('Id')->getValue ());
			
			$this->Push (new ServiceAddressTypes ($this->Pull ("ServiceAddressType")->getValue ()));
			$this->Push (new ServiceStreetTypes ($this->Pull ("ServiceStreetType")->getValue ()));
			$this->Push (new ServiceStreetSuffixTypes ($this->Pull ("ServiceStreetTypeSuffix")->getValue ()));
			$this->Push (new ServiceEndUserTitleTypes ($this->Pull ("EndUserTitle")->getValue ()));
			$this->Push (new ServiceStateTypes ($this->Pull ("ServiceState")->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Update
		//------------------------------------------------------------------------//
		/**
		 * Update()
		 *
		 * Update Service Address Information
		 *
		 * Save the new Service Address information to the Database
		 *
		 * @param	Array		$arrDetails		An associative array of Service Address Information
		 *
		 * @method
		 */
		
		public function Update ($arrDetails)
		{
			$eutEndUserTitle = new ServiceEndUserTitleTypes ();
			$bolEndUserTitle = $eutEndUserTitle->setValue ($arrDetails ['EndUserTitle']);

			$satServiceAddressType = new ServiceAddressTypes ();
			$bolServiceAddressType = $satServiceAddressType->setValue ($arrDetails ['ServiceAddressType']);
			
			$sstServiceStreetType = new ServiceStreetTypes ();
			$bolServiceStreetType = $sstServiceStreetType->setValue ($arrDetails ['ServiceStreetType']);
			
			$sstServiceStreetSuffixType = new ServiceStreetSuffixTypes ();
			$bolServiceStreetSuffixType = $sstServiceStreetSuffixType->setValue ($arrDetails ['ServiceStreetTypeSuffix']);
			
			$staServiceStateType = new ServiceStateTypes ();
			$bolServiceStateType = $staServiceStateType->setValue ($arrDetails ['ServiceState']);
			
			$arrData = Array (
				"BillName"						=> $arrDetails ['BillName'],
				"BillAddress1"					=> $arrDetails ['BillAddress1'],
				"BillAddress2"					=> $arrDetails ['BillAddress2'],
				"BillLocality"					=> $arrDetails ['BillLocality'],
				"BillPostcode"					=> sprintf ("%04d", $arrDetails ['BillPostcode']),
				"EndUserTitle"					=> (($bolEndUserTitle == true) ? $arrDetails ['EndUserTitle'] : ""),
				"EndUserGivenName"				=> $arrDetails ['EndUserGivenName'],
				"EndUserFamilyName"			=> $arrDetails ['EndUserFamilyName'],
				"EndUserCompanyName"			=> $arrDetails ['EndUserCompanyName'],
				"DateOfBirth"					=> sprintf ("%04d", $arrDetails ['DateOfBirth:year']) . 
												   sprintf ("%02d", $arrDetails ['DateOfBirth:month']) . 
												   sprintf ("%02d", $arrDetails ['DateOfBirth:day']),
				"Employer"						=> $arrDetails ['Employer'],
				"Occupation"					=> $arrDetails ['Occupation'],
				"ABN"							=> $arrDetails ['ABN'],
				"TradingName"					=> $arrDetails ['TradingName'],
				"ServiceAddressType"			=> (($bolServiceAddressType == true) ? $arrDetails ['ServiceAddressType'] : ""),
				"ServiceAddressTypeNumber"		=> $arrDetails ['ServiceAddressTypeNumber'],
				"ServiceAddressTypeSuffix"		=> $arrDetails ['ServiceAddressTypeSuffix'],
				"ServiceStreetNumberStart"		=> $arrDetails ['ServiceStreetNumberStart'],
				"ServiceStreetNumberEnd"		=> $arrDetails ['ServiceStreetNumberEnd'],
				"ServiceStreetNumberSuffix"	=> $arrDetails ['ServiceStreetNumberSuffix'],
				"ServiceStreetName"			=> $arrDetails ['ServiceStreetName'],
				"ServiceStreetType"				=> (($bolServiceStreetType == true) ? $arrDetails ['ServiceStreetType'] : ""),
				"ServiceStreetTypeSuffix"		=> (($bolServiceStreetSuffixType == true) ? $arrDetails ['ServiceStreetTypeSuffix'] : ""),
				"ServicePropertyName"			=> $arrDetails ['ServicePropertyName'],
				"ServiceLocality"				=> $arrDetails ['ServiceLocality'],
				"ServiceState"					=> (($bolServiceStateType == true) ? $arrDetails ['ServiceState'] : ""),
				"ServicePostcode"				=> sprintf ("%04d", $arrDetails ['ServicePostcode'])
			);
			
			$updServiceAddress = new StatementUpdate ("ServiceAddress", 'Id = <Id>', $arrData, 1);
			$updServiceAddress->Execute ($arrData, Array ('Id' => $this->Pull ('Id')->getValue ()));
			
			return true;
		}
	}
	
?>
