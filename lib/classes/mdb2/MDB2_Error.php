<?php
class MDB2_Error extends Exception {

	public static function fromPDOException(PDOException $oPDOException) {
		// TODO: Copy across all useful things from PDOException
		throw new self($oPDOException->getMessage());
	}

}