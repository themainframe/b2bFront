<?php
/** 
 * Plugin Base Class
 * Abstract class
 *
 * Represents a plugin with no functionality.
 * Other plugins extend this class to mask unused methods.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
abstract class Plugin
{
  /**
   * Executed after startup
   * @param BFClass $parent The parent object
   * @return boolean
   */
  public function b2bfrontDidStartup($parent)
  {
    return true;
  }

  /**
   * Executed before model execution takes place
   * @param BFClass $parent The parent object
   * @param string $modelName The name of the model that will be rendered.
   * @return boolean
   */
  public function modelWillExecute($parent, $modelName)
  {
    return true;
  }
  
  /**
   * Executed after model execution has taken place
   * @param BFClass $parent The parent object
   * @param string $modelName The name of the model that will be rendered.
   * @return boolean
   */
  public function modelDidExecute($parent, $modelName)
  {
    return true;
  }

  /**
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
    return false;
  }
 
  /**
   * Executed after view rendering has taken place
   * @param BFClass $parent The parent object
   * @param string $viewName The name of the view file that will be rendered.
   * @return boolean
   */
  public function viewDidRender($parent, $viewName)
  {
    return true;
  }
  
  /**
   * Executed before final shutdown
   * @param BFClass $parent The parent object
   * @return boolean
   */
  public function b2bfrontWillShutdown($parent)
  {
    return true;
  }
}
?>