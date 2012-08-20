<?php
/** 
 * Notifier Class
 * Pushes notifications to subscribed administrators.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Notifier extends Base
{
  /**
   * Handle the named event and fire any notifications that are required.
   * @param string $eventName The name of the event that took place.
   * @param string $title A title for any generated notifications.
   * @param string $shortMessage Optionally a Short message to include with any generated notifications.
   * @param string $data Optionally a longer message to include with any generated notifications.
   * @param string $icon Optionally an Icon to include for In-ACP notifications.
   * @param string $emailTemplate Optionally the email template to use.
   * @return boolean
   */
  public function send($eventName, $title, $shortMessage = '', $data = '', $icon = '', $emailTemplate = '')
  {
    // Find subscribed admins
    $subscribedAdmins = $this->parent->db->query();
    $subscribedAdmins->select('*', 'bf_admins')
                     ->where('`notification_{1}` <> 0', $eventName)
                     ->execute();
                     
    // For each admin, perform a notification
    while($admin = $subscribedAdmins->next())
    {
      //
      // Level 1+
      // Implicit In-ACP Notification
      //
      
      $newACPNotification = $this->parent->db->query();
      $newACPNotification->insert('bf_admin_notifications', array(
                                    'title' => Tools::truncate($title, 25),
                                    'content' => $shortMessage,
                                    'icon_url' => ($icon ? $icon : 'information.png'),
                                    'timestamp' => time(),
                                    'admin_id' => $admin->id
                                 ))
                         ->execute();
                         
      $this->parent->log('Notifier', 'Sending notification (Level 1+ - ACP) to: ' . $admin->full_name);
          
      if(isset($admin->{'notification_' . $eventName}) && $admin->{'notification_' . $eventName} >= 2)
      {
        //
        // Level 2+
        // Email
        //

        // Build an email
        if($emailTemplate == '')
        {
          $templateName = $this->parent->config->get('com.b2bfront.mail.default-template', true);
        }
        else
        {
          $templateName = $emailTemplate;
        }
        
        $XMLfile = BF_ROOT . '/extensions/mail_templates/' . $templateName . '/template.xml';
        
        // Possible?
        if(!Tools::exists($XMLfile))
        {
          // Can't find mail template
          $this->parent->log('Notifier: Cannot find mail template \'' . 
            $templateName . '\'');
          continue;
        }
        
        // Load XML
        $XMLdata = simplexml_load_file($XMLfile);
        $templateTitle = (string)$XMLdata->description;
        $templateContentName = (string)$XMLdata->content;
        
        // Build path
        $contentPath = BF_ROOT . '/extensions/mail_templates/' . $templateName . 
          '/' . $templateContentName;
          
        // Create email object
        $email = new Email($this->parent);
        $email->loadFromFile($contentPath);
        
        // Add recipient to the email
        $email->addRecipient($admin->email, (array)$admin);
        
        // Set Subject Line
        $email->setSubject($shortMessage);
      
        // Build content
        $content  = '
        
        Hello ' . $admin->full_name . '
        <br /><br />
        
        ' . $data . '
        
        <br /><br /><br />
        <strong>
          Thanks,<br />
          ' . $this->parent->config->get('com.b2bfront.site.title', true) . ' Automated Services.
        </strong>
        ';
        
        // Set template values
        $email->assign(array(
          'date' => Tools::longDate(),
          'title' => $subject,
          'content' => $content,
          'url' => $this->parent->config->get('com.b2bfront.site.url', true) 
        ));
        
        // Log
        $this->parent->log('Notifier', 'Sending notification (Level 2+ - Email) to: ' . $admin->email);
        
        // Send
        $email->send();
      }
      
      if(isset($admin->{'notification_' . $eventName}) && $admin->{'notification_' . $eventName} >= 3)
      {
        //
        // Level 3
        // SMS
        //
        
        // Build an SMS report
        $SMS  = $this->parent->config->get('com.b2bfront.site.title', true) . "\n\n";
        $SMS .= $shortMessage;
        
        // Send the SMS
        $smsObject = new SMS($this->parent);
        $smsObject->send($SMS, $admin->mobile_number);     
        
        // Log
        $this->parent->log('Notifier', 'Sending notification (Level 3 - SMS) to: ' . $admin->full_name);
        
      }
      
    }
    
    return true;
  }
}
?>