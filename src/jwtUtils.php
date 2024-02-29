<?php
// jwtUtils.php
// ============
// encode/decode JWT
//
//  AUTHOR: Song Ho Ahn (song.ahn@gmail.com)
// CREATED: 2024-01-11
// UPDATED: 2024-01-30
///////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// print a messaghe to the log file
function flog($msg, $type="")
{
    $logPath = __DIR__ . "/" . $GLOBALS["config"]["logFile"];
    //echo $logPath;

    // generate a log message line
    $logMsg = date("Y-m-d H:i:s") . " - ";
    if($type)
        $logMsg .= "[" . $type . "] ";
    $logMsg .= $msg . "\n";

    file_put_contents($logPath, $logMsg, FILE_APPEND | LOCK_EX);
}

// merge array of (header, payload, signature) to JWT
// header and payload will be encoded from string to base64url
function mergeJwt($header, $payload, $signature)
{
    $header = encodeBase64Url($header);
    $payload = encodeBase64Url($payload);
    return $header . "." . $payload . "." . $signature;
}

// split JWT into array of (header, payload, signature)
// header and payload are decoded from base64url to string
function splitJwt($jwt="")
{
    $array = array();   // return value
    $tokens = explode(".", $jwt);
    if(isset($tokens[0]))
    {
        // decode header part as array
        $array["header"] = json_decode(decodeBase64Url($tokens[0]), true);
    }
    if(isset($tokens[1]))
    {
        // decode payload part as array
        $array["payload"] = json_decode(decodeBase64Url($tokens[1]), true);
    }
    if(isset($tokens[2]))
    {
        $array["signature"] = $tokens[2];
    }
    return $array;
}

// convert a string to Base64URL
function encodeBase64Url($data)
{
    // convert it to Base64 first
    $base64 = base64_encode($data);
    // convert Base64 to Base64URL by replacing "+"=>"-" and "/"=>"_"
    $base64url = strtr($base64, "+/", "-_");
    // remove padding char(=) at the end
    return rtrim($base64url, "=");
}

// convert Base64URL data to a string
// if $strict=true, it returns false if data contains outside base64 chars
// if $strict=false, it silently discards non-base64 chars 
function decodeBase64Url($data, $strict=true)
{
    // convert it to Base64 first by replacing "-"=>"+" and "_"=>"/"
    $base64 = strtr($data, "-_", "+/");
    // convert Base64 to string
    return base64_decode($base64, $strict); 
}
?>
