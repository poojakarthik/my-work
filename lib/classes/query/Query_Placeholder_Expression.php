<?php
class Query_Placeholder_Expression implements Query_Placeholder {
	private $_expression;
	private $_data;

	public function __construct($expression, $data=array()) {
		$this->_expression = $expression;
		$this->_data = $data;
	}

	public function evaluate($connectionType=FLEX_DATABASE_CONNECTION_DEFAULT) {
		$replaceData = array();
		foreach ($this->_data as $alias=>$value) {
			$alias = strval($alias);
			if (preg_match('/^[\w\-]+$/i', $alias)) {
				$value = Query::prepareByPHPType($value, $connectionType);
				$aliasRegex = "/\<{$alias}\>/";
				$replaceData[$aliasRegex] = (is_string($value)) ? addcslashes($value, '$\\') : $value;
				// Log::getLog()->log("Match pair '{$aliasRegex}':{$value} registered");
			}
		}
		return preg_replace(array_keys($replaceData), array_values($replaceData), $this->_expression);
	}

	static public function create($expression, $value) {
		return new self($expression, $value);
	}
}