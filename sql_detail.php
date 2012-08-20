<?php
/** 
 * SQL Connection information file
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.1
 * @author Damien Walsh <damien@transcendsolutions.net>
 */

// Context check for admin and index
if(!defined("BF_CONTEXT_INSTALLER") && !defined("BF_CONTEXT_INDEX") &&
   !defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Define SQL Connection details
define('BF_SQL_USER',   'root');
define('BF_SQL_PASS',   'root');
define('BF_SQL_DB',     'b2bfront');
define('BF_SQL_HOST',   '127.0.0.1:8889');

// Define the system secret
// This is used to Salt passwords stored in the database.
// Do not change this value once a data set is initiated!
define('BF_SECRET', '');

?>