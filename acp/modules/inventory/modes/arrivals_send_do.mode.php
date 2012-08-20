<?php
/**
 * Module: Inventory
 * Mode: Arrivals Send Do
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

// Collect all notifications
$query = $BF->db->query();
$query->text('SELECT *,`bf_items`.`id` AS itemid, `bf_items`.`name` AS name ' . 
             'FROM `bf_items`,  `bf_stock_replenishments` WHERE `bf_items`.`id`' . 
             ' = `bf_stock_replenishments`.`item_id`')
      ->execute();
      
// Get the timeout period from config
$timeout = $BF->config->get(
  'com.b2bfront.crm.purchase-history-length', true) * 86400;
      
// Build a collection of users to email
$users = array();
      
// Find all
while($arrival = $query->next())
{
  // Get the item and check it is in stock still
  $itemTest = $BF->db->getRow('bf_items', $arrival->item_id);
  
  // Check
  if(!$itemTest || $itemTest->stock_free < 1)
  {
    // Not in stock or doesn't exist.
    continue;
  }

  // Look up how many dealers have ordered this recently
  // Find orders
  $orders = $BF->db->query();
  $orders->select('*', 'bf_orders')
         ->where('(UNIX_TIMESTAMP() - `timestamp`) < {1}', $timeout)
         ->execute();
         
  while($order = $orders->next())
  {
    // Find lines
    $lines = $BF->db->query();
    $lines->select('*', 'bf_order_lines')
          ->where('`order_id` = \'{1}\' AND `item_id` = \'{2}\'', 
                  $order->id, $arrival->itemid)
          ->execute();
        
    // Make sure each user only gets 1 line for each item
    if($lines->count > 0 && !in_array(intval($arrival->itemid), $users[$order->owner_id]))
    {
      $users[$order->owner_id][] = intval($arrival->itemid);
    }    
  }
}


// Remove arrivals
$removeArrivals = $BF->db->query();
$removeArrivals->delete('bf_stock_replenishments')
               ->execute();

//
// For each user, generate mail and send
//

// Count mailings
$mailCount = 0;

foreach($users as $UID => $notification)
{
  $userData = $BF->db->getRow('bf_users', $UID);
  
  //  Valid?       Excluded from mailshots?
  if(!$userData || $userData->include_in_bulk_mailings == 0)
  {
    continue;
  }
  
  // Generate mailshot
  // Create email object
  $email = new Email(& $BF);
  
  // Use the default template
  $templateName = $BF->config->get('com.b2bfront.mail.default-template', true);
  
  // Load mail template without path traversal
  $mailTemplateName = str_replace('..', '', $templateName);
  
  // Load file
  $XMLfile = BF_ROOT . '/extensions/mail_templates/' . $mailTemplateName . '/template.xml';
  $XMLdata = simplexml_load_file($XMLfile);
  $templateTitle = (string)$XMLdata->description;
  $templateContentName = (string)$XMLdata->content;
  
  // Build path
  $contentPath = BF_ROOT . '/extensions/mail_templates/' . $mailTemplateName . 
    '/' . $templateContentName;
  
  $email->loadFromFile($contentPath);
  
  // Generate subject
  $subject = count($notification) . ' Item' . Tools::plural(count($notification)) . 
    ' back in stock at ' . $BF->config->get('com.b2bfront.site.title', true) . '!';
  
  // Set subject
  $email->setSubject($subject);
    
  // Create content body
  $content  = '<strong>Hello, ' . $userData->description . '</strong>';
  $content .= '<br /><br />';
  $content .= 'Our records show that you have purchased
               the following item' . Tools::plural(count($notification)) . 
               ' in the past.<br />
               We are pleased to inform you that ' . 
               (count($notification) > 1 ? 'these items are' : 'this item is') . '
               now back in stock.<br /><br /><br />';
  $content .= '<table width="76%">';
               
  foreach($notification as $itemID)
  {
    // Get item info
    $itemData = $BF->db->getRow('bf_items', $itemID);
    if(!$itemData)
    {
      // Doesn't exist any more.
      continue;
    }
  
    $content .= '<tr>';
    $content .= '  <td width="100"><strong>' . $itemData->sku . '</strong></td>';
    $content .= '  <td>' . $itemData->name . '</td>';
    $content .= '  <td width="100"><strong style="color: #34c121">' . ($itemData->stock_free > 100 ? '100+' : 
                $itemData->stock_free) . ' In Stock</strong></td>';
    $content .= '  <td><a style="color: #00f; text-decoration: underline"' . 
                ' href="' . $BF->config->get('com.b2bfront.site.url', 'true') .
                '/?option=item&id=' . $itemData->id . '">Order Now</a></td>';
    $content .= '</tr>';             
  }
  
  $content .= '</table>';
  $content .= '<br /><br /><br /><p>We hope you find this information helpful!<br /><br />
               Regards,<br /><strong>' . $BF->config->get('com.b2bfront.site.title', true) .
               ' Web Services</strong></p>';
  
  // Set global template values
  $email->assign(array(
    'date' => Tools::longDate(),
    'title' => $subject,
    'content' => $content,
    'url' => $BF->config->get('com.b2bfront.site.url', true),
    'unsubscribe' => $BF->config->get('com.b2bfront.site.url', true) . '/unsubscribe/'
  ));

  // Add recipient
  $email->addRecipient($userData->email, (array)$userData);

  // Send
  $email->send();
  $mailCount ++;
}

// Finished - show actions

?>

<h1>Notifications Sent</h1>
<br />

<div class="panel">
  <div class="contents">
    
    
    <h3><?php print $mailCount; ?> Back In Stock Notification<?php print Tools::plural($mailCount); ?>
       Sent</h3>
    
    <p>
       <?php print $mailCount; ?> Back In Stock Notification Email<?php print Tools::plural($mailCount); ?>
        was dispatched to dealers.<br />
      The Back In Stock alerts list has been automatically cleared.
    
      <br /><br />
      <a href="./?act=inventory">Back to Inventory</a>
    </p>
    
  </div>
</div>