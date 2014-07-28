<?php
class Query_Placeholder_QueryPlaceholderSet implements Query_Placeholder {
	private $_queryPlaceholders;
	private $_setOperator;

	const OPERATOR_OR = 'OR';
	const OPERATOR_AND = 'AND';

	// (<queryPlaceholders[0]> <setOperator> <queryPlaceholders[1]>…)
	public function __construct(array $queryPlaceholders, $setOperator) {
		$this->_queryPlaceholders = $queryPlaceholders;

		$this->_setOperator = trim(strtoupper($setOperator));
		if (!preg_match('/OR|AND/', $this->_setOperator)) {
			throw new DomainException(sprintf('%s is not a valid LIKE Set operator (only OR & AND permitted)', var_export($setOperator, true)));
		}
	}

	public function evaluate($connectionType=FLEX_DATABASE_CONNECTION_DEFAULT) {
		if ($this->_queryPlaceholders && count($this->_queryPlaceholders)) {
			$evalutedValues = array();
			foreach ($this->_queryPlaceholders as $queryPlaceholder) {
				$evalutedValues []= $queryPlaceholder->evaluate();
			}
			return sprintf('(%s)', implode(" {$this->_setOperator} ", $evalutedValues));
		} else {
			return 'NULL = NULL'; // `NULL = NULL` will never match, and is an approximation of the logic
		}
	}

	static public function create(array $expressions, $value, $setOperator) {
		return new self($expressions, $value, $setOperator);
	}
}