<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author JanVanDerBreggen
 */
interface Logic_Payable {
    //put your code here

    public function getBalance();

    public function getAmount();

    public function processDistributable($mDistributable);
}
?>
