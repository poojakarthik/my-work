<?php

class Cli_App_DataReportDump extends Cli {
	const SWITCH_REPORT_ID = 'i';

	function run() {
		try {
			$aArgs = $this->getValidatedArguments();
			$aReport = DataReport::getForId($aArgs[self::SWITCH_REPORT_ID])->toArray();

			echo "<?php
// Data Report #{$aReport['Id']}
\$arrDataReport = array(
	'Name' => ".var_export($aReport['Name'], true).",
	'FileName' => ".var_export($aReport['FileName'], true).",
	'Summary' => ".var_export($aReport['Summary'], true).",
	/*
	 * 	Priviledges
	 *		This number represents the employee permissions required to be able to execute the report.
	 *		To specify it you can the following php constants (numeric value in parentheses):
	 *			PERMISSION_PUBLIC (".PERMISSION_PUBLIC.")
	 *			PERMISSION_ADMIN (".PERMISSION_ADMIN.")
	 *			PERMISSION_OPERATOR (".PERMISSION_OPERATOR.")
	 *			PERMISSION_SALES (".PERMISSION_SALES.")
	 *			PERMISSION_ACCOUNTS (".PERMISSION_ACCOUNTS.")
	 *			PERMISSION_RATE_MANAGEMENT (".PERMISSION_RATE_MANAGEMENT.")
	 *			PERMISSION_CREDIT_MANAGEMENT (".PERMISSION_CREDIT_MANAGEMENT.")
	 *			PERMISSION_OPERATOR_VIEW (".PERMISSION_OPERATOR_VIEW.")
	 *			PERMISSION_OPERATOR_EXTERNAL (".PERMISSION_OPERATOR_EXTERNAL.")
	 *			PERMISSION_CUSTOMER_GROUP_ADMIN (".PERMISSION_CUSTOMER_GROUP_ADMIN.")
	 *			PERMISSION_KB_USER (".PERMISSION_KB_USER.")
	 *			PERMISSION_KB_ADMIN_USER (".PERMISSION_KB_ADMIN_USER.")
	 *			PERMISSION_SALES_ADMIN (".PERMISSION_SALES_ADMIN.")
	 *			PERMISSION_PROPER_ADMIN (".PERMISSION_PROPER_ADMIN.")
	 *			PERMISSION_SUPER_ADMIN (".PERMISSION_SUPER_ADMIN.")
	 *			PERMISSION_DEBUG (".PERMISSION_DEBUG.")
	 *			PERMISSION_GOD (".PERMISSION_GOD.")
	 *		They can be OR'd by using the | character (e.g. PERMISSION_ADMIN | PERMISSION_OPERATOR)
	 */
	'Priviledges' => ".var_export($aReport['Priviledges'], true).",
	'CreatedOn' => ".var_export($aReport['CreatedOn'], true).",
	// Shouldn't need to change this one
	'Documentation' => serialize(".self::_unserialize($aReport['Documentation'])."),
	// FROM clause
	'SQLTable' => ".var_export($aReport['SQLTable'], true).",
	/*
	 *	SELECT fields
	 *		The key to the array is the alias. 
	 *		The 'Value' property is the actual query clause (e.g. a.Id)
	 *		The 'Type' property is optional and can be used to define how the selected value will be treated in excel, use the following php constants: 
	 *			EXCEL_TYPE_CURRENCY (".EXCEL_TYPE_CURRENCY.")
	 *			EXCEL_TYPE_INTEGER  (".EXCEL_TYPE_INTEGER .")
	 *			EXCEL_TYPE_PERCENTAGE (".EXCEL_TYPE_PERCENTAGE.")
	 *			EXCEL_TYPE_FNN (".EXCEL_TYPE_FNN.")
	 */
	'SQLSelect' => serialize(".self::_unserialize($aReport['SQLSelect'])."),
	// WHERE clause
	'SQLWhere' => ".var_export($aReport['SQLWhere'], true).",
	/*
	 *	SQLFields
	 *		This is where you can define the input parameters.
	 *		The array key is the query placeholder. To reference the parameter in the query use <Placeholder>.
	 *		- 'Type' is one of the following: dataInteger, dataString, dataBoolean, dataFloat, dataDate or dataDatetime.
	 *		- Leave 'Documentation-Entity' as 'DataReport'.
	 *		- 'Documentation-Field' is the name of the parameter as it will appear in the user interface
	 */
	'SQLFields' => serialize(".self::_unserialize($aReport['SQLFields'])."),
	// GROUP BY clause
	'SQLGroupBy' => ".var_export($aReport['SQLGroupBy'], true).",
	// This defines how the report executes, whether it runs on the spot (REPORT_RENDER_INSTANT) or is run in the background and the result is sent via email (REPORT_RENDER_EMAIL)
	'RenderMode' => ".self::_getRenderModeConstant($aReport['RenderMode']).",
	// Leave this null to allow the option of csv or excel as the output format
	'RenderTarget' => ".var_export($aReport['RenderTarget'], true).",
	// Leave null
	'Overrides' => ".var_export($aReport['Overrides'], true).",
	// Leave null
	'PostSelectProcess' => ".var_export($aReport['PostSelectProcess'], true).",
	/* 	data_report_status_id
	 * 		The status of the report
	 *			DATA_REPORT_STATUS_DRAFT -- will only show in the user interface for those with enough permission (?). can still be edited.
	 *			DATA_REPORT_STATUS_ACTIVE -- can be viewed/run via the user interface. cannot be edited anymore.
	 *			DATA_REPORT_STATUS_INACTIVE -- will not show in the user interface. cannot be edited anymore
	 */
	'data_report_status_id' => ".self::_getStatusConstant($aReport['data_report_status_id'])."
);
?>";
			return 0;
		} catch(Exception $oException) {
			$this->showUsage('Error: '.$oException->getMessage());
			return 1;
		}
	}

	function getCommandLineArguments() {
		return array(
			self::SWITCH_REPORT_ID => array(
				self::ARG_REQUIRED => true,
				self::ARG_DESCRIPTION => "id of the data report to output",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s");'
			)
		);
	}

	private static function _getStatusConstant($iStatus) {
		$aStatus = Query::run("	SELECT const_name
								FROM data_report_status
								WHERE id = <id>",
								array('id' => $iStatus))->fetch_assoc();
		return $aStatus['const_name'];
	}

	private static function _getRenderModeConstant($iRenderMode) {
		return $GLOBALS['*arrConstant']['ReportRender'][$iRenderMode]['Constant'];
	}

	private static function _unserialize($sValue) {
		$aValue = unserialize($sValue);
		return self::_getArrayOutput($aValue);
	}

	private static function _getArrayOutput($aValue, $iTabLevel=2) {
		$sTabString = '';
		$sEndTabString = '';
		for ($i = 0; $i < $iTabLevel; $i++) {
			$sTabString .= "\t";
			if ($i < ($iTabLevel - 1)) {
				$sEndTabString .= "\t";
			}
		}

		$aLines = array("array(");
		foreach ($aValue as $sKey => $mValue) {
			$sLine = "{$sTabString}'{$sKey}' => ";
			if (is_array($mValue)) {
				$sLine .= self::_getArrayOutput($mValue, $iTabLevel + 1).',';
			} else {
				$sLine .= var_export($mValue, true).",";
			}

			$aLines[] = $sLine;
		}

		$aLines[count($aLines) - 1] = preg_replace('/\,$/', '', $aLines[count($aLines) - 1]);
		$aLines[] = "{$sEndTabString})";

		return implode("\n", $aLines);
	}
}