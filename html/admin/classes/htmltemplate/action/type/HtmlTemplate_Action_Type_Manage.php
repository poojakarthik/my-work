<?php

class HtmlTemplate_Action_Type_Manage extends FlexHtmlTemplate
{
	private	$_arrActionAssociationTypeColumnOrder	= array();
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("flex_constant");
		$this->LoadJavascript("action_type_edit");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->SetCurrentPage("Manage Action Types");
	}

	public function Render()
	{
		$this->mxdDataToRender;
		
		echo	"\n" .
		"<table class='reflex clickable hoverable'>\n" .
		"	<caption>\n" .
		"		<div class='caption_bar'>\n" .
		"			<div class='caption_title'>\n" .
		"				Managing Action Types" .
		"			</div>\n" .
		"			<div class='caption_options'>\n" .
		"				<span onclick='Flex.Action_Edit = new Action_Type_Edit();'><img class='icon_16' src='../admin/img/template/page_white_add.png' />Add a new Action Type</span>" .
		"			</div>\n" .
		"		</div>\n" .
		"	</caption>\n" .
		$this->_buildHeader() .
		$this->_buildContent() .
		$this->_buildFooter() .
		"</table>\n";
	}
	
	protected function _buildHeader()
	{
		$strHeaderHTML	= "" .
		"	<thead>\n" .
		"		<tr>\n" .
		"			<th>Code</th>\n" .
		"			<th>Name</th>\n" .
		"			<th>Description</th>\n" .
		"			<th>Details Required</th>\n";
		
		$arrActionAssociationTypes	= Action_AssociationType::getAll();
		foreach ($arrActionAssociationTypes as $intActionAssociationTypeId=>$objActionAssociationType)
		{
			$this->_arrActionAssociationTypeColumnOrder[]	= $intActionAssociationTypeId;
			
			$strIconSubPath	= "admin/img/template/".strtolower($objActionAssociationType->name).".png";
			if (@file_exists(Flex::getBase()."html/".$strIconSubPath))
			{
				$strHeaderHTML	.= "			<th><img class='icon_16' src='../{$strIconSubPath}' alt='{$objActionAssociationType->name}' title='{$objActionAssociationType->name}' /></th>\n";
			}
			else
			{
				$strHeaderHTML	.= "			<th><span>{$objActionAssociationType->name}</span></th>\n";
			}
		}
		
		$strHeaderHTML	.= "" .
		"			<th>Method</th>\n" .
		"			<th>Nature</th>\n" .
		"			<th>Status</th>\n" .
		"			<th>&nbsp;</th>\n" .
		"		</tr>\n" .
		"	</thead>\n";
		
		return $strHeaderHTML;
	}
	
	protected function _buildFooter()
	{
		$intColumnCount	= 8 + count(Action_AssociationType::getAll());
		return	"" .
		"	<tfoot>\n" .
		"		<tr>\n" .
		"			<th colspan='{$intColumnCount}'>&nbsp;</th>\n" .
		"		</tr>\n" .
		"	</tfoot>\n";
	}
	
	protected function _buildContent()
	{
		$strHTML	= "	<tbody>\n";
		foreach ($this->mxdDataToRender['arrActionTypes'] as $intActionTypeId=>$arrActionType)
		{
			$strHTML	.= $this->_buildRecord($arrActionType);
		}
		return $strHTML . "	</tbody>\n";
	}
	
	protected function _buildRecord($objActionType)
	{
		static	$cgActionTypeDetailRequirement;
		static	$cgActiveStatus;
		$cgActionTypeDetailRequirement	= ($cgActionTypeDetailRequirement)	? $cgActionTypeDetailRequirement	: Constant_Group::getConstantGroup('action_type_detail_requirement');
		$cgActiveStatus					= ($cgActiveStatus) 				? $cgActiveStatus					: Constant_Group::getConstantGroup('active_status');
		
		$strActionEdit	= '&nbsp;';
		if ($objActionType->active_status_id === ACTIVE_STATUS_ACTIVE && (($objActionType->is_system && AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) || $objActionType->is_system))
		{
			$strActionEdit	= "<img class='icon_16' src='../admin/img/template/page_white_edit.png' onclick='Flex.Action_Edit = new Action_Type_Edit({$objActionType->id});' />";
		}
		
		$strHTMLContent	=	"" .
		"		<tr>\n" .
		"			<td>{$objActionType->id}</td>\n" .
		"			<td>{$objActionType->name}</td>\n" .
		"			<td>{$objActionType->description}</td>\n" .
		"			<td>".$cgActionTypeDetailRequirement->getConstantName($objActionType->action_type_detail_requirement_id)."</td>\n";
		
		$arrAllowableAssoctiationTypes	= $objActionType->getAllowableActionAssociationTypes();
		foreach ($this->_arrActionAssociationTypeColumnOrder as $intActionAssociationTypeId)
		{
			$strAssociationTypeHTML	= "			<td>";
			if ($arrAllowableAssoctiationTypes[$intActionAssociationTypeId] instanceof Action_AssociationType)
			{
				$strAssociationTypeHTML	.= "<img class='icon_16' src='../admin/img/template/tick.png' alt='Yes' title='{$arrAllowableAssoctiationTypes[$intActionAssociationTypeId]->name}: Yes' />";
			}
			$strAssociationTypeHTML	.= "</td>\n";
			
			$strHTMLContent	.= $strAssociationTypeHTML;
		}
		
		$strHTMLContent	.=	"".
		"			<td>".($objActionType->is_automatic_only ? 'Automatic' : 'Quick Action')."</td>\n" .
		"			<td>".($objActionType->is_system ? 'System' : 'Custom')."</td>\n" .
		"			<td>".$cgActiveStatus->getConstantDescription($objActionType->active_status_id)."</td>\n" .
		"			<td>{$strActionEdit}</td>\n" .
		"		</tr>\n";
		
		return $strHTMLContent;
	}
}

?>