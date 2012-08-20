<?php
/** 
 * Model: Item Notification Request Form
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ItemRequestNotification extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    // Update CCTV
    $this->parent->security->action('Request a Notification');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Request a Notification');
    $this->addValue('tab_home', 'selected');
  
    // Logged in?
    if(!$this->parent->security->loggedIn())
    {
      $this->parent->loadView('login');
   
      return false;
    }
    
    
    // Invalid ID?
    if(!$this->parent->inInteger('id'))
    {
      // Stop rendering the model
      $this->parent->go('./?');
      return false;
    }
    
    // Get item
    $item = new BOMItem($this->parent->inInteger('id'), & $this->parent);
    $this->addValue('itemSKU', $item->sku);
 
    // Boolean
    $SMSEnabled = $this->parent->config->get('com.b2bfront.sms.enable', true) && 
      $this->parent->config->get('com.b2bfront.crm.allow-sms-notify', true);
    
    // SMS possible?
    $this->addValue('smsEnabled',
      ($SMSEnabled ? '1' : '0')
    );
    

    // SMS number stored?
    $this->addValue('haveNumber',
      $this->parent->security->attr('phone_mobile') != '' ? '1' : '0');
    
    // Always make ID available
    $this->addValue('id', $this->parent->inInteger('id'));

    // Showtime?
    if($this->parent->inInteger('done') == 1)
    {
      // Set done marker
      $this->addValue('done', '1');
      
      // Get the type
      $notificationType = $this->parent->in('type');
      
      // Valid?
      if($notificationType != 'email' && $notificationType != 'sms')
      {
        // Default
        $notificationType = 'email';
      }
      
      // Insert number if requested
      if($this->parent->in('mob_num'))
      {
        $mobileNumber = substr($this->parent->in('mob_num'), 0, 12);
        
        $this->parent->db->update('bf_users', array(
                                   'phone_mobile' => $mobileNumber
                                 ))
                         ->where('`id` = \'{1}\'', $this->parent->security->UID)
                         ->limit(1)
                         ->execute();
                         
        // Session update
        $this->parent->security->setAttribute('phone_mobile', $mobileNumber);
      }
      
      // SMS possible
      // User should never be presented with this option if not possible.
      if($notificationType == 'sms')
      {
        if($this->parent->security->attr('phone_mobile') == '' &&
          !$this->parent->in('mob_num'))
        {
          // Not on file - use email instead
          $notificationType = 'email';
        }
      }
      
      try
      {
        // Add notification
        $this->parent->db->insert('bf_user_stock_notifications', array(
                                    'user_id' => $this->parent->security->UID,
                                    'item_id' => $this->parent->inInteger('id'),
                                    'type' => $notificationType
                                 ))
                         ->execute();
      
      }
      catch(Exception $exception)
      {
        // Do nothing - duplicate request.
      }
    }
    
    return true;
  }
}  
?>