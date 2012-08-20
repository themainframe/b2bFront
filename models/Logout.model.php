<?php
/** 
 * Model: Logout
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Logout extends RootModel
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
    $this->parent->security->action('Logged Out');
    
    // Statistics bump
    $this->parent->stats->increment('com.b2bfront.stats.users.logouts', 1);  

    // Close window
    @setcookie('window', 'closed');

    // Notify admins
    $this->parent->notifier->send('dealer_login', 
      $this->parent->security->attributes['description'],
      'Logged out.', false, 'status-offline.png');
  
    // Set this model's title and tab
    $this->addValue('title', 'BFClass Sports - Log Out');
    $this->addValue('tab_account', 'selected');
  
    // Log out
    $this->parent->security->logOut();  
    
    // Redirect
    $this->parent->go(Tools::getModifiedURL(array('option' => 'account')));
      
    return true;
  }
}  
?>