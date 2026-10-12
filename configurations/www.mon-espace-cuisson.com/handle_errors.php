<?php
/*
$site_host=$_SERVER['DOCUMENT_ROOT'];

ini_set('display_errors', false);
error_reporting(7);

set_error_handler("errorHandler");
register_shutdown_function("shutdownHandler");

function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context)
{
$error = date("d/m/Y") . ' ||| ' . date("H:i:s") . ' ||| LVL : ' . $error_level . ' ||| Message : ' . $error_message . ' ||| File : ' . $error_file . ' ||| Ligne : ' . $error_line;
switch ($error_level) {
    case E_ERROR:
    case E_CORE_ERROR:
    case E_COMPILE_ERROR:
    case E_PARSE:
        mylog($error, "fatal");
        break;
    case E_USER_ERROR:
    case E_RECOVERABLE_ERROR:
        mylog($error, "error");
        break;
    case E_WARNING:
    case E_CORE_WARNING:
    case E_COMPILE_WARNING:
    case E_USER_WARNING:
        mylog($error, "warn");
        break;
    case E_NOTICE:
    case E_USER_NOTICE:
        //mylog($error, "info");
        break;
    case E_STRICT:
        //mylog($error, "debug");
        break;
}
}

function shutdownHandler() //will be called when php script ends.
{
    $lasterror = error_get_last();
    switch ($lasterror['type'])
    {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_PARSE:
            $error = date("d/m/Y") . ' ||| ' . date("H:i:s") . ' ||| LVL : ' . $lasterror['type'] . ' ||| Message : ' . $lasterror['message'] . ' ||| File : ' . $lasterror['file'] . ' ||| Ligne : ' . $lasterror['line'];
            mylog($error, "fatal");
    }
}

function mylog($error, $errlvl)
{
    global $site_host;

    // $f = fopen("../../log/log_phperrors_" . date("Y-m-d") . ".txt","a");
    //echo $error;
    // $f = fopen("D:\wamp\www\ma-cave-a-vin\log\log_phperrors_" . date("Y-m-d") . ".txt","a");
    $f = fopen($site_host . "/log/phperrors_" . date("Y-m-d") . ".txt","a");
    fputs($f, $error . " " . $errlvl . "\r\n\r\n");
    fclose($f);
}
*/
?>