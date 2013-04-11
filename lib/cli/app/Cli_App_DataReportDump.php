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
	'Priviledges' => ".var_export($aReport['Priviledges'], true).",
	'CreatedOn' => ".var_export($aReport['CreatedOn'], true).",
	'Documentation' => serialize(".self::_unserialize($aReport['Documentation'])."),
	'SQLTable' => ".var_export($aReport['SQLTable'], true).",
	'SQLSelect' => serialize(".self::_unserialize($aReport['SQLSelect'])."),
	'SQLWhere' => ".var_export($aReport['SQLWhere'], true).",
	'SQLFields' => serialize(".self::_unserialize($aReport['SQLFields'])."),
	'SQLGroupBy' => ".var_export($aReport['SQLGroupBy'], true).",
	'RenderMode' => ".var_export($aReport['RenderMode'], true).",
	'RenderTarget' => ".var_export($aReport['RenderTarget'], true).",
	'Overrides' => ".var_export($aReport['Overrides'], true).",
	'PostSelectProcess' => ".var_export($aReport['PostSelectProcess'], true).",
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