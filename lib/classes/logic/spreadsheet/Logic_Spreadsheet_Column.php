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


    public function setCellValue($iRow, $mValue)
    {
         $sheet = $this->oSpreadsheet->getActiveSheet()-> setCellValue(chr($this->getPosition()) . $iRow,$mValue);
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
