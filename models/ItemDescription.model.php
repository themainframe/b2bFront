<?php
/** 
 * Model: Item Description
 * Provides a view of an item's description only
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ItemDescription extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Get the item information
    $item = new BOMItem($this->parent->inInteger('id'), $this->parent);
    
    // Not found?
    if(!$item)
    {
      // Overrride the view
      $this->parent->loadView('alert');
      
      // Display an alert
      $this->addValue('alertText', 'That item does not exist.');
      
      return false;
    }
    
    // Get allowed HTML list
    $allowedHTML = $this->parent->config->get('com.b2bfront.site.allowed-description-html', true);
    $allowedHTML = '<' . str_replace(',', '><', $allowedHTML) . '>';
    
    // Fix item description bad HTML
    $item->description = strip_tags($item->description, $allowedHTML);

    // Replace HTML special entities if desired
    if($this->parent->config->get('com.b2bfront.site.description-html-utf8', true))
    {
      $item->description = utf8_encode(utf8_decode($item->description));
    }

    // Try to tidy the string if possible
    if(function_exists('tidy_repair_string') && 
      $this->parent->config->get('com.b2bfront.site.tidy-description-html', true))
    {
      $item->description = tidy_repair_string($item->description);
    }
    
    // Replace attributes
    foreach($item->properties as $attribute => $value)
    {
      $item->description = str_replace('{' . $attribute . '}', $value, $item->description);
    }

    // Provide the images to the view
    $this->addValue('itemDescription', $item->description);
    
    return true;
  }
}  
?>