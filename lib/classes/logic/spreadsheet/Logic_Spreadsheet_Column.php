<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Spreadsheet_Column
 *
 * @author JanVanDerBreggen
 */
class Logic_Spreadsheet_Column {


    protected $sName;
    protected $iPosition;
    protected $oSpreadsheet;
   

    public function __construct($sName, $iPosition, $oSpreadsheet)
    {
        $this->sName = $sName;
        $this->iPosition = $iPosition;
        $this->oSpreadsheet = $oSpreadsheet;
    }


    public function setCellValue($iRow, $mValue) {
        $iPosition = $this->getPosition() - 64;
        $sPosition = '';
        while ($iPosition > 0) {
            $iRemainder = ($iPosition -1) % 26;
            $sPosition = chr($iRemainder + 65) . $sPosition;
            $iPosition = intval(($iPosition - $iRemainder) / 26);
        }
        $sheet = $this->oSpreadsheet->getActiveSheet()-> setCellValue($sPosition . $iRow,$mValue);
    }

    public function applyFormatting($oCellRange)
    {

    }

    public function getPosition($bCharacter = false)
    {
        return $this->iPosition;
    }

    public function getName()
    {
        return $this->sName;
    }
    
    public static function createColumn($sColumnName, $iColumnPosition, $aColumnFormatting, $oSpreadsheet)
    {
        switch ($aColumnFormatting[0])
        {
            case 'int':
                return new Logic_Spreadsheet_Column_Integer($sColumnName,  $iColumnPosition, $oSpreadsheet);
                break;
            case 'currency':
                return new Logic_Spreadsheet_Column_Currency($sColumnName, $iColumnPosition, $oSpreadsheet);
                break;
            case 'url':
                return new Logic_Spreadsheet_Column_URL($sColumnName, $iColumnPosition,$aColumnFormatting[1], $oSpreadsheet );
                break;
            case 'text':
            case 'fnn' :
                 return new Logic_Spreadsheet_Column_Text($sColumnName, $iColumnPosition, $oSpreadsheet );
                 break;
            default:
                return new self($sColumnName, $iColumnPosition, $oSpreadsheet);
        }
   
        
    }


}
?>
