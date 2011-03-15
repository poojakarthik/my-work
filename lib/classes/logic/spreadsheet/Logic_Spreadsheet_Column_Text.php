<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Spreadsheet_Column_Text
 *
 * @author JanVanDerBreggen
 */
class Logic_Spreadsheet_Column_Text extends  Logic_Spreadsheet_Column{
    //put your code here

     public function applyFormatting($oCellRange)
    {
        $oNumberFormat = $oCellRange->getNumberFormat();
        $oNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    }
    
      public function setCellValue($iRow, $mValue)
    {
         $this->oSpreadsheet->getActiveSheet()-> setCellValueExplicit(chr($this->getPosition()) . $iRow, $mValue, PHPExcel_Cell_DataType::TYPE_STRING);
         
    }

     
}
?>
