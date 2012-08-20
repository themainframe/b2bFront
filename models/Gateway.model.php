<?php
/** 
 * Model: Gateway
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Gateway extends RootModel
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
    $this->parent->security->action('Login Gateway');
    
    // Provide error
    if($this->parent->in('error') == '1')
    {
      $this->addValue('incorrect', '1');
    }
    
    // Provide data
    $this->addValue('option', $this->parent->in('option'));
    $this->addValue('id', $this->parent->in('id'));
    
    
    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true));
    
    return true;
  }
}  
?>