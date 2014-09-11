<?php
class Query_Placeholder_LikeValueSet implements Query_Placeholder {
	private $_expression;
	private $_values;
	private $_setOperator;

	const OPERATOR_OR = 'OR';
	const OPERATOR_AND = 'AND';

	// (<expression> LIKE <values[0]> OR <expression> LIKE <values[1]>…)
	public function __construct($expression, array $values, $setOperator) {
		$this->_expression = $expression;
		$this->_values = $values;

		$this->_setOperator = trim(strtoupper($setOperator));
		if (!preg_match('/OR|AND/', $this->_setOperator)) {
			throw new DomainException(sprintf('%s is not a valid LIKE Set operator (only OR & AND permitted)', var_export($setOperator, true)));
		}
	}

	public function evaluate($connectionType=FLEX_DATABASE_CONNECTION_DEFAULT) {
		if ($this->_values && count($this->_values)) {
			$evalutedValues = array();
			foreach ($this->_values as $value) {
				$evalutedValues []= "{$this->_expression} LIKE CONCAT('%', " . Query::prepareByPHPType(strval($value), $connectionType) . ", '%')";
			}
			return sprintf('(%s)', implode(" {$this->_setOperator} ", $evalutedValues));
		} else {
			return "{$this->_expression} = NULL"; // `<expression> = NULL` will never match, and is an approximation of the logic
		}
	}

	static public function create($expression, array $values, $setOperator) {
		return new self($expression, $values, $setOperator);
	}
}