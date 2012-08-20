<?php
/**
 * Module: Orders
 * Mode: Print View (Processed/Unprocessed/Held)
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

// Load the order to view
$orderID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_orders')
       ->where('id = \'{1}\'', $orderID)
       ->limit(1)
       ->execute();

$orderRow = $BF->db->next();
$orderID = $orderRow->id;
$dealerRow = $BF->db->getRow('bf_users', $orderRow->owner_id);
$bandRow = $BF->db->getRow('bf_user_bands', $dealerRow->band_id);

// Find all notes
$notes = $BF->db->query();
$notes->select('*', 'bf_order_notes')
      ->where('`order_id` = \'{1}\'', $orderID)
      ->order('timestamp', 'asc')
      ->execute();
      
$notesCollection = $notes->assoc();

// Do not output rendered headers.
ob_clean();

// Load default order template
$templateName = $BF->config->get('com.b2bfront.ordering.print-template', true);

// Load the template
if(!file_exists(BF_ROOT . '/extensions/invoice_print_templates/' . $templateName))
{
  $BF->admin->error('Printing Failed',
    'Unable to print because the invoice printing template \'' . $templateName . '\' cannot be found.',
    'com.b2bfront.ordering.print-template');
  exit();
}

// Load the template file
$XMLfile = BF_ROOT . '/extensions/invoice_print_templates/' . $templateName . '/template.xml';
$XMLdata = simplexml_load_file($XMLfile);
$templateTitle = (string)$XMLdata->description;
$templateContentName = (string)$XMLdata->content;

// Build path
$contentPath = BF_ROOT . '/extensions/invoice_print_templates/' . $templateName . 
  '/' . $templateContentName;


// Create a query to find order lines
$query = $BF->db->query();
$query->select('`bf_order_lines`.*, `bf_items`.*, `bf_items`.`id` AS itemid, `bf_items`.`classification_id` AS class,' . 
               'SUM(`bf_order_lines`.`invoice_price_each` * `bf_order_lines`.`quantity`) AS subtotal',
               'bf_order_lines')
      ->text('LEFT OUTER JOIN `bf_items` ON `bf_order_lines`.`item_id` = ' .
           '`bf_items`.`id` ')
      ->where('`bf_order_lines`.`order_id` = \'{1}\'', $orderID)
      ->group('`bf_order_lines`.`id`')
      ->order('class', 'desc')
      ->execute();
      
// Select tags for all items
$tags = $BF->db->query();
$tags->select('*', 'bf_item_tags')
     ->execute();
     
// Pull all item tag applications
$tagAssignments = $BF->db->query();
$tagAssignments->select('*', 'bf_item_tag_applications')
               ->execute();

// Associate assignments on ID
$tagAssigns = $tagAssignments->assoc();
     
// Associate on ID
$tagsAssoc = $tags->assoc();
 
// Associate orders on ID     
$orderLines = $query->assoc();

// Default: no tags
foreach($orderLines as $id => $line)
{
  $orderLines[$id]['tags'] = '';
}

// Join tags on
foreach($orderLines as $id => $orderLine)
{
  // Join on tags
  foreach($tagAssigns as $assignment)
  {
    if($orderLine['itemid'] == $assignment['item_id'])
    {
      // This item has this tag
      // Create string element
      if(!isset($orderLine['tags']))
      {
        $orderLine['tags'] = '&nbsp;';
      }

      // Append
      $orderLines[$id]['tags'] .= '<img src="' . $tagsAssoc[$assignment['item_tag_id']]['icon_path'] . 
        '" width="20" /> ';
    }
  } 
}

// Build a view
$view = new View($contentPath, $BF);
$view->assign(array(
  'lines' => $orderLines,
  'submitted' => Tools::shortDate($orderRow->timestamp),
  'processed' => $orderRow->processed,
  'email' => $dealerRow->email,
  'phone' => $dealerRow->phone_landline,
  'account' => $dealerRow->account_code,
  'name' => $dealerRow->description,
  'username' => $dealerRow->name,
  'lines_count' => count($orderLines),
  'notes' => $notesCollection,
  'notes_count' => count($notesCollection),
  'mecard' => 
  'MECARD:N:' . $dealerRow->description . ';NOTE:' . 
    $dealerRow->account_code . ';TEL:' . $dealerRow->phone_landline . 
    ';EMAIL:' . $dealerRow->email . ';;',
  'processed_date' => Tools::shortDate($orderRow->processed_timestamp),
  'order_number' => $BF->config->get('com.b2bfront.ordering.order-id-prefix', true) . 
    $orderRow->id
));

// Render
$view->render();

?>

  <script type="text/javascript">
  
    print();
  
  </script>

<?php

print (string)$view;

?>