<?php
/** 
 * Main Class
 * Instanciated by both the runtimes of the frontend and ACP. 
 * This class also takes on the role of a Controller for the frontend.
 *
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BFClass
{
  /**
   * The location of the main log file
   * @var string
   */
  public $logLocation = '/opt/b2bfront/log/access.log';

  /**
   * Security
   * @var Security
   */
  public $security = null;

  /**
   * Database
   * @var Database
   */
  public $db = null;
  
  /**
   * Cache
   * @var Cache
   */
  public $cache = null;
  
  /**
   * The output manager
   * @var Output
   */
  public $out = null;
  
  /**
   * The current view
   * @var View
   */
  public $view = null;

  /**
   * The current model
   * @var Model
   */
  public $model = null;

  /**
   * Cleaned input data
   * @var Array
   */
  public $inputs = array();

  /**
   * Configuration Access
   * @var Config
   */
  public $config = null;
  
  /**
   * Notification System
   * @var Notifier
   */
  public $notifier = null;
  
  /**
   * Statistics toolkit
   * @var Statistics
   */
  public $stats = null;
  
  /**
   * A stopclock to monitor the time the page takes to render
   * @var ProfilerStopwatch
   */
  public $stopwatch = null;
  
  /**
   * The file handle for logging
   * @var resource
   */
  private $logHandle = null;
  
  /**
   * Memcache object
   * @var Memcache
   */
  public $memcache = null;
  
  /** 
   * Rackspace Cloud Connection
   * @var RackspaceCloud
   */
  public $rackspace = null;
  
  /**
   * ItemImages object
   * @var ItemImages
   */
  public $images = null;
  
  /**
   * Shopping Cart access object
   * @var Cart
   */
  public $cart = null;
  
  /**
   * Plugin server object
   * @var PluginServer
   */
  public $pluginServer = null;
  
  /** 
   * The total number of database transactions that have taken place
   * @var integer
   */
  public $queries = 0;

  /** 
   * The total number of cache hits for Memcached
   * @var integer
   */
  public $cacheHits = 0;

  /**
   * Start the main class instance.
   * Create instances of child objects.
   * @param boolean $debug Start the site in debug mode?
   * @return BFClass
   */
  public function __construct($debug = false)
  {
    // Software Version Information
    define('BF_VERSION', 'b2bfront-1.2.5-avo');
    
    // Definitions
    define('BF_MAX_LOG_SIZE', 1048576);
    
    // Publish header
    header('X-Powered-By: ' . BF_VERSION);
    header('Content-Type: text/html; charset=UTF-8');
    
    // Start the clock
    $this->stopwatch = new ProfilerStopwatch();
    $this->stopwatch->start();
  
    // Debug mode enable?
    if($debug)
    {
      // Define a global constant
      define('BF_DEBUG', true);
    }
  
    // Start logging
    // Open a log file for appending only
    $this->logHandle = @fopen($this->logLocation, 'a');
  
    // Collect input data
    $this->parseInputs();
  
    // Connect to database
    $this->db = new Database(BF_SQL_USER, BF_SQL_PASS, BF_SQL_HOST, BF_SQL_DB, & $this);
    
    // Access to config
    $this->config = new Config($this, $this->db);
    
    // Create the Memcache server
    if(class_exists('Memcache'))
    {
      $this->memcache = new Memcache();
      $this->memcache->pconnect($this->config->get('com.b2bfront.memcache.host', true),
                                $this->config->get('com.b2bfront.memcache.port', true)); 
    }
    else
    {
      // Failed to locate Memcache on this system - create dummy object
      $this->memcache = new MemcacheDummy();
    }
          
    // Create the cache
    $this->cache = new Cache($this);
    
    // Create Plugin Server object
    $this->pluginServer = new PluginServer($this);
    
    // Create images object
    $this->images = new ItemImages($this);
    
    // Create statistics access
    $this->stats = new Statistics($this);
  
    // Create notification system access
    $this->notifier = new Notifier($this);
    
    // Create shopping cart 
    $this->cart = new Cart($this);
    
    // Check if Session ID passing in the URL is allowed
    if($this->config->get('com.b2bfront.security.url-session-ids', true))
    {
      // Yes, copy PHPSESSID if it is passed
      if($this->in('PHPSESSID'))
      {
        // Copy into Cookie
        $_COOKIE['PHPSESSID'] = $this->in('PHPSESSID');
      }
    }
    
    // Unique visit count
    if($_COOKIE['b2b-visit'] != '1')
    { 
      // Unique Visits + 1
      $this->stats->increment('com.b2bfront.stats.website.unique-visits', 1);
      setcookie('b2b-visit', '1');
    }
    
    // Start security
    session_start();
    $this->security = new Security($this);    
    
    // Start output
    $this->out = new Output($this);
    
    // Plugin event:
    $this->pluginServer->b2bfrontDidStartup($this->parent);
  }
  
  /** 
   * The disposal method for this class
   * Called when the object is dereferenced and GC'd
   * @return boolean
   */
  public function __destruct()
  {
    return true;
  }
  
  /**
   * Load input data
   * @return boolean.
   */  
  private function parseInputs()
  {
    // Merge POST and GET
    $rawInputs = array_merge($_POST, $_GET);
    
    // Load $_POST and $_GET content
    foreach($rawInputs as $key => $value)
    {
      // Make the key and value safe
      $key = str_replace("\0", '', $key);
      $value = str_replace("\0", '', $value);
      
      // Store
      $this->inputs[$key] = $value;
    }
    
    return true;
  }
  
  /**
   * Make a value safe for provision by BFClass::in()
   * @param string $value The value to make safe.
   * @return string
   */
  private function safe($value)
  {
    return str_replace(chr(0x00), '', stripslashes(strip_tags($value)));
  }
    
  /**
   * Shorthand to retrieve a single input
   * NB: This method automatically applies strip_tags.
   *     Null bytes will have been removed beforehand by the input parse routine (see parseInputs)
   *     Use inUnfiltered for the unsafe (raw) versions.
   * Intrinsically safe.
   * @param string $name The index of the input to load
   * @return string|boolean False on failure
   */
  public function in($name)
  {
    if(isset($this->inputs[$name]))
    {
      return $this->safe($this->inputs[$name]);
    }
    
    return false;
  }
  
  /**
   * Equivalent to BFClass::in() with no filtering except null bytes and overrecursion.
   * Unsafe.
   * @param string $name The index of the input to load
   * @return string|boolean False on failure
   */
  public function inUnfiltered($name)
  {
    if(isset($this->inputs[$name]))
    {
      return str_replace(chr(0x00), '', $this->inputs[$name]);
    }
    
    return false;
  }

  /**
   * Equivalent to BFClass::in() except the value is converted to a signed integer.
   * Intrinsically safe.
   * @param string $name The index of the input to load
   * @return string|boolean False on failure
   */
  public function inInteger($name)
  {
    if(isset($this->inputs[$name]))
    {
      return intval($this->inputs[$name]);
    }
    
    return false;
  }
  
  
  /**
   * Shorthand to set an input manually during execution
   * @param string $name The index of the input to add/replace
   * @param string $value The value to assign
   * @return boolean
   */
  public function setIn($name, $value = '')
  {
    $this->inputs[$name] = $value;
    
    return true;
  }
  
  /**
   * Retrieve all input values as an associative array
   * Intrinsically safe.
   * @return array
   */
  public function allIn()
  {
    // Rebuild a safe copy of the inputs array
    $safeInputs = array();
    
    foreach($this->inputs as $key => $value)
    {
      $safeInputs[$key] = $this->safe($value);
    }
    
    return $safeInputs;
  }
  
  /**
   * Force redirection to the specified URL / resource
   * @param string $URL The resource to redirect to.
   * @return boolean
   */
  public function go($URL)
  {
    // Redirect, attempt - do not fail on output started already
    @header('Location: ' . $URL);
    
    return true;
  }
  
  /**
   * Prepare a view by loading it into memory
   * @param string $viewName The view to load.
   * @param boolean $skipRendering Optionally do not render the view.
   * @return boolean.
   */
  public function loadView($viewName, $skipRendering = false)
  {
    // Create a new view
    $this->view = new View($viewName, & $this, $skipRendering);
  }

  /**
   * Render the loaded view with the specified model
   * A view must be loaded with BFClass::loadView before a call is made.
   * @param string $modelName The model to render.
   * @return boolean.
   */
  public function renderModel($modelName)
  {
    // Can the model be loaded?
    if(!class_exists($modelName))
    {
      throw new Exception('Cannot find model: ' . $modelName);
    }
  
    // Load the model class
    try
    {
      // Instanciate
      $this->model = new $modelName(& $this);
     
      // Plugin event:
      $this->pluginServer->modelWillExecute(& $this->parent, $modelName);
            
      // Render
      $this->model->execute();
      $values = $this->model->getValues();

      // Plugin event:
      $this->pluginServer->modelDidExecute(& $this->parent, $modelName);
      
      // Apply the results to the loaded view
      $this->view->assign($values);

      // Render the view
      $this->view->render();
      
      // Write the view to output
      $this->out->buffer($this->view);
    }
    catch(Exception $exception)
    {
      throw new Exception('Unable to load model: ' . $modelName . ' ' . $exception->getMessage());
    }
  }
  
  /**
   * Shutdown
   * b2bFront execution should stop once this method is finished.
   * @return boolean
   */
  public function shutdown()
  {
    // Plugin event:
    $this->pluginServer->b2bfrontWillShutdown(& $this->parent);
  
    // Flush output
    $this->out->flush();
    
    // Close database
    $this->db->close();
    
    // Pack session data
    $this->security->write();
    
    // Stop the clock
    $this->stopwatch->stop();

    // Write debug information
    if(defined('BF_DEBUG'))
    {
      // Logging info
    }
    
    return true;
  } 
  
  /**
   * Write to the log file
   * @param string $title The title of the logged event
   * @param string $contents The contents of the log message
   * @return boolean
   */
  public function log($title, $contents)
  {
    // Title only?
    if($contents == '' && $title != '')
    {
      $contents = $title;
    }
  
    // No empty log lines if no content is present
    if($contents == '')
    {
      return true;
    }
    
    if(!$this->logHandle)
    {
      $this->logHandle = @fopen($this->logLocation, 'a+');
    }
    
    // Replace paths
    $contents = str_replace(BF_ROOT, '%ROOT%/' , $contents);
  
    $logMessage = str_pad(date('j/n/Y g:i a'), 25, ' ') . $contents . "\n"; 
    @fwrite($this->logHandle, stripslashes($logMessage));
    
    return true;
  }
  
  /**
   * Mark a file with a TTL
   * The file will be deleted when the TTL expires
   * @param string $path The path to mark
   * @param string $life The time in seconds for the file to remain. Default 1 hour.
   * @return boolean
   */
  public function setFileTTL($path, $life = 3600)
  {
    if(!$this->db)
    {
      return false;
    }
    
    // Fix the path
    $path = Tools::cleanPath($path);
  
    // Remove existing TTLs for this file
    $this->db->delete('bf_file_ttls')
             ->where('path = \'{1}\'', $path)
             ->execute();
  
    // Create a record of this in data.
    $query = $this->db->query();
    $query->insert('bf_file_ttls', array(
                   'path' => $path,
                   'expiry_timestamp' => time() + $life
                  ))
          ->execute();
    
    return true;
  }
  
  /**
   * Purge all expired file TTLs
   * @return boolean
   */
  public function purgeFileTTLs()
  {
    if(!$this->db)
    {
      return false;
    }
  
    // Find all TTLs
    $this->db->select('*', 'bf_file_ttls')
             ->where('expiry_timestamp < UNIX_TIMESTAMP()')
             ->execute();
             
    while($ttl = $this->db->next())
    {
      // Remove path
      @unlink(BF_ROOT . '/' . $ttl->path);
      @unlink($ttl->path);
    }

    // Remove all TTLs
    $this->db->delete('bf_file_ttls')
             ->where('expiry_timestamp < UNIX_TIMESTAMP()')
             ->execute();
    
    return true;
  }
  
  /**
   * Create an event log entry
   * Must have a connection to the database, this is checked.
   *
   * Event levels are as follows:
   *
   *   1 - Warning
   *       The software is in a temporary or continuing state in which some features do not work.
   *       E.g. a missing noncritical file or security warning.
   *  
   *   2 - Error
   *       One or more operations failed and the requested work was not done.       
   *       E.g. a failed database transaction.
   *
   *   3 - Fatal
   *       The application stopped functioning because of an error.
   *       E.g. a missing critical or 'required' file.
   *
   *   4 - Emergency
   *       There is an ongoing problem that requires user action.
   *       E.g. failed scheduled events or internal processes.  
   *
   * @param string $title The title of the event
   * @param string $contents The content data
   * @param integer $level Optionally the level of the event. See above. Default 1
   * @return boolean
   */
  public function logEvent($title, $contents, $level = 2)
  {
    // Check DB
    if(!$this->db)
    {
      // No data connection
      return false;
    }
    
    // Check level
    if($level < 1 || $level > 4)
    {
      // Invalid level
      return false;
    }
    
    // Serious?
    if($level > 2)
    {
      $this->stats->increment('com.b2bfront.stats.system.errors', 1);
    }
    
    // Append some detail
    $contents .= "<br />\n<br />\n";
    $contents .= 'Serving URI: ' . $_SERVER['REQUEST_URI'] . "<br />\n";
    $contents .= 'Remote IP: ' . $_SERVER['REMOTE_ADDR'] . "<br />\n<br />\n";
    
    // Insert event
    $eventInsert = $this->db->query();
    $eventInsert->insert('bf_events', array(
                          'level' => intval($level),
                          'title' => $title,
                          'contents' => $contents,
                          'timestamp' => time()
                        ))
                ->execute();
    
    return true;
  }
  
  /**
   * Exception handler
   * @param Exception $exception The exception that has caused the error.
   * @return boolean
   */
  public static function handleException($exception)
  {
    // Avoid out-of-stack exceptions here
    try
    {
      // Clear any output
      ob_clean();
    }
    catch(Exception $e)
    {
      // Empty buffer - Ignore.
    }
    
    // Write a message to output.
    // Don't use the Output manager, it might be broken.
    
    print '<style type=\'text/css\'>body,p,h1,h2 {font-family: helvetica, ' . 
          'verdana, sans-serif;} body {margin: 35px}</style>' . "\n";
    print '<h1>Page Error</h1>' . "\n";
    print '<p style=\'padding: 0 0 40px 0; border-bottom: 1px solid #afafaf\'>' . "\n";
    print '  We\'re sorry, there was a problem during the production of this page.<br /><br />' . "\n";
    print '  You could try reloading the page using the Refresh button on your browser' . "\n";
    print '  or clearing your browser\'s cache or cookies.' . "\n";
    print '</p>' . "\n";
    print '' . "\n";
    print '<p style=\'color: #afafaf; padding: 20px 0 0 0 \'>' . "\n";
    print '  An audit has been created for this error.<br />' . "\n";
    print '  The unique designation of the error is ER' . intval($logID) . "\n";
    print '</p>' . "\n";
    
    // Retrieve the stack
    $stackData = array_reverse($exception->getTrace());
    
    // Start creating an admin log
    $log =  date("F j, Y, g:i a") . "\n";
    $log .= 'b2bFront error event log.' . "\n\n";
    $log .= 'Call graph:' . "\n";
    $pre = '';
    
    // Generate a stack overview
    foreach($stackData as $stackFrame)
    {
      $log .= $pre . str_replace(BF_ROOT, '', $stackFrame['file']) . ':' . 
              $stackFrame['line'] . ' -> ' . $stackFrame['function'] . '()' . "\n";
      $pre .= '    ';
    }

    // Add the Exception Description
    $log .= "\n" . 'Exception breakdown:' . "\n";
    $log .= print_r($exception, true) . "\n\n";
    $log .= 'This is the end of the log.';
     
    // Are we executing in debug mode?
    if(defined('BF_DEBUG'))
    {
      // Print the stack trace
      print '<br />' . "\n";
      print '<h2>Debug output: </h2>' . "\n";
      print '<pre style=\'overflow: auto; height:400px; border: 2px solid' . 
            ' #cfcfcf; padding: 10px; width: 100%;\'>';
      print $log; 
      print '</pre>';
    }

    return true;
  }

  /**
   * Error handler
   * @param integer $errorLevel The level of the error raised.
   * @param string $errorMessage The error message.
   * @param string $errorFile The file that caused the error.
   * @param integer $errorLine The line number the error was encountered on.
   * @return boolean
   */
  public static function handleError($errorLevel, $errorMessage, $errorFile, $errorLine)
  {
    // We don't like errors, surpress them and create exceptions instead...
    
    if($errorLevel == E_ERROR)
    {
      self::handleException(new Exception($errorMessage));
    }
    
    return true;
  }
  
}
?>
