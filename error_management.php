<?php
/**
 * Global error management definitions
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.2
 * @author Damien Walsh
 */
 
// Context check
if(!defined("BF_CONTEXT_INSTALLER") && !defined("BF_CONTEXT_INDEX") &&
   !defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Define the default exception handler
set_exception_handler(array("BFClass", "handleException"));

// Also redirect errors as exceptions
set_error_handler(array("BFClass", "handleError"));

?>