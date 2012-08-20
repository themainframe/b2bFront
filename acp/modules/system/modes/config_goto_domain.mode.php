<?php
/**
 * Module: System
 * Mode: Configuration redirect to domain page
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

// Find the domain
$domain = $BF->in('domain');

// Query for it
$BF->db->select('*', 'bf_config_domains')
           ->where('`name` = \'{1}\'', $domain)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=system&mode=config');
  exit();
}

$domainRow = $BF->db->next();

$BF->go('./?act=system&mode=config_modify&domain=' . $domainRow->id);

?>