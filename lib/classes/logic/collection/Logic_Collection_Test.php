<?php
/**
 * Description of Collection_Logic_Suspension
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Test
{

    public static function promiseBatchProcessDataLayerTest()
    {

    }

    public static function promiseGetActivePromises()
    {

    }

    public static function collectableGetForPromiseId()
    {

    }

    public static function PromiseInstalmentGetForPromiseId()
    {

    }

    public static function promiseBatchProcessTest()
    {
        $bPassed = Logic_Collection_Test_Collection_Promise::testGetActivePromises();

        $bPassed = Logic_Collection_Test_Collection_Promise::testProcess();

    }


}
?>
