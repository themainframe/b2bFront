<?php
/** 
 * PluginServer Class
 * Hosts plugins and executes them at runtime
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class PluginServer extends Base
{
  /**
   * The collection of plugins
   * @var array
   */
  private $plugins = array();

  /**
   * Psuedo-constructor
   * Autoloads plugins from the configured directory
   * @return boolean
   */
  public function init()
  {
    // Should this subsystem load?
    if(!$this->parent->config->get('com.b2bfront.plugins.enable', true))
    {
      return false;
    }
  
    // Get location
    $pluginPath = BF_ROOT . 
      $this->parent->config->get('com.b2bfront.plugins.location', true);
    
    // List disabled plugins
    $disabledPlugins = 
      Tools::unCSV($this->parent->config->get('com.b2bfront.plugins.disabled-plugins', true));
    
    // Load all plugins
    $pluginList = Tools::listDirectory($pluginPath);
    
    foreach($pluginList as $pluginFilename)
    {
      // Get class name and instanciate
      $pluginClassName = str_replace('.plugin.php', '', $pluginFilename);
      
      // Disabled?
      if(in_array($pluginClassName, $disabledPlugins))
      {
        // Do not load
        continue;
      }
    
      // Load file
      @include($pluginPath . '/' . $pluginFilename);
      
      // Verify that the class can be loaded
      if(class_exists($pluginClassName))
      {
        $this->plugins[] = new $pluginClassName();
      }
      else
      {
        $this->parent->log('Plugins', 'Plugin \'' . $pluginClassName . 
          '\' is invalid and cannot be loaded.');
      }
    }
    
    return true;
  }
  
  /**
   * Execute on all served plugins:
   *
   * Executed after startup
   * @param BFClass $parent The parent object
   * @return boolean
   */
  public function b2bfrontDidStartup($parent)
  {
    // Execute on all plugins
    foreach($this->plugins as $plugin)
    {
      $plugin->b2bfrontDidStartup($parent);
    }
    
    return true;
  }

  /**
   * Execute on all served plugins:
   *
   * Executed before model execution takes place
   * @param BFClass $parent The parent object
   * @param string $modelName The name of the model that will be rendered.
   * @return boolean
   */
  public function modelWillExecute($parent, $modelName)
  {
    // Execute on all plugins
    foreach($this->plugins as $plugin)
    {
      $plugin->modelWillExecute($parent, $modelName);
    }
    
    return true;
  }
  
  /**
   * Execute on all served plugins:
   *
   * Executed after model execution has taken place
   * @param BFClass $parent The parent object
   * @param string $modelName The name of the model that will be rendered.
   * @return boolean
   */
  public function modelDidExecute($parent, $modelName)
  {
    // Execute on all plugins
    foreach($this->plugins as $plugin)
    {
      $plugin->modelDidExecute($parent, $modelName);
    }
    
    return true;
  }

  /**
   * Execute on all served plugins:
   *
   * Executed before view rendering takes place
   *
   * If an array is returned, the view token array will be replaced with it.
   * If boolean false is returned, the view token array will not be modified.
   *
   * @param BFClass $parent The parent object
   * @param string $viewName The name of the view file that will be rendered.
   * @param array $viewTokens An array of tokens for the view
   * @return boolean|array
   */
  public function viewWillRender($parent, $viewName, $viewTokens)
  {
    // Execute on all plugins
    foreach($this->plugins as $plugin)
    {
      $newViewTokens = $plugin->viewWillRender($parent, $viewName, $viewTokens);
      
      // Change needed?
      if($newViewTokens)
      {
        $viewTokens = $newViewTokens;
      }
    }
    
    return $viewTokens;
  }
 
  /**
   * Execute on all served plugins:
   *
   * Executed after view rendering has taken place
   * @param BFClass $parent The parent object
   * @param string $viewName The name of the view file that will be rendered.
   * @return boolean
   */
  public function viewDidRender($parent, $viewName)
  {
    // Execute on all plugins
    foreach($this->plugins as $plugin)
    {
      $plugin->viewDidRender($parent, $viewName);
    }
    
    return true;
  }
  
  /**
   * Execute on all served plugins:
   *
   * Executed before final shutdown
   * @param BFClass $parent The parent object
   * @return boolean
   */
  public function b2bfrontWillShutdown($parent)
  {
    // Execute on all plugins
    foreach($this->plugins as $plugin)
    {
      $plugin->b2bfrontWillShutdown($parent);
    }
    
    return true;
  }
}
?>