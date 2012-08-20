  <?php 
/**
 * Automated/Scheduled Scripts
 * Script: 5-Minutely Script
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_AUTOMATION") && !defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

//
// Executes every 5 minutes
//
// No events created by this schedule due to the frequency causing high traffic between
// PHP and MySQL when events are created.
//


//
// Statistics Rotation
//

/**
 * Determines if a statistics rotation (SR) should take place now.
 * @param StdClass $requestedTime A row defining the requested time at which SR should take place.
 * @return boolean
 */
function rotateNow($requestedTime)
{
  // Get the current time values
  $timeValues = array();
  $timeValues['minute'] = intval(date('i'));
  $timeValues['hour'] = intval(date('G'));
  $timeValues['weekday'] = intval(date('N'));
  $timeValues['month'] = intval(date('n'));
  $timeValues['monthday'] = intval(date('j'));
  
  // Check values
  foreach($timeValues as $key => $value)
  {
    if($requestedTime->{$key} == -1)
    {
      // Do not validate, any is valid
      continue;
    }
    
    if($requestedTime->{$key} != $value)
    {
      return false;
    }
  }
  
  return true;
}

// Find selected frequency
$selectedFrequencyID = 
  $BF->config->get('com.b2bfront.statistics.frequency', true);
$selectedFrequency = $BF->db->getRow('bf_statistics_periods',
  $selectedFrequencyID);

// Check timing
if(rotateNow($selectedFrequency))
{
  // Create snapshot
  $BF->db->insert('bf_statistic_snapshots', array(
                    'timestamp' => time()
                 ))
         ->execute();
         
  $snapshotID = $BF->db->insertID;

  // Copy all stats values into snapshot data values
  $statistics = $BF->db->query();
  $statistics->select('*', 'bf_statistics')
             ->execute();
                       
  while($statistic = $statistics->next())
  {
    $BF->db->insert('bf_statistic_snapshot_data', array(
                      'value' => $statistic->value,
                      'snapshot_id' => $snapshotID,
                      'statistic_id' => $statistic->id
                   ))   
           ->execute();
  }
  
  // Update all live values to 0.00
  $BF->db->update('bf_statistics', array(
                    'value' => 0.00
                 ))
         ->execute();
         
  // Rotation complete.
}

//
// Scheduled Imports
//

// Process one due scheduled import
$imports = $BF->db->query();
$imports->select('*', 'bf_scheduled_imports')
        ->where('UNIX_TIMESTAMP() > `timestamp` AND `completed` = 0')
        ->limit(1)
        ->execute();

while($schedule = $imports->next())
{
  // Start data loader
  $BF->admin->api('Data')
                ->initiate();
print $schedule->path;
  // Process
  $result = $BF->admin->api('Data')
              ->import($schedule->path,
                       $schedule->create_new_skus == 1,
                       true);      
                                                
  // Check if the result was OK
  if(!$result)
  {
    // Do nothing
    break;
  }
  
  // Get the admin that caused this
  $admin = $BF->db->getRow('bf_admins', $schedule->admin_id);
  
  // No admin found?
  if(!$admin)
  {
    // Do nothing
    break;
  }
  
  if($schedule->notification_sms && $admin->mobile_number)
  {
    // Build an SMS report
    $SMS  = $schedule->name . "\n";
    $SMS .= 'Scheduled Import OK' . "\n\n";
    $SMS .= 'Total: ' . $result['total'] . "\n";
    $SMS .= 'Updated: ' . count($result['updated']) . "\n";
    $SMS .= 'No Action: ' . count($result['noaction']) . "\n";
    $SMS .= 'Created: ' . count($result['created']);
    
    // Send the SMS
    $smsObject = new SMS($BF);
    $smsObject->send($SMS, $admin->mobile_number); 
  }
  
  if($schedule->notification_email)
  {
    // Build an email
    $templateName = $BF->config->get('com.b2bfront.mail.default-admin-template', true);
    $XMLfile = BF_ROOT . '/extensions/mail_templates/' . $templateName . '/template.xml';
    
    // Load XML
    $XMLdata = simplexml_load_file($XMLfile);
    $templateTitle = (string)$XMLdata->description;
    $templateContentName = (string)$XMLdata->content;
    
    // Build path
    $contentPath = BF_ROOT . '/extensions/mail_templates/' . $templateName . 
      '/' . $templateContentName;
      
    // Create email object
    $email = new Email(& $BF);
    $email->loadFromFile($contentPath);
    
    // Add recipient
    $email->addRecipient($admin->email, (array)$admin);
    
    // Set subject
    $subject = 'Scheduled Import results: ' . $schedule->name;
    $email->setSubject($subject);
    
    // Build content
    $content  = 'Hello ' . $admin->full_name . '<br /><br />' . "\n";
    $content .= 'This is a notification that your scheduled import \'' . $schedule->name . '\' has been processed. <br /><br />' . "\n";
    $content .= '<strong>' . count($result['updated']) . '</strong>&nbsp;&nbsp;&nbsp;Items were updated.<br />' . "\n";
    $content .= '<strong>' . count($result['noaction']) . '</strong>&nbsp;&nbsp;&nbsp;Items were not modified because they could not be found.<br />' . "\n";
    $content .= '<strong>' . count($result['created']) . '</strong>&nbsp;&nbsp;&nbsp;Items were created.<br /><br />' . "\n";
    $content .= '<br /><br /><strong>Thanks,<br />' .  $BF->config->get('com.b2bfront.site.title', true) . ' Automated Services.';
    $content .= '</strong>' ."\n\n";
    
    // Set template values
    $email->assign(array(
      'date' => Tools::longDate(),
      'title' => $subject,
      'content' => $content,
      'url' => $BF->config->get('com.b2bfront.site.url', true) 
    ));
    
    // Set from address and name
    $email->from = $BF->config->get('com.b2bfront.mail.from-auto-address', true);
    $email->fromName = $BF->config->get('com.b2bfront.mail.from-auto', true);
    
    // Send
    $email->send();
  }
  
  // Update data
  $BF->db->update('bf_scheduled_imports', array(
                 'completed' => 1
               ))
         ->where('id = \'{1}\'', $schedule->id)
         ->limit(1)
         ->execute();
}

//
// Back-In-Stock Dealer Notifications
//

$dealerNotifications = $BF->db->query();
$dealerNotifications->select('*', 'bf_user_stock_notifications')
                    ->execute();
     
// Collect notification IDs to remove
$oldNotifications = array();
                  
// Process
while($notification = $dealerNotifications->next())
{
  // Check item
  $item = new BOMItem($notification->item_id, & $BF);
  
  // Item exists still?
  if(!$item->attributes)
  {
    $oldNotifications[] = $notification->id;
    continue;
  }
  
  // Check user
  $user = new BOMDealer($notification->user_id, & $BF);
  
  // Dealer exists still?
  if(!$user->attributes)
  {
    $oldNotifications[] = $notification->id;
    continue;
  }
  
  // In stock?
  if($item->stock_free > 0)
  {
    // Notify
    switch($notification->type)
    {
      case 'sms':
      
        // Dealer has mobile number?
        if($user->phone_mobile == '')
        {
          $oldNotifications[] = $notification->id;
          continue;
        }
        
        // Build message
        $message  = 'RE: ' . $item->sku . ' (' . Tools::truncate($item->name) . ')' . "\n\n";
        $message .= 'Item is now in stock at ' . 
          $BF->config->get('com.b2bfront.site.title', true);
        
        // Send an SMS
        $sms = new SMS(& $BF);
        $sms->send($message, $user->phone_mobile);
        
        break;
        
      case 'email':
      
        // Build an email
        $templateName = $BF->config->get('com.b2bfront.mail.default-template', true);
        $XMLfile = BF_ROOT . '/extensions/mail_templates/' . $templateName . '/template.xml';
        
        // Load XML
        $XMLdata = simplexml_load_file($XMLfile);
        $templateTitle = (string)$XMLdata->description;
        $templateContentName = (string)$XMLdata->content;
        
        // Build path
        $contentPath = BF_ROOT . '/extensions/mail_templates/' . $templateName . 
          '/' . $templateContentName;
          
        // Create email object
        $email = new Email(& $BF);
        $email->loadFromFile($contentPath);
        
        // Add recipient
        $email->addRecipient($user->email, (array)$user);
        
        // Set Subject Line
        $subject = $item->sku . ' Back in Stock at ' . 
          $BF->config->get('com.b2bfront.site.title', true);
        $email->setSubject($subject);
      
        // Build content
        $content  = 'Hi ' . $user->description . '<br /><br />' . "\n";
        $content .= 'We are writing to notify you that our product: <br /><br />' . "\n";
        $content .= '<strong>' . $item->sku . '&nbsp;&nbsp;&nbsp;' . $item->name . '</strong><br /><br />' . "\n";
        $content .= 'Is now back in stock!<br />Place an order now to secure yours!<br /><br />' . "\n";
        $content .= 'Visit <a href="' . $BF->config->get('com.b2bfront.site.url', true) . '">';
        $content .= $BF->config->get('com.b2bfront.site.url', true) . '</a> today to place an order!' . "\n";
        $content .= '<br /><br /><strong>Thanks,<br />' .  $BF->config->get('com.b2bfront.site.title', true);
        $content .= '</strong>' ."\n\n";
        
        // Set template values
        $email->assign(array(
          'date' => Tools::longDate(),
          'title' => $subject,
          'content' => $content,
          'url' => $BF->config->get('com.b2bfront.site.url', true) 
        ));
        
        // Send
        $email->send();
      
        break;
    }
    
    $oldNotifications[] = $notification->id;
  }
}
                              
// Remove old notifications
$oldNotificationsCSV = Tools::CSV($oldNotifications);
$BF->db->delete('bf_user_stock_notifications')
       ->whereInHash($oldNotificationsCSV)
       ->execute();
       
//
// Admin chat onlines status timeouts
//

$oldAdmins = $BF->db->query();
$oldAdmins->update('bf_admins', array(
              'online' => 0
            ))
          ->where('(UNIX_TIMESTAMP() - `last_activity_timestamp`) > 1600')
          ->execute();
          
// Delete messages older than 15 minutes
$oldMessages = $BF->db->query();
$oldMessages->delete('bf_chat')
            ->where('(UNIX_TIMESTAMP() - `timestamp`) > 900')
            ->execute();
          
?>
