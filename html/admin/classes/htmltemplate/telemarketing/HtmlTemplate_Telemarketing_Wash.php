<?php

class HtmlTemplate_Telemarketing_Wash extends FlexHtmlTemplate
{
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		//$this->LoadJavascript("telemarketing_wash");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->Admin();
		BreadCrumb()->SetCurrentPage("Wash Proposed Dialler List");
	}

	public function Render()
	{
		// Render Dealer List
		$strDealerListHTML	= '';
		foreach ($this->mxdDataToRender['CallCentres'] as $objDealer)
		{
			$strDealerListHTML	.= "<option value='{$objDealer->id}'>{$objDealer->firstName} {$objDealer->lastName}</option>\n";
		}
		$strDealerListHTML	= trim($strDealerListHTML);
		
		// Render Vendor List
		$strVendorListHTML	= '';
		foreach ($this->mxdDataToRender['Vendors'] as $objCustomerGroup)
		{
			$strVendorListHTML	.= "<option value='{$objCustomerGroup->id}'>{$objCustomerGroup->externalName}</option>\n";
		}
		$strVendorListHTML	= trim($strVendorListHTML);
		
		
		// Render the containing DIV
		echo	"
	<div>
		<table>
			<tbody>
				<tr style='vertical-align:top;'>
					<td>
						<div class='PartTitle'>File Details:</div>
						<table class='reflex' style='width:100%'>
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
											{$strDealerListHTML}
										</select>
									</td>
								</tr>
								<tr>
									<td>File to wash:</td>
									<td>
										<input type='file' />
									</td>
								</tr>
							</tbody>
						</table>
					</td>
					<td></td>
					<td>
						<div class='PartTitle'>Washing Progress</div>
						<table class='reflex' style='width:100%'>
							<tbody>
								<tr>
									<td>TODO</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
";
		
	}
}

?>