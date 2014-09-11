<?php
interface Query_Placeholder {
	public function evaluate($sConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT);
}