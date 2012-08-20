<?php
/**
 * Module: Inventory
 * Mode: Browse Modify Filters Add Do
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


// Build the validation array
$validation = array(
  
    'name' => array(
    
               'validations' => array(
                                 'max' => array(20),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
              )

);

// Check each field
foreach($validation as $fieldName => $fieldData)
{
  // Create a validator
  $validator = new FormValue($fieldData['value'], $fieldData['name'], & $BF);

  // Check
  if($validator->batch($fieldData['validations'])->failed())
  {
    // Failed - Pack up fields and redirect
    $BF->admin->packAndRedirect('./?act=inventory&mode=browse_modify_filters_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Load the map from a PList.
$mapListParser = new PropertyList();
$map = $mapListParser->parseFile(
  BF_ROOT . '/acp/definitions/qb_sql_map.plist');
  
// Build the query from blocks
$whereClause = '';
$query = $BF->in('f_query');
$queryParts = explode(' ', $query);

// Build a SQL query
$sql = 'SELECT * FROM `bf_items` WHERE ';

foreach($queryParts as $part)
{
  if($part == '')
  {
    // Don't parse blank spaces
    continue;
  }
  
  // Check if it is valid
  if(array_key_exists($part, $map))
  {
    // Replace and add to query
    $whereClause .= $map[$part];
  }
  else
  {
    // Rigourously filter and add
    $value = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $part);
    
    // Number?
    if(!is_numeric($value))
    {
      $value = '\'' . $value . '\'';
    }
    
    $whereClause .= $value;
  }
}

// Add to main query
$sql .= $whereClause;

// Try executing
try 
{
  $BF->db->text($sql)
             ->execute();
}
catch(Exception $exception) 
{
  // Error due to bad query
  header('Location: ./?act=inventory&mode=browse_modify_filters_add');
  exit();
}

// Create a filter
$BF->db->insert('bf_admin_inventory_browse_filters', array(
                     'name' => $BF->in('f_name'),
                     'sql_where' => $whereClause
                   ))
           ->execute();

$BF->admin->notifyMe('OK', 'The filter was created.');
header('Location: ./?act=inventory&mode=browse');

?>