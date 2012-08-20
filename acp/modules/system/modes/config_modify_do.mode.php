<?php
/**
 * Module: System
 * Mode: Config Modify Do
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

// Read each of the config items in the domain
$configRows = $BF->db->query();
$configRows->select('*', 'bf_config')
           ->where('domain_id = \'{1}\' AND admin_editable = \'1\'', $BF->inInteger('id'))
           ->execute();

// Update each
while($configRow = $configRows->next())
{
  // Retrieve value
  $configRowValue = $BF->inUnfiltered('f_' . $configRow->id);
  
  // Get the type
  $configRowType = $configRow->type;
  
  // Decide what data transformations are needed
  switch($configRowType)
  {
    case 'integer':
      $configRowValue = intval($configRowValue);
      break;
      
    case 'boolean':
      $configRowValue = ($configRowValue == '1' ? '1' : '0');
      break;
  }
  
  // Update
  $BF->db->update('bf_config', array(
                       'value' => $configRowValue
                     ))
             ->where('id = \'{1}\'', $configRow->id)
             ->limit(1)
             ->execute();
}

// Sync config hive
$BF->config->sync();

// Generate a notification
$BF->admin->notifyMe('OK', 'The configuration was updated.');

// Finished
header('Location: ./?act=system&mode=config');

?>