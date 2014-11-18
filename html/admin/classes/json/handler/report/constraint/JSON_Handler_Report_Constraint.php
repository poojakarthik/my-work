<?php
class JSON_Handler_Report_Constraint extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getForReportId($mData) {
		try {
			// Check user authorisation and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);
			$aConstraints = array();
			$aConstraintResult = Report_Constraint::getConstraintForReportId($mData->iReportId);
			foreach ($aConstraintResult as $iReportConstraintId=>$oReportConstraintObject) {
				$aConstraint = $oReportConstraintObject->toArray();
				//Mapping Constraint Type to UI Elements
				switch($aConstraint['report_constraint_type_id']) {
				 	case REPORT_CONSTRAINT_TYPE_FREETEXT:
				 		$aConstraint['component_type'] = REPORT_CONSTRAINT_TYPE_FREETEXT;
				 		break;
				 	case REPORT_CONSTRAINT_TYPE_DATABASELIST:
				 		$rQuery	= Query::run($aConstraint['source_query']);
						$aDataSet= array();
						while ($aResultSet = $rQuery->fetch_assoc()) {
							$aDataSet[] = $aResultSet;
						}
						$aConstraint['component_type'] = REPORT_CONSTRAINT_TYPE_DATABASELIST;
						$aConstraint['source_data'] = $aDataSet;
						unset($aConstraint['source_query']);
						break;
					case REPORT_CONSTRAINT_TYPE_MULTIPLESELECTIONLIST:
				 		$rQuery	= Query::run($aConstraint['source_query']);
						$aDataSet= array();
						while ($aResultSet = $rQuery->fetch_assoc()) {
							$aDataSet[] = $aResultSet;
						}
						$aConstraint['component_type'] = REPORT_CONSTRAINT_TYPE_MULTIPLESELECTIONLIST;
						$aConstraint['source_data'] = $aDataSet;
						unset($aConstraint['source_query']);
						break;
					case REPORT_CONSTRAINT_TYPE_DATE:
						$aConstraint['component_type'] = REPORT_CONSTRAINT_TYPE_DATE;
				 		break;
				 	case REPORT_CONSTRAINT_TYPE_DATETIME:
						$aConstraint['component_type'] = REPORT_CONSTRAINT_TYPE_DATETIME;
				 		break;
				 	default:
				 		break;
				}
				$aConstraints[] = $aConstraint;
			}
			return $aConstraints;

		} catch (JSON_Handler_Account_Run_Exception $oException) {
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}
	public function validateSourceQuery($sSourceQuery) {
		try {
			$aResultSet = Query::run($sSourceQuery);
			$aRow = $aResultSet->fetch_assoc();
			if (count($aRow) > 2) {
				return 	array(
					'success' => true,
					'bSuccess'	=> false,
					'sMessage'	=> 'Select query cannot have more than two columns'
				);
			}
			if(!isset($aRow['label']) || ! isset($aRow['value'])) {
				return 	array(
					'success' => true,
					'bSuccess'	=> false,
					'sMessage'	=> 'Select columns are not named properly as "label" and "value" (case sensitive)'
				);
			}
			return array(
				'success' => true,
				'bSuccess' => true
			);
		} catch (Exception $e) {
			return 	array(
				'success' => true,
				'bSuccess'	=> false,
				'sMessage'	=> 'Source query is not a valid query'
			);
		}
			
	}
}