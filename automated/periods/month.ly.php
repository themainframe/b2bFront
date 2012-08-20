<?php 
/**
 * Automated/Scheduled Scripts
 * Script: Monthly Script
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_AUTOMATION") && !defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

//
// Executes every Month
// Specifically, 1st day, 00:00
//

$BF->logEvent('Automation',
                  'Executing `month-ly` script as scheduled.', 1);


?>