<?php
/**
 * Module: System
 * Mode: Cache Clear
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

// Clear the Memcache
$BF->memcache->flush();

$BF->admin->notifyMe('OK', 'All cache entries have been marked as stale.');
header('Location: ./?act=system&mode=info');
?>