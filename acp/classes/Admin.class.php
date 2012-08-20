<?php
/**
 * Admin Class
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Admin extends Base
{
  /**
   * The AID of this admin
   * @var integer
   */
  public $AID = -1;

  /**
   * A collection of permissions for the current admin
   * @var array
   */
  public $permissions = array();
  
  /**
   * A collection of information about the current admin
   * @var array
   */
  public $adminInformation = array(); 
   
  /**
   * A boolean value to decide if the user may access the admin interface
   * @var boolean
   */
  public $isAdmin = false;
  
  /**
   * A boolean value indicating that the current administrator is a supervisor
   * @var boolean
   */
  public $isSupervisor = false;
  
  /**
   * The directory from which API files should be preloaded
   * @var string
   */
  private $APIDirectory = '/acp/classes/apis/';
  
  /**
   * APIs loaded by the Admin class
   * @var array
   */
  private $APIs = array();
  
  /**
   * Initiate the admin session
   * @param BFClass* $parent A referance to the parent object.
   * @return boolean
   */
  public function __construct($parent)
  {
    // Assign parent
    $this->parent = $parent;
    
    // Also assign my DB
    $this->db = & $this->parent->db;
    
    // Load any APIs
    $this->loadAPIs();
    
    // Check scheduling
    $this->checkScheduling();
    
    // Check if the user is an administrator
    if(isset($_SESSION['admin']))
    {
      // Set admin flag
      $this->isAdmin = true;
    
      // Load information
      $this->sync();
    }
    else
    {
      // Do no more loading
      return;
    }
  }
  
  /**
   * Provide access via a method call to loaded APIs
   * If the API is not loaded, throw an exception
   * @param string $APIName The name of the API
   * @return object
   */
  public function api($APIName)
  {
    if(array_key_exists($APIName, $this->APIs))
    {
      return $this->APIs[$APIName];
    }
    else
    {
      throw new Exception('Accessing unregistered API: ' . $APIName);
    }
  }
  
  /**
   * Load APIs from the API directory
   * @return boolean True on success, False on failure.
   */
  public function loadAPIs()
  {
    // Test directory
    if(!Tools::exists($this->APIDirectory))
    {
      throw new Exception('Cannot find API directory: ' . $this->APIDirectory);
    }
  
    // Open API Directory
    $APIDirectoryHandle = opendir(BF_ROOT . '/' . $this->APIDirectory);
    
    // Failed?
    if(!$APIDirectoryHandle)
    {
      throw new Exception('Denied permission to access API directory: ' . '/acp/classes/apis/');
    }
    
    // Load each API class
    while(($APIFilename = readdir($APIDirectoryHandle)) !== false)
    {
      // Is this a class file
      $APIFilenameParts = explode('.', $APIFilename);

      if(count($APIFilenameParts) != 3 || $APIFilenameParts[1] != 'class' || 
        $APIFilenameParts[2] != 'php')
      {
        continue;
      }

      // Load file
      $this->loadAPIClassFile($APIFilename);
    }
  }
  
  /**
   * Load a single class file
   * @param string $APIFilename The filename to load
   * @return boolean True on success, False on failure
   */
  private function loadAPIClassFile($APIFilename)
  {
    // Does the file exist?
    if(!Tools::exists($this->APIDirectory . '/' . $APIFilename))
    {
      return false;
    }
    
    // Obtain a class name to load
    $className = ucfirst(Tools::valueAt(explode('.', $APIFilename), 0));
     
    // Valid class?
    if($className == '')
    {
      return false;
    }
    
    // Include the file
    include BF_ROOT . '/' . $this->APIDirectory . '/' . $APIFilename;
    
    // Loaded?
    if(class_exists($className))
    {
      // New Object with BFClass and DB class access
      $this->APIs[$className] = new $className($this->parent, $this->parent->db);
    }
    
    return true;
  }
  
  /**
   * Log in
   * @param string $user The user name.
   * @param string $passwordPlaintext The password in plaintext.
   * @param boolean $silent Optionally do not show the login to other admins.
   * @return boolean True on success, False on failure.
   */
  public function logIn($user, $passwordPlaintext, $silent = false)
  {
    // Query for admin
    $password = md5(BF_SECRET . $passwordPlaintext);
    $this->parent->db->select('*', 'bf_admins')
                     ->where("`name` = '{1}' AND `password` = '{2}'", $user, $password)
                     ->limit(1)
                     ->execute();
    
    if(!$silent)
    {
      // Staff Logins + 1
      $this->parent->stats->increment('com.b2bfront.stats.admins.logins', 1);
    }
    
    // Success
    if($this->parent->db->count == 1)
    {
      // Found a suitable user, authenticate them
      $_SESSION['admin'] = array();
      $this->isAdmin = true;

      // Load private data
      $adminRow = $this->parent->db->next();
      
      // Store admin row information
      foreach($adminRow as $key => $value)
      {
        $_SESSION['admin'][$key] = $value;
      }
      
      // Supervisor status
      $this->isSupervisor = ($adminRow->supervisor == 1);

      // Search for the group for this administrator
      $this->parent->db->select('*', 'bf_admin_profiles')
                       ->where("`id` = {$adminRow->profile_id}")
                       ->limit(1)
                       ->execute();
    
      // If the user is not in a group, or their group does not exist,
      // then no permissions should be granted.
      if($this->parent->db->count == 1)
      {
        // Apply permissions
        $profile = $this->parent->db->next();
        
        // Load each column as a permission
        foreach($profile as $key => $value)
        {
          if(substr($key, 0, 4) == "can_")
          {
            $_SESSION['admin'][$key] = ($value == '1' ? true : false);
          }
        }
      }
      else
      {
        return false;
      }
      
      // Sync session with object
      $this->sync();
      
      // Can this user log in?
      if(!$this->can('login'))
      {
        if($silent)
        {
          exit();
        }
      
        $this->logOut();
        $this->parent->go('./?m=0');
        
        // Stop
        exit();
      }
      
      // Can the user man live chat?
      if(!$this->can('chat'))
      {
        // Set me as offline instantly
        $this->setInfo('online', 0);
        
        // Update DB too
        $setOffline = $this->db->query();
        $setOffline->update('bf_admins', array(
                      'online' => 0
                    ))
                   ->where('`id` = \'{1}\'', $this->AID)
                   ->limit(1)
                   ->execute();
      }
      
      if(!$silent)
      {
        // Notify others of the login
        $this->sendBulkNotification($this->getInfo('full_name'),
          'just logged in to the ACP.', true, 'status.png');
      }
      
      // Set last login time
      $this->parent->db->update('bf_admins', array(
                                  'last_login_timestamp' => time()
                               ))
                       ->where('`id` = \'{1}\'', $this->AID)
                       ->limit(1)
                       ->execute();
      
    }
    else
    {
      // Log event
      $this->parent->logEvent('ACP Security',
                              'There was an attempt to log in to the ACP with: ' . $user . 
                              '<br />The attempt did not authenticate successfully.<br /><br />' . 
                              'The ACP is secure.<br /><br />' . 
                              'The attempt was made from: ' . $_SERVER['REMOTE_ADDR'],
                              3);
    
      return false;
    }
    
    return true;
  }

  /**
   * Log Out
   * @return boolean
   */
  public function logOut()
  {
    // Set me as offline
    $this->parent->db->update('bf_admins', array(
                          'online' => 0
                       ))
                     ->where('`id` = \'{1}\'', $this->AID)
                     ->limit(1)
                     ->execute();  
  
    // Clear session
    unset($_SESSION['admin']);                
    
    // Clear permissions
    unset($this->permissions);
    unset($this->adminInformation);
    
    return true;
  }
  
  /**
   * Load a module menu bar
   * @param string $moduleName The name of the module
   * @return boolean True on success, False on failure
   */
  public function loadModuleMenu($moduleName)
  {
    if(file_exists(BF_ROOT . "/acp/modules/{$moduleName}/")
       && file_exists(BF_ROOT . "/acp/modules/{$moduleName}/{$moduleName}.menu.php"))
    {
      // Include the menu
      include BF_ROOT . "/acp/modules/{$moduleName}/{$moduleName}.menu.php";
      
      return true;
    }
    else
    {
      return false;
    }
  } 

  /**
   * Load a module logic file
   * @param string $moduleName The name of the module
   * @return boolean True on success, False on failure
   */
  public function loadModuleLogic($moduleName)
  {
    if(file_exists(BF_ROOT . "/acp/modules/{$moduleName}/")
       && file_exists(BF_ROOT . "/acp/modules/{$moduleName}/{$moduleName}.logic.php"))
    {
      // Include the logic file
      include BF_ROOT . "/acp/modules/{$moduleName}/{$moduleName}.logic.php";
      
      return true;
    }
    else
    {
      return false;
    }
  } 

  /**
   * Load a module file
   * @param string $moduleName The name of the module
   * @return boolean True on success, False on failure
   */
  public function loadModule($moduleName)
  {
    if(file_exists(BF_ROOT . "/acp/modules/{$moduleName}/")
       && file_exists(BF_ROOT . "/acp/modules/{$moduleName}/{$moduleName}.module.php"))
    {
      // Include the module
      include BF_ROOT . "/acp/modules/{$moduleName}/{$moduleName}.module.php";
      
      return true;
    }
    else
    {
      // Log a missing module
      $this->parent->log('Missing file', "/acp/modules/{$moduleName}/{$moduleName}.module.php");
      
      return false;
    }
  }
  
  /**
   * Check that scheduling is functioning
   * @return boolean
   */
  private function checkScheduling()
  {
    // Load timestamp file
    $timestampFile = Tools::getText(BF_ROOT . '/automated/last-scheduled-event');
    
    // Check timestamp
    if(!$timestampFile || (time() - $timestampFile) > 360)
    {
      // Scheduling issue
      $this->sendBulkNotification('Scheduling Failure', 'Scheduled tasks are not running on time.<br />Please <a href="./?act=system&mode=config_scheduling_setup">reset</a> the scheduling system.', false,
        'exclamation.png', true, 'scheduled-task-failure');
    }
  }
  
  /**
   * Get information about the admin
   * @param string $informationKey The key for the information item to retreive
   * @param boolean $resync Optionally Resync the information from the database. Default no.
   * @return string
   */
  public function getInfo($informationKey, $resync = false)
  {
    // Get information
    if($resync)
    {
      $adminRow = $this->db->getRow('bf_admins', $this->AID, 'id', true);
      if(isset($adminRow->{$informationKey}))
      {
        $this->adminInformation[$informationKey] = $adminRow->{$informationKey};
      }
    }
  
    if(isset($this->adminInformation[$informationKey]))
    {
      return $this->adminInformation[$informationKey];
    }

    return '';
  }
  
  /**
   * Set information about the admin 
   * Does not modify DB, only session and cache
   * @param string $informationKey The key of the information to update
   * @param string $value The value to set
   * @return boolean
   */
  public function setInfo($informationKey, $value)
  {
    // Update session and cache for this execution...
    $_SESSION['admin'][$informationKey] = $value;
    $this->adminInformation[$informationKey] = $value;
  
    return true;
  }
  
  /**
   * Load information and permissions from the session into the object
   * @return boolean True on success, False on failure
   */
  private function sync()
  {
    // Load permissions and admin information
    foreach($_SESSION['admin'] as $key => $value)
    {
      if(substr($key, 0, 4) == "can_")
      {
        // Set permission
        $this->permissions[substr($key, 4)] = $value;
      }
      else
      {
        // Set admin information
        $this->adminInformation[$key] = $value;
        
        // ID?
        if($key == 'id')
        {
          $this->AID = $value;
        }
      }
    }
  
    // Supervisor status
    $this->isSupervisor = ($_SESSION['admin']['supervisor'] == 1);
    
    return true;
  }
  
  /**
   * Shorthand to check the administrator holds a specific permission
   * @param string $permissionName The permission name, without 'can_' or 'can'.
   * @return boolean True if permission held, False if not
   */
  public function can($permissionName)
  {
    if(isset($this->permissions[$permissionName]) && $this->permissions[$permissionName] == true)
    {
      return true;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Publish a notification to me
   * @param string $title The notification title
   * @param string $content The notification text
   * @param string $icon Optionally an icon to use. Default an "information" symbol.
   * @param boolean $logged Optionally display this item in the log.  Default false.
   * @param string $relevance Optionally the relevance of the message.  Avoids repeated notifications.
   * @return boolean
   */
  public function notifyMe($title, $content, $icon = 'information.png', $logged = false,
    $relevance = '')
  {
    // Get a boolean value for logged
    $logged = ($logged ? '1' : '0');
    
    try
    {
      // Publish
      $this->db->insert('bf_admin_notifications', array_merge(
                         array(
                           'title' => $title,
                           'content' => $content,
                           'icon_url' => $icon,
                           'timestamp' => time(),
                           'admin_id' => $this->AID,
                           'logged' => $logged
                         ),
                         ($relevance == '' ? array() : array(
                           'relevance' => $relevance
                         ))
                       ))
               ->execute();
     }
     catch(Exception $exception)
     {
       // Repeated relevance value - ignored.
     }
               
    return true;
  }

  
  /**
   * Publish a notification message to an administrator by ID.
   * @param integer $admin The target admin ID.
   * @param string $title The notification title
   * @param string $content The notification text
   * @param string $icon Optionally an icon to use. Default an "information" symbol.
   * @param boolean $logged Optionally display this item in the log.  Default false.
   * @param boolean $persist Optionally make the notification window (if any) remain until clicked.
   * @param string $relevance Optionally the relevance of the message.  Avoids repeated notifications.
   * @return boolean
   */
  public function sendNotification($admin, $title, $content, $icon = 'information.png',
    $logged = false, $persist = false, $relevance = '')
  {
    // Get a boolean value for logged + persist
    $logged = ($logged ? '1' : '0');  
    $persist = ($persist ? '1' : '0');  

    try
    {
      // Publish
      $this->db->insert('bf_admin_notifications', array_merge(
                         array(
                           'title' => $title,
                           'content' => $content,
                           'icon_url' => $icon,
                           'timestamp' => time(),
                           'admin_id' => $admin,
                           'logged' => $logged
                         ),
                         ($relevance == '' ? array() : array(
                           'relevance' => $relevance
                         ))
                       ))
               ->execute();
     }
     catch(Exception $exception)
     {
       // Repeated relevance value - ignored.
     }
      
    return true;
  }

  /**
   * Publish a notification message to all administrators.
   * @param string $title The notification title
   * @param string $content The notification text
   * @param boolean $excludeMe Optionally exclude the current user.  Default false.
   * @param string $icon Optionally an icon to use. Default an "information" symbol.
   * @param boolean $logged Optionally display these items in the log.  Default false.
   * @param string $relevance Optionally the relevance of the message.  Avoids repeated notifications.
   * @return boolean
   */
  public function sendBulkNotification($title, $content, $excludeMe = false,
    $icon = 'information.png', $logged = false, $relevance = '')
  {
    // Find admin IDs
    $this->db->select('*', 'bf_admins')
             ->execute();
             
    // Get a boolean value for logged
    $logged = ($logged ? '1' : '0');
    
    while($adminUser = $this->db->next())
    {
    
      if($adminUser->id == $this->AID && $excludeMe)
      {
        continue;
      }
      
      try
      {
        $this->db->insert('bf_admin_notifications', array_merge(
                           array(
                             'title' => $title,
                             'content' => $content,
                             'icon_url' => $icon,
                             'timestamp' => time(),
                             'admin_id' => $adminUser->id,
                             'logged' => $logged
                           ),
                           ($relevance == '' ? array() : array(
                             'relevance' => $relevance
                           ))
                         ))
                 ->execute();
       }
       catch(Exception $exception)
       {
         // Repeated relevance value - ignored.
       }
    }
    
    return true;
  }
  
  /**
   * Pack up all field (f_*) values and redirect.
   * @param string $location The location to redirect to.
   * @param string $field Optionally The field that caused an error (if any).
   * @param string $message Optionally a message to show after the redirection.
   * @return boolean
   */
  public function packAndRedirect($location, $field = '', $message = '')
  {
    // Begin collecting fields
    $fields = array();
  
    foreach($this->parent->inputs as $inputKey => $inputValue)
    {
      if(substr($inputKey, 0, 2) == 'f_')
      {
        $fields[$inputKey] = $inputValue;
      }
    }
    
    // Build a query string
    $queryString = Tools::queryString($fields);
    
    // Was there an error to show?
    if($field)
    {
      $queryString .= '&e_f_' . $field . '=true&message=' . urlencode($message);
    }
    
    // Navigate to the URL specified
    header('Location: ' . $location . '&' . $queryString);
    
    return true;
  }
  
  /**
   * Provide the "Turn off Tips" HTML block
   * Checks if tips are enabled first. Returns empty string if not.
   * @return string
   */
  public function turnOffTipsHint()
  {
    if(!$this->parent->config->get('com.b2bfront.acp.tips', true))
    {
      return '';
    }
    
    return 'You can disable this message and others like it by turning off ' .
           '<strong>Tips</strong> in the <a href="./?act=system&mode=config' . 
           '_modify&domain=1" title="Config" class="new" target="_blank">AC' . 
           'P Configuration</a> view.';
  }
  
  /**
   * Provide the "Need to be Supervisor" HTML Block
   * @return string
   */
  public function notSupervisor()
  {
    return '    <h1>Permission Denied</h1>
                <br />
                <p>
                  You do not have permission to use this section of the ACP.<br />
                  Please ask your supervisor for more information.<br /><br />
                  <a href="javascript: history.back();">Go Back</a> to the previous page to continue.
                </p><br /><br />
                
                <span class="grey">Reason: &nbsp; <tt>b2b-not-supervisor</tt></span>
                ';
  }
  
  /**
   * Provide the "Need Admin Profile Permissions" HTML Block
   * @return string
   */
  public function notAllowed()
  {
    return '    <h1>Permission Denied</h1>
                <br />
                <p>
                  You do not have permission to use this section of the ACP.<br />
                  Please ask your supervisor for more information.<br /><br />
                  <a href="javascript: history.back();">Go Back</a> to the previous page to continue.
                </p><br /><br />
                
                <span class="grey">Reason: &nbsp; <tt>b2b-not-allowed</tt></span>
                ';
  }
  
  /**
   * Provide an error screen
   * @param string $title The title of the error.
   * @param string $message The associated message.
   * @param string $designation Optionally a designation to use.
   * @return string
   */
  public function error($title, $message, $designation = 'com.b2bfront.error.generic')
  {
    // Cut output
    ob_clean();
    
    print '
        
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html>
        <head>
          <title>' . $this->parent->config->get('com.b2bfront.site.title', true) . ' - ' . $title . '</title>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <link rel="favicon" type="image/icon" href="' . 
          $this->parent->config->get('com.b2bfront.site.url', true) . '/favicon.ico" />
          <link rel="stylesheet" type="text/css" href="static/style/default_reset.css" />
          <link rel="stylesheet" type="text/css" href="static/style/default_main.css" />
          <link rel="stylesheet" type="text/css" href="static/style/aui_elements.css" />
        </head>
        <body style="padding: 30px">
          <h1>' . $title . '</h1>
          <br />
          <p>
            ' . $message . '<br /><br />
            
            <span class="grey">Designation: <tt>' . $designation . '</tt></span>
          </p>
        </body>
      </html>   
    
    ';
  }
  
  /**
   * Last activity set to Now.
   * @return boolean
   */
  public function setLastActivity()
  {
    // Update DB - last activity
    $setActivity = $this->parent->db->query();
    $setActivity->update('bf_admins', array(
                          'last_activity_timestamp' => time()
                        ))
                ->where('`id` = \'{1}\'', $this->AID)
                ->limit(1)
                ->execute();
                  
    return true;
  }
  
  /**
   * Go Online
   * @return boolean
   */
  public function goOnline()
  {
    // Update DB - Online/Offline status = Online
    $setStatus = $this->parent->db->query();
    $setStatus->update('bf_admins', array(
                        'online' => 1
                      ))
              ->where('`id` = \'{1}\'', $this->AID)
              ->limit(1)
              ->execute();
                  
    // Cache & session
    $this->setInfo('online', 1);
                  
    return true;
  }
  
  /**
   * Go Offline
   * @return boolean
   */
  public function goOffline()
  {
    // Update DB - Online/Offline status = Offline
    $setStatus = $this->parent->db->query();
    $setStatus->update('bf_admins', array(
                        'online' => 0
                      ))
              ->where('`id` = \'{1}\'', $this->AID)
              ->limit(1)
              ->execute();
                  
    // Cache & session
    $this->setInfo('online', 0);
                  
    return true;
  }
}
?>