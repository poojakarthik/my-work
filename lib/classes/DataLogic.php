<?php
interface DataLogic 
{
   /* 
    * save() should handle the validation and saving of
    * all data members
    */
	public function save();

    /*
     * toArray() should transform all data members into arrays,
     * and return all these in one array.
     */
	public function toArray();


    /*
     * The Data Logic class has one or more data members
     * the main data member, if it is database related
     * would typically be called $oDO.
     * The __get and __set basically handle interaction with these data members
     */
	public function __get($sField);
	public function __set($sField, $mValue);
}
?>
