<?php
	
	//----------------------------------------------------------------------------//
	// costcentre.php
	//----------------------------------------------------------------------------//
	/**
	 * costcentre.php
	 *
	 * File for CostCentre Class
	 *
	 * File for CostCentre Class
	 *
	 * @file		costcentre.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// CostCentre
	//----------------------------------------------------------------------------//
	/**
	 * CostCentre
	 *
	 * Contains information reguarding Cost Centres
	 *
	 * Contains information reguarding Cost Centres
	 *
	 *
	 * @prefix	rty
	 *
	 * @package		intranet_app
	 * @class		CostCentre
	 * @extends		dataObject
	 */
	
	class CostCentre extends dataObject
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
		
		function __construct ($intId)
		{
			parent::__construct ('CostCentre', $intId);
			
			// Pull the CostCentre information and attach it to the Object
			$selCostCentre = new StatementSelect ('CostCentre', '*', 'Id = <Id>');
			$selCostCentre->useObLib (TRUE);
			$selCostCentre->Execute (Array ('Id' => $intId));
			$selCostCentre->Fetch ($this);
		}
		
		//------------------------------------------------------------------------//
		// Update
		//------------------------------------------------------------------------//
		/**
		 * Update()
		 *
		 * Update Cost Centre Information
		 *
		 * Update Cost Centre Information
		 *
		 * @param	Array		$arrDetails		An associative array representing new Cost Centre Information
		 * @return	void	
		 *
		 * @method
		 */
		 
		public function Update ($arrDetails)
		{
			$arrData = Array (
				"Name"			=>	$arrDetails ['Name'],
			);
			
			$updCostCentre = new StatementUpdate ('CostCentre', 'Id = <Id>', $arrData, 1);
			$updCostCentre->Execute ($arrData, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
	}
	
?>
