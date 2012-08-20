<?php
/**
 * Module: Website
 * Mode: Skin Do Set
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

// Find skin
$skinsRoot = BF_ROOT . '/skins/';
$skinPath = $skinsRoot . '/' . $BF->in('name') . '/';

// Valid?
if(Tools::exists($skinPath . '/skin.xml'))
{
  // Set config
  $BF->config->set('com.b2bfront.site.skin', str_replace('.skin', '', $BF->in('name')));
  
  // Notify      
  $BF->admin->notifyMe('OK', 'The website skin has been changed.');
  header('Location: ./?act=website&mode=skin');
}
else
{
  // Fail
  header('Location: ./?act=website&mode=skin');
}

?>