<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// movement.php
//----------------------------------------------------------------------------//
/**
 * movement
 *
 * HTML Template for the ServiceMovement HTML object
 *
 * HTML Template for the ServiceMovement HTML object
 * This popup is used to move Services between Accounts (change of lessee)
 *
 * @file		movement.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateServiceMovement
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceMovement
 *
 * HTML Template class for the ServiceMovement HTML object
 *
 * HTML Template class for the ServiceMovement HTML object
 * This popup is used to move Services between Accounts (change of lessee)
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceMovement
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceMovement extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext			= $intContext;
		$this->_strContainerDivId	= $strId;
		
		$this->LoadJavascript("service_movement");
		$this->LoadJavascript("validation");
		$this->LoadJavascript("input_masks");
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		$objService			= DBO()->ServiceMove->ServiceObject->Value;
		$arrProbableAction	= DBO()->ServiceMove->ProbableActionDetails->Value;
		$arrAccount			= DBO()->Account->_arrProperties;
		$arrPreviousOwner	= DBO()->ServiceMove->PreviousOwner->Value;
		
		$strCustomerGroup	= Customer_Group::getForId($arrAccount['CustomerGroup'])->externalName;
		$intAccountId		= $arrAccount['Id'];
		if ($arrAccount['BusinessName'])
		{
			$strAccountName = $arrAccount['BusinessName'];
		}
		elseif ($arrAccount['TradingName'])
		{
			$strAccountName = $arrAccount['TradingName'];
		}
		else
		{
			$strAccountName = "[Not Specified]";
		}
		$strTimeOfAcquisition			= $objService->GetTimeOfAcquisition();
		$strNow							= GetCurrentISODateTime();
		$strTimeOfAcquisitionFormatted	= OutputMask()->ShortDateTime($strTimeOfAcquisition, TRUE);
		
		$arrPossibleActions = array();
		if ($arrPreviousOwner !== NULL)
		{
			// The Move can be reversed
			switch ($arrPreviousOwner['Action'])
			{
				case SERVICE_CREATION_LESSEE_CHANGED:
					$arrPossibleActions['ReverseLesseeChange'] = "Reverse Change of Lessee";
					break;
				case SERVICE_CREATION_ACCOUNT_CHANGED:
					$arrPossibleActions['ReverseAccountChange'] = "Reverse Change of Account";
					break;
			}
			
			$strPreviousOwnerAccount			= "{$arrPreviousOwner['AccountName']} ({$arrPreviousOwner['Id']})";
			$strPreviousOwnerStatus				= $arrPreviousOwner['StatusDesc'];
			$strPreviousOwnerCustomerGroupName	= $arrPreviousOwner['CustomerGroupName'];
		}
		else
		{
			$strPreviousOwnerAccount			= "";
			$strPreviousOwnerStatus				= "";
			$strPreviousOwnerCustomerGroupName	= "";
		}
		
		// You can only perform a change of lessee/account move if the Service has come into effect on this account
		if ($strTimeOfAcquisition < $strNow)
		{
			$arrPossibleActions['LesseeChange']		= "Change of Lessee";
			$arrPossibleActions['AccountChange']	= "Change of Account";
		}
		
		// Build the options for the MovementType ComboBox
		$strActionOptions = "<option value='NoAction' selected='selected'></option>";
		foreach ($arrPossibleActions as $strAction=>$strDescription)
		{
			$strActionOptions .= "<option value='$strAction'>$strDescription</option>";
		}
		
		// Check if the cached action can be performed on this service
		if ($arrProbableAction !== NULL && !array_key_exists($arrProbableAction['Action'], $arrPossibleActions))
		{
			$arrProbableAction = NULL;
		}
		
		$objPreviousOwner	= Json()->Encode($arrPreviousOwner);
		$objProbableAction	= Json()->Encode($arrProbableAction);
		$objCurrentAccount	= Json()->Encode(
												array(
														"Id"				=> $intAccountId, 
														"Name"				=> $strAccountName,
														"CustomerGroup"		=> $arrAccount['CustomerGroup'],
														"CustomerGroupName"	=> $strCustomerGroup
													)
											);
		$intServiceId		= $objService->GetId();
		$objFNN				= Json()->Encode($objService->GetFNN());
		
		echo "
<form id='ServiceMovementForm'>
	<div class='GroupedContent'>
		<strong>Current Owner</strong>
		<div style='margin-bottom:8px;position:relative;'>
			<span>Account :</span>
			<span style='position:absolute;left:30%;'>$strAccountName ($intAccountId)</span>
		</div>
		<div style='margin-bottom:8px;position:relative;'>
			<span>Customer Group :</span>
			<span style='position:absolute;left:30%;'>$strCustomerGroup</span>
		</div>
		<div style='margin-bottom:8px;position:relative;'>
			<span>Time of Acquisition :</span>
			<span style='position:absolute;left:30%;'>$strTimeOfAcquisitionFormatted</span>
		</div>
	</div>
	<div class='TinySeparator'></div>
	<div class='GroupedContent'>
		<!-- <strong>Action</strong> -->
		<div style='margin-bottom:8px;position:relative;'>
			<span style='top:2px'>Action :</span>
			<select name='ActionType' style='position:absolute;left:30%;width:40%;border: solid 1px #D1D1D1'>$strActionOptions</select>
		</div>
	
		<div id='ServiceMovement.AccountIdInputContainer' style='margin-bottom:5px;position:relative;display:none'>
			<span style='top:2px'>Account :</span>
			<input type='text' name='AccountId' maxlength='15' style='position:absolute;left:30%;width:20%;border: solid 1px #D1D1D1'></input>
			<input type='button' value='Find' onclick='Vixen.ServiceMovement.FindAccount()' style='position:absolute;left:52%;'></input>
		</div>
	</div>
	
	<div id='ServiceMovement.PreviousOwnerContainer' class='GroupedContent' style='margin-top:5px;display:none'>
		<strong>Previous Owner</strong>
		<div style='margin-bottom:5px;position:relative;'>
			<span>Account :</span>
			<span style='position:absolute;left:30%;width:55%'>$strPreviousOwnerAccount</span>
		</div>
		<div style='margin-bottom:5px;position:relative;'>
			<span>Customer Group :</span>
			<span style='position:absolute;left:30%;width:55%'>$strPreviousOwnerCustomerGroupName</span>
		</div>
		<div style='margin-bottom:5px;position:relative'>
			<span>Status :</span>
			<span style='position:absolute;left:30%;width:55%'>$strPreviousOwnerStatus</span>
		</div>
		<input type='button' value='Commit' onclick='Vixen.ServiceMovement.CommitReverse()' style='position:absolute;right:5px;bottom:5px'></input>
	</div>
	
	<div id='ServiceMovement.NewOwnerContainer' class='GroupedContent' style='margin-top:5px;display:none'>
		<strong>New Owner</strong>
		<div style='margin-bottom:8px;position:relative;'>
			<span>Account Number :</span>
			<span id='ServiceMovement.NewAccountId' style='position:absolute;left:30%'>123</span>
		</div>
		<div style='margin-bottom:8px;position:relative;'>
			<span>Account Name :</span>
			<span id='ServiceMovement.NewAccountName' style='position:absolute;left:30%;width:55%'>Dummy Account Name</span>
		</div>
		<div style='margin-bottom:8px;position:relative;'>
			<span>Customer Group :</span>
			<span id='ServiceMovement.NewAccountCustomerGroup' style='position:absolute;left:30%;width:55%'>Dummy Customer Group</span>
		</div>
	
		<div style='margin-bottom:8px;position:relative'>
			<span>Status :</span>
			<span id='ServiceMovement.NewAccountStatus' style='position:absolute;left:30%;width:55%'>Fake Status</span>
		</div>
		<div style='margin-bottom:8px'>
			<span style='top:2px'>Time of Acquisition :</span>
			<select name='EffectiveOnType' style='position:absolute;left:30%;width:110px;border: solid 1px #D1D1D1'>
				<option value='Immediately'>Immediately</option>
				<option value='Date'>Date</option>
			</select>
			<input type='text' name='EffectiveOnDate' InputMask='ShortDate' maxlength='10' style='display:none;position:absolute;left:55%;width:85px;border: solid 1px #D1D1D1'/>
		</div>
		<div style='margin-bottom:8px;position:relative'>
			<span style='top:2px'>Move unbilled CDRs :</span>
			<input type='checkbox' name='MoveCDRs' style='position:absolute;left:30%;'></input>
		</div>
		<div style='margin-bottom:8px;position:relative'>
			<span style='top:2px'>Retain Plan Details :</span>
			<input type='checkbox' name='MovePlan' style='position:absolute;left:30%;'></input>
		</div>
		<input type='button' value='Commit' onclick='Vixen.ServiceMovement.CommitServiceMove()' style='position:absolute;right:5px;bottom:5px'></input>
	</div>
</form>

<div class='ButtonContainer'>
	<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)' style='float:right'></input>
</div>

<script type='text/javascript'>Vixen.ServiceMovement.Initialise($objFNN, $intServiceId, $objCurrentAccount, $objPreviousOwner, $objProbableAction)</script>
";
		
	}
}

?>
