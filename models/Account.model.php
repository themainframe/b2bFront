<?php
/** 
 * Model: Account
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Account extends RootModel
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
    $this->parent->security->action('Account');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - My Account');
    $this->addValue('tab_account', 'selected');

    // Add last update timestamp
    $this->addValue('last_update',
      date('F j, Y, g:i a', $this->parent->config->get('com.b2bfront.site.last-data-import', true)));
  
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