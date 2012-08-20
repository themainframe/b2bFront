<?php
/** 
 * Model: Login
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Login extends RootModel
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
    $this->parent->security->action('Log In');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - My Account');
    $this->addValue('tab_account', 'selected');
  
    // Get username and password
    $username = $this->parent->in('f_username');
    $password = $this->parent->in('f_password');
    
    if($username == '' && $password == '')
    {
      // Non-standard form submission
      $username = $this->parent->in('username');
      $password = $this->parent->in('password');
    }
  
    // Try to log in
    $result = $this->parent->security->logIn($username, $password);
    
    // Success or failure?
    if(!$result)
    {
      $this->parent->go('./?option=gateway&error=1');
      
      // Bad logins + 1
      $this->parent->stats->increment('com.b2bfront.stats.users.bad-logins', 1);
      
      // Stop rendering
      return false;    
    }    
    else
    {
      // Notify admins
      $this->parent->notifier->send('dealer_login', 
        $this->parent->security->attributes['description'],
        'Logged in.', false, 'status.png');
    
      // Statistics bump
      $this->parent->stats->increment('com.b2bfront.stats.users.logins', 1);      
    
      // Redirect to account
      if($this->parent->in('f_target_action') != 'login')
      {
        $this->parent->go(Tools::getModifiedURL(  
          array('option' => $this->parent->in('f_target_action'),
                'id' => $this->parent->inInteger('f_target_id'))));
      }
      else
      {
        $this->parent->go(Tools::getModifiedURL(array('option' => 'home')));
      }
    }
    
    return true;
  }
}  
?>