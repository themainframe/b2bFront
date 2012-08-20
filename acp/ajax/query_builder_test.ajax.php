<?php
/**
 * Query Builder Tester
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

// Load the map from a PList.
$mapListParser = new PropertyList();
$map = $mapListParser->parseFile(
  BF_ROOT . '/acp/definitions/qb_sql_map.plist');

// Build the query from blocks
$query = $BF->in('query');
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
    $sql .= $map[$part];
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
    
    $sql .= $value;
  }
}

// Try executing
try 
{
  $BF->db->text($sql)
             ->execute();
             
  print $BF->db->count;
}
catch(Exception $exception) 
{
  // Error!
  print $exception->getMessage();
}

?>