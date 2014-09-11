<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once FLEX_BASE_PATH.'/lib/PHPExcel/Classes/PHPExcel.php';
/**
 * Description of Logic_Spreadsheet
 *
 * @author JanVanDerBreggen
 */
class Logic_Spreadsheet {
	//put your code here
	protected $oPHPExcel;
	protected $aColumns = array();
	protected $sVersion;
	protected $aData = array();

	public function __construct($aColumns,  $aData = null, $sVersion = 'Excel2007')
	{
		$this->oPHPExcel = new PHPExcel();
		$this->sVersion = $sVersion;
		$this->setupColumns($aColumns);
		if ($aData!== null)
		{
			foreach ($aData as $aRecord)
			{
				$this->addRecord($aRecord);
			}
		}

	}

	public function getRowCount()
	{
	return count($this->aData);
	}

	private function setupColumns($aColumns)
	{
		$starting_pos = ord('A');
		$aColumnFormatting = array();
		$iColumnPosition = 0;
		foreach ($aColumns as $sColumn)
		{
			$aColumnParts = explode('|', $sColumn);
			$sColumnName = $aColumnParts[0];
			if (count($aColumnParts)>1)
			{
				$aFormat = explode('#', $aColumnParts[1]);
			}
			else
			{
			   $aFormat = null;
			}

			$oColumn =  Logic_Spreadsheet_Column::createColumn($sColumnName, $starting_pos+$iColumnPosition, $aFormat, $this);
			$this->aColumns[] = $oColumn;
			$this->getActiveSheet()->setCellValue(chr($oColumn->getPosition()) . '1',$oColumn->getName());
			$iColumnPosition++;

		}
	}

	public function addRecord($aRecord)
	{
		$iCol = 0;
		$this->aData[] = $aRecord;
		foreach ($aRecord as $sColumnName=>$mValue)
		{
			if (!isset ( $this->aColumns[$iCol]))
					$this->aColumns[$iCol] = Logic_Spreadsheet_Column::createColumn($sColumnName, ord('A')+$iCol, null, $this);
			$oColumn = $this->aColumns[$iCol];
			$oColumn->setCellValue(count($this->aData)+1, $mValue);
			$iCol++;
		}
	}

	public function getActiveSheet()
	{
		return $this->oPHPExcel->getActiveSheet();
	}

	public function applyColumnStyling()
	{
		$iNumRows = count($this->aData)+1;
		foreach ($this->aColumns as $oColumn)
		{
		   // $iPos =  $oColumn->getPosition();
			$sRange = chr($oColumn->getPosition()).'2:'.chr($oColumn->getPosition())."$iNumRows";
			$oStyle = $this->getActiveSheet()->getStyle($sRange);
			$oColumn->applyFormatting($oStyle);
		}
	}

	public function save($sOutputPath)
	{
		$this->applyColumnStyling();
		$objWriter = PHPExcel_IOFactory::createWriter($this->oPHPExcel, $this->sVersion);
		 $objWriter->save($sOutputPath);
	}

	public function saveAs($sOutputPath, $sType)
	{
		switch($sType)
		{
			case 'Excel2007':
				$this->applyColumnStyling();
				$objWriter = PHPExcel_IOFactory::createWriter($this->oPHPExcel, $sType);
				 $objWriter->save($sOutputPath);
				break;
			case 'CSV':
				$objWriter = PHPExcel_IOFactory::createWriter($this->oPHPExcel, $sType);
				 $objWriter->save($sOutputPath);
				break;
			default:
				$this->save($sOutputPath);
		}
	}


}
?>
