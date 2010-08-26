<?php

abstract class Correspondence_Source
{
protected $_oCorrespondenceSourceType; //this could be the ORM object, or perhaps just the constant name


/*
 * to be implemented by each child class
 * every implementation of this method must return data in the same format
 */
abstract public function getData();

}


?>