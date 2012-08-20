<?php
/** 
 * Model: Stats Portal
 * Increments a statistic value then redirects.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class StatPortal extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Increment stat
    $this->parent->stats->increment($this->parent->in('stat'), 1);

    // Redirect
    $this->parent->go(Tools::getModifiedURL(array(
      'option' => $this->parent->in('option_target'),
      'id' => $this->parent->inInteger('id_target')
    )));
    
    // No more rendering
    return true;
  }
}  
?>