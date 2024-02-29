<?php
///////////////////////////////////////////////////////////////////////////////
// print a message to the log file
function flog($msg, $type="LOG")
{
    $logPath = __DIR__ . "/log/lti.log";

    // generate a log message line
    $logMsg = date("Y-m-d H:i:s") . " - ";
    if($type)
        $logMsg .= "[" . $type . "] ";
    $logMsg .= $msg . "\n";

    file_put_contents($logPath, $logMsg, FILE_APPEND | LOCK_EX);
}

///////////////////////////////////////////////////////////////////////////////
// print nested array
function printArray($a)
{
    foreach ($a as $k => $v)
    {
        if(is_array($v))
        {
            echo "<b>" . $k . "</b> = [<br>";
            printArray($v);
            echo " ]<br>";
        }
        else
        {
            echo "<b>" . $k . "</b> = " . $v;
        }
        echo "<br>\n";
    }
}

///////////////////////////////////////////////////////////////////////////////
// load config file
function getConfig($jsonFile)
{
    return json_decode(file_get_contents($jsonFile));
}

?>
