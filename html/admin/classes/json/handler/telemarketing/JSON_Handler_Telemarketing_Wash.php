<?php

class JSON_Handler_Telemarketing_Wash extends JSON_Handler
{

	// Waives the Contract Fees for a given ServiceRatePlan
	public function buildProposedUploadPopup()
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			$strDealerListHTML	= self::_renderCallCentreCombo();
			$strVendorListHTML	= self::_renderVendorCombo();
			
			// Build the innerHTML
			$strPopupContent	= "

	<div class='GroupedContent'>
		<table class='form-data' style='width:100%'>
			<tbody>
				<tr>
					<td>Dealer:</td>
					<td>
						<select id='Telemarketing_Wash_Dealer' name='Telemarketing_Wash_Dealer'>
							<option value='' selected='selected'>[None]</option>
							{$strDealerListHTML}
						</select>
					</td>
				</tr>
				<tr>
					<td>Vendor:</td>
					<td>
						<select id='Telemarketing_Wash_Vendor' name='Telemarketing_Wash_Vendor'>
							<option value='' selected='selected'>[None]</option>
							{$strVendorListHTML}
						</select>
					</td>
				</tr>
				<tr>
					<td>File to wash:</td>
					<td>
						<input type='file' id='Telemarketing_Wash_File' name='Telemarketing_Wash_File' />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div style='width:100%; margin: 0 auto; text-align:center;'>
		<input type='button' id='TelemarketingWashProposed_Submit' name='TelemarketingWashProposed_Submit' value='submitted' onclick='Flex.Telemarketing.ProposedList.submit(\"" + strAction + "\")' style='margin-left:3px'></input> 
		<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)' style='margin-left:3px'></input>
	</div>
";
			
			// If no exceptions were thrown, then everything worked
			
			return array(
							"Success"		=> TRUE,
							"PopupContent"	=> $strPopupContent,
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}

	private static function _renderVendorCombo()
	{
		$arrVendors	= Customer_Group::getAll();
		
		// Render Vendor List
		$strVendorListHTML	= '';
		foreach ($arrVendors as $objCustomerGroup)
		{
			$strVendorListHTML	.= "<option value='{$objCustomerGroup->id}'>{$objCustomerGroup->externalName}</option>\n";
		}
		return trim($strVendorListHTML);
	}

	private static function _renderCallCentreCombo()
	{
		$arrCallCentres	= Dealer::getCallCentres();
		
		// Render Dealer List
		$strDealerListHTML	= '';
		foreach ($arrCallCentres as $objDealer)
		{
			$strDealerListHTML	.= "<option value='{$objDealer->id}'>{$objDealer->firstName} {$objDealer->lastName}</option>\n";
		}
		return trim($strDealerListHTML);
	}
}

?>
