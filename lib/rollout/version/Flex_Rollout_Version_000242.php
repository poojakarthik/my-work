<?php

/**
 * Version 242 of database update.
 *
 *	Collections - Data migration for payments and adjustments
 *
 */

class Flex_Rollout_Version_000242 extends Flex_Rollout_Version
{
		private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Create the payment_request_invoice table",
									'sAlterSQL'			=>	"
															CREATE TABLE	payment_request_invoice (
																id					BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT,
																payment_request_id	BIGINT	UNSIGNED	NOT NULL,
																invoice_id			BIGINT	UNSIGNED	NOT NULL,

																CONSTRAINT	pk_payment_request_invoice_id					PRIMARY KEY (id),
																CONSTRAINT	fk_payment_request_invoice_payment_request_id	FOREIGN KEY	(payment_request_id)	REFERENCES payment_request(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																CONSTRAINT	fk_payment_request_invoice_invoice_id			FOREIGN KEY	(invoice_id)			REFERENCES Invoice(Id)			ON UPDATE CASCADE	ON DELETE RESTRICT
															) ENGINE=InnoDB;
														",
									'sRollbackSQL'		=>	"DROP TABLE	payment_request_invoice;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the payment_request_collection_promise_instalment table",
									'sAlterSQL'			=>	"
															CREATE TABLE	payment_request_collection_promise_instalment (
																id									BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT,
																payment_request_id					BIGINT	UNSIGNED	NOT NULL,
																collection_promise_instalment_id	BIGINT	UNSIGNED	NOT NULL,

																CONSTRAINT	pk_payment_request_promise_instalment_id					PRIMARY KEY (id),
																CONSTRAINT	fk_payment_request_promise_instalment_payment_request_id	FOREIGN KEY	(payment_request_id)				REFERENCES payment_request(id)					ON UPDATE CASCADE	ON DELETE RESTRICT,
																CONSTRAINT	fk_payment_request_promise_instalment_promise_instalment_id	FOREIGN KEY	(collection_promise_instalment_id)	REFERENCES collection_promise_instalment(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
															) ENGINE=InnoDB;
														",
									'sRollbackSQL'		=>	"DROP TABLE	payment_request_collection_promise_instalment;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the payment_request_invoice table",
									'sAlterSQL'			=>	"
															INSERT INTO	payment_request_invoice
																(payment_request_id	, invoice_id)
															SELECT		pr.id,
																		i.Id
															FROM		payment_request pr
																		JOIN Invoice i ON (i.Account = pr.account_id AND i.invoice_run_id = pr.invoice_run_id)
															WHERE		1;
														",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								)
							);


		// Perform Batch Rollout
		$iRolloutVersionNumber	= self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation)
		{
			$iStepNumber++;

			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");

			// Attempt to apply changes
			$oResult	= Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (PEAR::isError($oResult))
			{
				throw new Exception(__CLASS__ . " Failed to {$aOperation['sDescription']}. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
			}

			// Append to Rollback Scripts (if one or more are provided)
			if (array_key_exists('sRollbackSQL', $aOperation))
			{
				$aRollbackSQL	= (is_array($aOperation['sRollbackSQL'])) ? $aOperation['sRollbackSQL'] : array($aOperation['sRollbackSQL']);

				foreach ($aRollbackSQL as $sRollbackQuery)
				{
					if (trim($sRollbackQuery))
					{
						$this->rollbackSQL[] =	$sRollbackQuery;
					}
				}
			}
		}
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>