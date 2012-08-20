<?php
/** 
 * Model: Portal
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Portal extends RootModel
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
    $this->parent->security->action('Login Portal');
    
    // Provide error
    if($this->parent->in('error') == '1')
    {
      $this->addValue('incorrect', '1');
    }

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true));
    
    return true;
  }
}  
?>