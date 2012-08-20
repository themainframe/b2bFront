<?php
/**
 * Autocomplete Classification Values
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Search for values with the current prefix
$value = $BF->in('term');
$attributeID = $BF->in('id');

$BF->db->select('value, COUNT(*) AS num', 'bf_item_attribute_applications')
       ->where('value LIKE \'{1}%\' AND value <> \'\' AND value <> \'{1}\' AND `classification_attribute_id` = \'{2}\'', 
          $value, $attributeID)
       ->group('value')
       ->order('COUNT(*)', 'desc')
       ->limit(15)
       ->execute();
           
// Buffer all
$values = array();
while($value = $BF->db->next())
{
  $values[] = array('id' => $value->value, 
                    'label' => $value->value,
                    'value' => $value->value);
}

// Parents
$BF->db->select('value, COUNT(*) AS num', 'bf_parent_item_attribute_applications')
       ->where('value LIKE \'{1}%\' AND value <> \'\' AND value <> \'{1}\' AND `classification_attribute_id` = \'{2}\'', 
          $value, $attributeID)
       ->group('value')
       ->order('COUNT(*)', 'desc')
       ->limit(15)
       ->execute();
           
// Buffer all
while($value = $BF->db->next())
{
  $values[] = array('id' => $value->value, 
                    'label' => $value->value,
                    'value' => $value->value);
}

// Output as JSON
print json_encode($values);

?>