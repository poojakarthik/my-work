<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Spreadsheet_Column_Integer
 *
 * @author JanVanDerBreggen
 */
class Logic_Spreadsheet_Column_Integer extends Logic_Spreadsheet_Column{
    //put your code here

     public function applyFormatting($oCellRange)
    {
        $oNumberFormat = $oCellRange->getNumberFormat();
        $oNumberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
    }
}
?>
