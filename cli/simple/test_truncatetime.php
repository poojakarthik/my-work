<?php

require_once("../../flex.require.php");

$intDatetime	= strtotime("2008-03-03 11:22:23");

CliEcho("FLOOR");
CliEcho(TruncateTime($intDatetime, 's', 'floor')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 's', 'floor')));
CliEcho(TruncateTime($intDatetime, 'i', 'floor')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'i', 'floor')));
CliEcho(TruncateTime($intDatetime, 'h', 'floor')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'h', 'floor')));
CliEcho(TruncateTime($intDatetime, 'd', 'floor')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'd', 'floor')));
CliEcho(TruncateTime($intDatetime, 'm', 'floor')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'm', 'floor')));
CliEcho(TruncateTime($intDatetime, 'y', 'floor')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'y', 'floor')));

CliEcho("CEIL");
CliEcho(TruncateTime($intDatetime, 's', 'ceil')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 's', 'ceil')));
CliEcho(TruncateTime($intDatetime, 'i', 'ceil')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'i', 'ceil')));
CliEcho(TruncateTime($intDatetime, 'h', 'ceil')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'h', 'ceil')));
CliEcho(TruncateTime($intDatetime, 'd', 'ceil')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'd', 'ceil')));
CliEcho(TruncateTime($intDatetime, 'm', 'ceil')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'm', 'ceil')));
CliEcho(TruncateTime($intDatetime, 'y', 'ceil')."; ".date('Y-m-d H:i:s', TruncateTime($intDatetime, 'y', 'ceil')));
?>