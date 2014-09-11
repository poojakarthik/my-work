<?php
class Query_Placeholder_LikeExpressionSet implements Query_Placeholder {
	private $_expressions;
	private $_value;
	private $_setOperator;

	const OPERATOR_OR = 'OR';
	const OPERATOR_AND = 'AND';

	// (<expressions[0]> LIKE <value> <setOperator> <expressions[0]> LIKE <value>…)
	public function __construct(array $expressions, $value, $setOperator) {
		$this->_expressions = $expressions;
		$this->_value = $value;

		$this->_setOperator = trim(strtoupper($setOperator));
		if (!preg_match('/OR|AND/', $this->_setOperator)) {
			throw new DomainException(sprintf('%s is not a valid LIKE Set operator (only OR & AND permitted)', var_export($setOperator, true)));
		}
	}

	public function evaluate($connectionType=FLEX_DATABASE_CONNECTION_DEFAULT) {
		if ($this->_expressions && count($this->_expressions)) {
			$evalutedValues = array();
			foreach ($this->_expressions as $expression) {
				$evalutedValues []= "{$expression} LIKE CONCAT('%', " . Query::prepareByPHPType(strval($this->_value), $connectionType) . ", '%')";
			}
			return sprintf('(%s)', implode(" {$this->_setOperator} ", $evalutedValues));
		} else {
			return "{$this->_value} = NULL"; // `<value> = NULL` will never match, and is an approximation of the logic
		}
	}

	static public function create(array $expressions, $value, $setOperator) {
		return new self($expressions, $value, $setOperator);
	}
}