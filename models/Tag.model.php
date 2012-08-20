<?php
/** 
 * Model: Item Tag
 * Provides an item tag listing
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Tag extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Get the tag information
    $tag = $this->db->getRow('bf_item_tags', $this->parent->inInteger('id'));
    
    // Not found?
    if(!$tag)
    {
      // Override the view
      $this->parent->loadView('alert');
      $this->addValue('alertText', 'That Item Tag does not exist.');
      
      return false;
    }
    
    // Update CCTV
    $this->parent->security->action('Item Tag: ' . $tag->name);
    
    // Set this model's title
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) 
                             . ' - ' . $tag->name);
    
    // Tag was found
    $this->addValue('exists', 1); 
    $this->addValue('item_tag_name', $tag->name);
    $this->addValue('item_tag_id', $tag->id);
    
    // Get all tag applications
    $applications = $this->parent->db->query();
    $applications->select('*', 'bf_item_tag_applications')
                 ->where('item_tag_id = \'{1}\'', $tag->id)
                 ->execute();
                 
    // Find all items
    $itemIDs = $applications->getInHash('item_id');
    $query = $this->parent->db->query();
    $query->select('*', 'bf_items')
          ->where('`id` IN ({1}) AND `visible` = 1', ($itemIDs == '' ? '0' : $itemIDs));
              
    // Precache cart values
    $this->parent->cart->prefetch();
      
    // Construct table
    $dataView = new DataTable('items', & $this->parent, $query);
    $dataView->setOption('alternateRows');
    $dataView->setOption('showTopPager');
    $dataView->setOption('subjectName', 'Item');
    $dataView->addColumns($this->defaultColumns);

    // Add the table to the view template
    $this->addValue('table', $dataView->render());
    
    return true;
  }
}  
?>