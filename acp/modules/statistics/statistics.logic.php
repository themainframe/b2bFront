<?php
/**
 * Admin Module Logic : Statistics
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Gain BFClass access
global $BF;

// Define the module path
define('MODULE_PATH', '/acp/modules/statistics/');

// Decide which mode to start in
if($BF->in('mode') == '')
{
  $BF->setIn('mode', 'live');
}

?>