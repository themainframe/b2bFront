<?php
/** 
 * Model: Item
 * Provides a view of an item
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Item extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Redirect to parent view?
    if($this->parent->in('p') == '1')
    {
      // Move to parent item viewport
      $this->parent->go(Tools::getModifiedURL(array('option' => 'pitem')));
      
      // Stop rendering model
      return false;
    }
    
    // Get the item information
    $itemID = $this->parent->inInteger('id');
    
    // Set by SKU?
    if($this->parent->in('sku'))
    {
      // Find item by SKU
      $findItem = $this->parent->db->getRow('bf_items',
        strtoupper($this->parent->in('sku')), 'sku');
        
      if($findItem)
      {
        $itemID = $findItem->id;
      }
    }

    $item = new BOMItem($itemID, & $this->parent);
    
    // Visible?
    if(!$item->visible)
    {
      // Overrride the view
      $this->parent->loadView('alert');
      
      // Display an alert
      $this->addValue('alertText', 'That item is currently unavailable.');
      
      return false;
    }
    
    // Does this item have a parent? If so redirect to show it...
    if($item->parent_item_id != -1)
    {
      // Move to parent item viewport
      $queryStringChanges = array(
                              'option' => 'pitem',
                              'id' => $item->parent_item_id
                            );
      $this->parent->go(Tools::getModifiedURL($queryStringChanges));
      
      // Stop rendering model
      return false;
    }
    
    // Not found?
    if(!$item)
    {
      // Overrride the view
      $this->parent->loadView('alert');
      
      // Display an alert
      $this->addValue('alertText', 'That item does not exist.');
      
      return false;
    }
    
    // Messages
    switch($this->parent->inInteger('message'))
    {
      case 1:
      
        $this->addValue('notification', 'The item has been added to your favourites.');
      
        break;
    }
    
    // Add all details to view
    $this->add($item->attributes);
    
    // Make my price available
    $this->addValue('my_price', $item->myPrice);
    
    // Update CCTV
    $this->parent->security->action('Item: ' . $item->sku . '&nbsp;(' . $item->name . ') ' . 
      '[' . $item->classificationName .  ']' );
    
    // Set this model's title
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true)
                             . ' - ' . $item->name);
    
    // Overwrite item description with fixed HTML
    $item->description = strip_tags($item->description,
      $this->parent->config->get('com.b2bfront.site.allowed-description-html', true));
    $this->addValue('itemDescription', $item->description);
    
    // Create images
    $itemImages = array();
    
    foreach($item->images as $image)
    {
      // First image?
      if(count($itemImages) == 0)
      {
        $this->addValue('mainItemImage', Tools::getImageThumbnail($image->url, 'lrg'));
      }
    
      $itemImages[] = array(
        'full' => $image->url,
        'thm' => Tools::getImageThumbnail($image->url),
        'lrg' => Tools::getImageThumbnail($image->url, 'lrg'),
        'prv' => Tools::getImageThumbnail($image->url, 'prv')
      );
    }
    
    // Empty set now?
    if(count($itemImages) == 0)
    {
      $this->addValue('mainItemImage', 
        $this->parent->config->get('com.b2bfront.site.default-image', true));
    }
    
    // Provide the images to the view
    $this->addValue('itemImages', $itemImages);

    // Set brand name
    $this->addValue('brandName', $item->brand->name);

    // Set classification ID
    $this->addValue('classificationID', $item->classification_id);
    
    // Get my band
    $dealer = new BOMDealer($this->parent->security->UID, $this->parent);
    $this->addValue('band', str_replace(' Dealer', '', $dealer->bandName));
    
    // In stock?
    $this->addValue('inStock', ($item->stock_free > 0 ? 1 : 0));
    
    // Due date
    if($item->stock_date != '' && intval($item->stock_date) > time())
    {
      // Show due date
      $this->addValue('dueDateAvailable', 1);
      $this->addValue('dueDate', date('d/m/Y', $item->stock_date));
    }
  
    // Catalogue page
    if($item->paper_catalogue_page != '')
    {
      // Show page
      $this->addValue('paperPageAvailable', 1);
      $this->addValue('paperPage', $item->paper_catalogue_page);
    }
    
    
    // Validate emptiness of properties
    $properties = array();
    foreach($item->linearProperties as $property)
    {
      if($property['value'] != '')
      {
        $properties[] = $property;
      }
    }
    
    // Provide attributes
    $this->addValue('attributes', $properties);
    
    $this->addValue('hasAttributes', (count($properties) > 0 ? 1 : 0));
    
    // Get the QTY of this item in the user basket
    $basketSearch = $this->parent->db->query();
    $basketSearch->select('*', 'bf_user_cart_items')
                 ->where('`item_id` = \'{1}\' AND `user_id` = \'{2}\'',
                         $itemID,
                         $this->parent->security->UID)
                 ->limit(1)
                 ->execute();
                 
    // In Basket?
    if($basketSearch)
    {
      $basketRow = $basketSearch->next();
      $this->addValue('itemBasketCount', $basketRow->quantity);
    }
    
    // Increment stats
    $this->parent->stats->increment('com.b2bfront.stats.website.item-views', 1);
    $this->parent->stats->increment('com.b2bfront.stats.custom.' . $itemID .
      '-item-views', 1);

    return true;
  }
}  
?>