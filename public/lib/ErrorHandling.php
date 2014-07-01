<?php
/*********************************************
 * Filename    : ExceptionHandler.php
 * Author      : Christopher Shireman
 * Create Date : 08/08/2005
 * Description : Setting up the exception handle for elements.  Also
 *               setup default error handler in elements
 * Change Log  :
 *    08/08/2005 - Created file.
 *********************************************/

define("ERROR_TO","chris@nit.cc");
define("NIT_DEBUG",true);

function ExceptionHandler($exception)
{
   $timestamp = date("Y-m-d H:i:s",strtotime("now"));

   $message = "An exception has occured in elements.  Please see details below.\n";
   $message .= $exception->getMessage()."\n";
   $message .= "Filename: " . $exception->getFile();
   $message .= "Line: " . $exception->getLine();
   $message .= $exception->getTraceAsString();
   
   mail(ERROR_TO,"Elements Exception",$message);
   
   $message = "[$timestamp][EXCEPTION][elements][".$exception->getFile().":".$exception->getLine()."][".$exception->getMessage()."][".$exception->getTraceAsString()."]";
   error_log($message);

   //header("Location: /global/templates/error.html");
   print($message);
}

function ErrorHandler($errno,$errstr,$errfile,$errline,$errcontext)
{
   $timestamp = date("Y-m-d H:i:s",strtotime("now"));

   ob_start();
      print_r($errcontext);
      $errcontext = ob_get_contents();
   ob_end_clean();
      
   ob_start();
      debug_print_backtrace();
      $backtrace = ob_get_contents();
   ob_end_clean();
   
   $message = "An error has occured in elements.  Please see details below.\n";
   $message .= "Error Number: $errno\n";
   $message .= "Error Message: $errstr\n";
   $message .= "Filename: $errfile\n";
   $message .= "Line: $errline\n";
   $message .= "Stack Trace: $backtrace\n";
   //$message .= "Context: $errcontext\n";
   
   mail(ERROR_TO,"Elements Error",$message);

   switch($errno)
   {
      case E_ERROR           : $errno = "Error";
                               break;
      case E_WARNING         : $errno = "Warning";
                               break;
      case E_PARSE           : $errno = "Parse Error";
                               break;
      case E_NOTICE          : $errno = "Notice";
                               break;
      case E_CORE_ERROR      : $errno = "Code Error";
                               break;
      case E_CORE_WARNING    : $errno = "Core Warning";
                               break;
      case E_COMPILE_ERROR   : $errno = "Compile Error";
                               break;
      case E_COMPILE_WARNING : $errno = "Compile Warning";
                               break;
      case E_USER_ERROR      : $errno = "User Error";
                               break;
      case E_USER_WARNING    : $errno = "User Warning";
                               break;
      case E_USER_NOTICE     : $errno = "User Notice";
                               break;
      default                : $errno = "General Error";
                               break;
   }


   $message = "[$timestamp][ERROR][elements][".$errfile.":".$errline."][".$errno."][".$errstr."]";
   error_log($message);

   if($errno != "Notice")
   {
      print($message);
//      header("Location: /global/templates/error.html");
      exit();
   }
}

function AssertionHandler($file,$line,$code)
{
   $timestamp = date("Y-m-d H:i:s",strtotime("now"));
   
   $message = "An assertion has failed in elements.  Please see details below.\n";
   $message .= "Filename: $file\n";
   $message .= "Line: $line\n";
   $message .= "Code: $code\n";

   mail(ERROR_TO,"Elements Assertion Failure",$message);
   
   $message = "[$timestamp][ASSERT_FAILURE][elements][".$file.":".$line."][".$code."]";
   error_log($message);

//   header("Location: /global/templates/error.html");
   print($message);
   exit();
}


//set_exception_handler("ExceptionHandler");
//set_error_handler("ErrorHandler");
assert_options(ASSERT_CALLBACK, 'AssertionHandler');
assert_options(ASSERT_BAIL, true);
assert_options(ASSERT_WARNING, false);
assert_options(ASSERT_QUIET_EVAL, true);

if(defined("NIT_DEBUG") && NIT_DEBUG)
{
   error_reporting(E_ALL ^ E_NOTICE);
   ini_set('display_errors','1');
   ini_set('log_errors','0');
}
else
{
   error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_USER_ERROR);
   ini_set('display_errors','0');
   ini_set('log_errors','1');
}

?>
