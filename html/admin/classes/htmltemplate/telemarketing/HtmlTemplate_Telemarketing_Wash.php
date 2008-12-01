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
		// Render the containing DIV
		echo	"
	<div class='Page'>
		<table class='form-data'>
			<tbody>
				<tr style='vertical-align:top;'>
					<td>
						<div class='PartTitle'>File Details:</div>
						<table class='PartPage'>
							<tbody>
								<tr>
									<td>Dealer:</td>
									<td>
										<select>
											<option>Insel</option>
											<option>Yellow Call Centre</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>Vendor</td>
									<td>
										<select>
											<option>Protalk Australia</option>
											<option>Telco Blue</option>
											<option>Voicetalk</option>
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
						<table class='PartPage'>
							<tbody>
								<tr>
									TODO
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