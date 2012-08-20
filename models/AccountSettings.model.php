<?php
/** 
 * Model: Account Settings
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class AccountSettings extends RootModel
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
    $this->parent->security->action('Account Settings');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - My Account Settings');
    $this->addValue('tab_account', 'selected');
    
    // Settings
    if($this->parent->in('save'))
    {
      // Enable hidden prices?
      if($this->parent->inInteger('hidePrices') == 1)
      {
        // Change profile
        $this->parent->security->loadProfile(12);
        $this->parent->loadView('home');
        
        // Confirm
        $this->parent->loadView('account');
        $this->addValue('notification', 'RRPs Only mode activated until logout.');
      }
      else
      {
        $this->parent->loadView('account');
        $this->addValue('notification', 'Account settings saved.');
      }
    }
  
    // Logged in?
    if(!$this->parent->security->loggedIn())
    {
      $this->parent->loadView('login');
   
      return false;
    }
    
    return true;
  }
}  
?>