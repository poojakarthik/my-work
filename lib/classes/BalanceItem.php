<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author JanVanDerBreggen
 */
interface BalanceItem {
    public function isDebit();

    public function isCredit();
}
?>
