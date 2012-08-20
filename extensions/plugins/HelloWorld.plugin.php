<?php
/** 
 * Plugin: HelloWorld
 * Demonstration of b2bFront Plugin functionallity.
 *
 *   Description:  Changes the title of all rendered pages to "Hello World!"
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @version 1.0
 */
class HelloWorld extends Plugin
{
  /**
   * Executed before view rendering takes place.
   *
   * If an array is returned, the view token array will be replaced with it.
   * If boolean false is returned, the view token array will not be modified.
   *
   * @param BFClass $parent The parent object
   * @param string $viewName The name of the view file that will be rendered.
   * @param array $viewTokens An array of tokens for the view
   * @return boolean|array
   */
  protected function viewWillRender($parent, $viewName, $viewTokens)
  {
    // Modify and return the $viewTokens array argument
    // b2bFront will replace the view token collection with our new one.
    
    $viewTokens['title'] = 'Hello World!';
    
    return $viewTokens;
  }
}  
?>