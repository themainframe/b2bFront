<?php
/** 
 * Model: Featured
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Featured extends RootModel
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
    $this->parent->security->action('Featured Items');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Featured');
    $this->addValue('tab_featured', 'selected');
    
    // Find the item tag that represents featured items
    $featuredTag = $this->parent->config->get('com.b2bfront.item-tags.featured', true);
    
    // Find all items tagged with this
    $items = $this->parent->db->query();
    $items->select('*', 'bf_items')
          ->where('`visible` = \'1\' AND `id` IN (SELECT `item_id` FROM ' . 
                  '`bf_item_tag_applications` WHERE `item_tag_id` = \'{1}\')', $featuredTag)
          ->execute();
  
    // Get the thumbnail generator closure
    $getThumbnail = $this->parent->images->loadThumbnail;

    // Build a collection
    $itemCollection = array();
    while($item = $items->next())
    {
      $itemCollection[] = array(
        'id' => $item->id,
        'name' => $item->name,
        'image_url' => $getThumbnail($item, $this->parent, 'thm')
      );
    }
    
    
    // Make the items available to the view
    $this->addValue('items', $itemCollection);
    
    return true;
  }
}  
?>