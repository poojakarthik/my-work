<?php
	
	//----------------------------------------------------------------------------//
	// DOMcostcentres.php
	//----------------------------------------------------------------------------//
	/**
	 * DOMcostcentres.php
	 *
	 * File for DomCostCentres Class
	 *
	 * File for DomCostCentres Class
	 *
	 * @file		costcentre.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Nathan 'nate' Abussi
	 * @version		6.11
	 * @copyright	2007 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// DomCostCentres
	//----------------------------------------------------------------------------//
	/**
	 * CostCentre
	 *
	 * Contains information reguarding Cost Centres, puts them into the DOM doc-
	 * ument via InsertDOM()
	 *
	 * Contains information reguarding Cost Centres, puts them into the DOM doc-
	 * ument via InsertDOM()
	 *
	 *
	 * @prefix	dcc
	 *
	 * @package		intranet_app
	 * @class		CostCentre
	 * @extends		dataObject
	 */
	
	class DomCostCentres extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs CostCentre information from the Database
		 *
		 * Constructs CostCentre information from the Database
		 *
		 * @param	Integer		$intId		The Id of the CostCentre being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($actAccount)
		{
			parent::__construct ('CostCentre', $actAccount);
			
			// Pull the CostCentre information and attach it to the Object
			$selCostCentre = new StatementSelect ('CostCentre', '*', 'Account = <Id>');
			$arrWhere = Array('Id' => $actAccount->Pull ('Id')->getValue());
			$selCostCentre->Execute ($arrWhere);
			$arrResults = $selCostCentre->FetchAll ($this);
			//debug($selCostCentre);die;
			$GLOBALS['Style']->InsertDOM($arrResults, 'CostCentres');
		}
	}
	
?>
