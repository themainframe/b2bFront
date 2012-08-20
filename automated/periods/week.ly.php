<?php 
/**
 * Automated/Scheduled Scripts
 * Script: Weekly Script
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
// Executes every Week
// Specifically, Monday 00:00
//

$BF->logEvent('Automation',
                  'Executing `week-ly` script as scheduled.', 1);


?>