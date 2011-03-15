<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Stopwatch
 *
 * @author JanVanDerBreggen
 */
class Logic_Stopwatch extends Stopwatch{
    protected static $instance;

    public static function getInstance($bReset = false)
    {
        
        if (self::$instance === null)
            self::$instance = new self();

        return self::$instance;
    }
}
?>
