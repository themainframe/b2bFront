<?php
/** 
 * Security Class
 * Manages the permissions and scope of the current user.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Security extends Base
{
  /**
   * Permissions
   * @var Array
   */
  public $permissions = array();

  /**
   * User Attributes
   * @var Array
   */
  public $attributes = array();

  /** 
   * The current User ID
   * @var integer
   */
  public $UID = -1;

  /**
   * Start the security subsystem.
   * @param BFClass $parent The parent object.
   * @return Security
   */
  public function __construct($parent)
  {
    // Set parent 
    $this->parent = & $parent;
  
    // Summary of session data associative array:
    //  
    // {
    //   'user' => {                                      <----- User Attributes
    //                'name'  =>  string,
    //                'uid'   =>  integer  
    //             },
    //              
    //   'permissions' => {                               <----- User Permissions
    //                       'can_do_foo'  =>  boolean
    //                       ...
    //                    }
    // }
    
    if(!array_key_exists('user', $_SESSION) || !array_key_exists('permissions', $_SESSION))
    {
      // No permissions
      $this->clearPermissions();
      return;
    }
    
    // Copy permissions and user from session object
    foreach($_SESSION['user'] as $key => $value)
    {
      $this->attributes[$key] = $value;
    }
    
    // Copy UID
    $this->UID = intval($this->attributes['uid']);
    
    // Permissions...
    foreach($_SESSION['permissions'] as $key => $value)
    {
      if(substr($key, 0, 3) == 'can')
      {
        $this->permissions[$key] = $value;
      }
    }
    
    // If no permissions are defined, load the Guest permission set
    if(empty($this->permissions))
    {
      // Load default permission set
      $defaultProfileID = 
        $this->parent->config->get('com.b2bfront.security.default-profile', true);
      
      // Load this permission set
      $defaultProfileRow = 
        $this->parent->db->getRow('bf_user_profiles', intval($defaultProfileID));
        
      // Exists?
      if(!$defaultProfileRow)
      {
        $this->parent->log('Security', 'Unable to load default profile. Permissions removed.');
      }
      
      // Apply it
      foreach($defaultProfileRow as $key => $value)
      {
        if(substr($key, 0, 3) == 'can')
        {
          $this->permissions[$key] = $value;
        }
      }
      
      // Write changes
      $this->write();
    }
    
    // Load my locale in to memory
    $this->parent->locale = new Locale($this->attributes['locale_id'], $this->parent);
  }
  
  /**
   * Log in
   * Validate the credentials and update the security object as appropriate.
   * @param string $userName The user name.
   * @param string $password The password as plaintext.
   * @return boolean
   */
  public function logIn($userName, $password)
  {
    // Validate the user
    $passwordSecret = md5(BF_SECRET . $password);
    
    $this->parent->db->select('*', 'bf_users')
                     ->where('name = \'{1}\' AND password = \'{2}\' AND `requires_review` = 0',
                        $userName, $passwordSecret)
                     ->limit(1)
                     ->execute();
                  
    // Check result
    if($this->parent->db->count == 0)
    {
       return false;
    }
    
    // Otherwise, OK
    // Write user attributes
    $user = $this->parent->db->next();
    
    // Store
    foreach($user as $key => $value)
    {
      $this->attributes[$key] = $value;
    }
    
    // Set logged in tag and UID for easy consistent-style access
    $this->attributes['loggedIn'] = true;
    $this->attributes['uid'] = intval($user->id);
    
    // Load the user permissions...
    $this->parent->db->select('*', 'bf_user_profiles')
                     ->where('id = \'{1}\'', $user->profile_id)
                     ->limit(1)
                     ->execute();
                     
    // Profile exists?
    if($this->parent->db->count == 1)
    {
      // Get profile and apply permissions
      $profile = $this->parent->db->next();
      
      // For each permission...
      foreach($profile as $key => $value)
      {
        if(substr($key, 0, 3) == 'can')
        {
          $this->permissions[$key] = $value;
        }
      }
    }
    
    // Synchronise
    $this->write();
    
    return true;
  }
  
  /**
   * Load a profile from the database and apply it.
   * @param integer $profileID The ID of the profile to apply.
   * @return boolean
   */
  public function loadProfile($profileID)
  {
    // Load the user permissions...
    $this->parent->db->select('*', 'bf_user_profiles')
                     ->where('id = \'{1}\'', $profileID)
                     ->limit(1)
                     ->execute();
                     
    // Profile exists?
    if($this->parent->db->count == 1)
    {
      // Get profile and apply permissions
      $profile = $this->parent->db->next();
      
      // For each permission...
      foreach($profile as $key => $value)
      {
        if(substr($key, 0, 3) == 'can')
        {
          $this->permissions[$key] = $value;
        }
      }
    }
    else
    {
      return false;
    }
    
    // Synchronise
    $this->write();
    
    return true;
  }
  
  /**
   * Set an attribute value
   * @param string $key The key for the attribute
   * @param string $value The value of the attribute
   * @return boolean
   */
  public function setAttribute($key, $value)
  {
    $this->attributes[$key] = $value; // Runtime
    $_SESSION['user'][$key] = $value; // Session
  
    return true;
  }
  
  /**
   * Log a dealer ID in
   * @param integer $dealerID The ID of the dealer to log in
   * @return boolean
   */
  public function logInWithID($dealerID)
  {
    // Validate the user
    $password = md5(BF_SECRET . $password);
    
    $this->parent->db->select('*', 'bf_users')
                     ->where('id = \'{1}\'', $dealerID)
                     ->limit(1)
                     ->execute();
                  
    // Check result
    if($this->parent->db->count == 0)
    {
      return false;
    }
    
    // Otherwise, OK
    // Write user attributes
    $user = $this->parent->db->next();
    
    // Store
    foreach($user as $key => $value)
    {
      $this->attributes[$key] = $value;
    }
    
    // Set logged in tag and UID for easy consistent-style access
    $this->attributes['loggedIn'] = true;
    $this->attributes['uid'] = intval($user->id);
    
    // Load the user permissions...
    $this->parent->db->select('*', 'bf_user_profiles')
                     ->where('id = \'{1}\'', $user->profile_id)
                     ->limit(1)
                     ->execute();
                     
    // Profile exists?
    if($this->parent->db->count == 1)
    {
      // Get profile and apply permissions
      $profile = $this->parent->db->next();
      
      // For each permission...
      foreach($profile as $key => $value)
      {
        if(substr($key, 0, 3) == 'can')
        {
          $this->permissions[$key] = $value;
        }
      }
    }
    
    // Synchronise
    $this->write();
    
    return true;
  }

  
  /**
   * Log out
   * Finish this session
   * @return boolean
   */
  public function logOut()
  {
    // Clear permissions
    $this->clearPermissions();
    
    // Unset attributes
    $this->attributes = null;
    
    // Write the session data
    $this->write();
  }
  
  /**
   * Is the user logged in?
   * @return boolean
   */
  public function loggedIn()
  {
    return $this->attr('loggedIn');
  }
  
  /**
   * Verify a permission
   * @param string $permissionName The name of the permission to check.
   * @return boolean True if granted, False if denied.
   */
  public function hasPermission($permissionName)
  {
    // Granted if the permission is defined and is set to true
    return array_key_exists($permissionName, $this->permissions)
            && $this->permissions[$permissionName];
  }  
  
  /**
  * Clear all permissions
  * @return boolean
  */
  private function clearPermissions()
  {
    // Turn off all permissions
    $this->permissions = array();
    
    return true;
  }
  
  /**
   * Write data from the Security object to Session Storage
   * @return boolean
   */
  public function write()
  {
    // Clear existing data
    $_SESSION['user'] = array();
    $_SESSION['permissions'] = array();
  
    // User Attributes first
    foreach($this->attributes as $key => $value)
    {
      $_SESSION['user'][$key] = $value;
    }
    
    // Permissions...
    foreach($this->permissions as $key => $value)
    {
      $_SESSION['permissions'][$key] = $value;
    }
    
    return true;
  }
  
  /** 
   * Determine if the current user can perform the requested action
   * @param string $actionName The name of the action to test
   * @return boolean
   */
  public function can($actionName)
  {
    return (boolean)$this->permissions[$actionName];
  }
  
  /** 
   * Provide an attribute of the current user
   * @param string $attributeName The name of the attribute to get
   * @return boolean
   */
  public function attr($attributeName)
  {
    return $this->attributes[$attributeName];
  }
  
  /**
   * Update the current users location in CCTV
   * @param string $actionName The name of the current action
   * @return boolean
   */
  public function action($actionName)
  {
    // Update CCTV records
    $this->parent->db->insert('bf_cctv', array(
                               'location' => $actionName,
                               'session_id' => session_id(),
                               'timestamp' => time(),
                               'user_id' => $this->UID
                             ))
                     ->orUpdate(array(
                                 'location' => $actionName,
                                 'timestamp' => time(),
                                 'user_id' => $this->UID
                               ))
                     ->execute();
    
    // Add log
    $this->parent->db->insert('bf_user_action_logs', array(
                               'location' => $actionName,
                               'session_id' => session_id(),
                               'timestamp' => time(),
                               'user_id' => $this->UID
                             ))
                     ->execute();
    
    return true;
  }
}

?>