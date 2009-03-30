<?php

class HtmlTemplate_Action_Type_Manage extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("action_type_manage");
		
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
		"				<span onclick='\$Alert(\"Add a new Action Type\");'><img class='icon_16' src='../admin/img/template/page_white_add.png' />Add a new Action Type</span>" .
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
		return	"" .
		"	<thead>\n" .
		"		<tr>\n" .
		"			<th>Code</th>\n" .
		"			<th>Name</th>\n" .
		"			<th>Description</th>\n" .
		"			<th>Details Required</th>\n" .
		"			<th>Automatic</th>\n" .
		"			<th>System</th>\n" .
		"			<th>Status</th>\n" .
		"			<th>&nbsp;</th>\n" .
		"		</tr>\n" .
		"	</thead>\n";
	}
	
	protected function _buildFooter()
	{
		return	"" .
		"	<tfoot>\n" .
		"		<tr>\n" .
		"			<th colspan='8'>&nbsp;</th>\n" .
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
	
	protected function _buildRecord($arrActionType)
	{
		static	$cgActionTypeDetailRequirement;
		static	$cgActiveStatus;
		$cgActionTypeDetailRequirement	= ($cgActionTypeDetailRequirement)	? $cgActionTypeDetailRequirement	: Constant_Group::getConstantGroup('action_type_detail_requirement');
		$cgActiveStatus					= ($cgActiveStatus) 				? $cgActiveStatus					: Constant_Group::getConstantGroup('active_status');
		
		$strActionEdit	= '&nbsp;';
		if ($arrActionType['active_status_id'] === ACTIVE_STATUS_ACTIVE && (($arrActionType['is_system'] && AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) || !$arrActionType['is_system']))
		{
			$strActionEdit	= "<img style='min-width: 16px; max-width: 16px; min-height: 16px; max-height: 16px;' src='../admin/img/template/page_white_edit.png' onclick='\$Alert(\"Editing {$arrActionType['id']}\");' />";
		}
		
		return	"" .
		"		<tr>\n" .
		"			<td>{$arrActionType['id']}</td>\n" .
		"			<td>{$arrActionType['name']}</td>\n" .
		"			<td>{$arrActionType['description']}</td>\n" .
		"			<td>".$cgActionTypeDetailRequirement->getConstantDescription($arrActionType['action_type_detail_requirement_id'])."</td>\n" .
		"			<td>".($arrActionType['is_automatic_only'] ? 'Automatic' : 'Quick Action')."</td>\n" .
		"			<td>".($arrActionType['is_system'] ? 'System' : 'Custom')."</td>\n" .
		"			<td>".$cgActiveStatus->getConstantDescription($arrActionType['active_status_id'])."</td>\n" .
		"			<td>{$strActionEdit}</td>\n" .
		"		</tr>\n";
	}
}

?>