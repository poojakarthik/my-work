<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Spreadsheet_Column_URL
 *
 * @author JanVanDerBreggen
 */
class Logic_Spreadsheet_Column_URL extends Logic_Spreadsheet_Column{
    //put your code here

    protected $sURLFormat;
    
    public function __construct($sName, $iPosition, $sURLFormat, $oSpreadsheet)
    {
        parent::__construct($sName, $iPosition, $oSpreadsheet);
        $this->sURLFormat = $sURLFormat;
        
    }

     public function applyFormatting($oCellRange)
    {
        $oColor = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLUE);
        $oFontFormat = $oCellRange->getFont();
        $oFontFormat->setColor($oColor);
        $oFontFormat->setUnderline( PHPExcel_Style_Font::UNDERLINE_SINGLE);
     }

    public function setCellValue($iRow, $mValue)
    {
        $sURL = str_replace ( "{".$this->sName."}" , $mValue , $this->sURLFormat );
       $this->oSpreadsheet->getActiveSheet()->   setCellValue(chr($this->getPosition()) . $iRow,$mValue);
        $this->oSpreadsheet->getActiveSheet()-> getCell(chr($this->getPosition()) . $iRow)->getHyperlink()->setUrl( $sURL);
    }

   
}
?>
