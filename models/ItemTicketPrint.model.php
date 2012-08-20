<?php
/** 
 * Model: Item Ticket Print
 * Allows the user to print a ticket for this item.
 * Performs the actual printing of the ticket.  
 * (Renders a view with no decoration.) 
 *
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ItemTicketPrint extends RootModel
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
    $this->parent->security->action('Print/Export item');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Export &amp; Print');
    $this->addValue('tab_home', 'selected');
  
    // Logged in?
    if(!$this->parent->security->loggedIn())
    {
      $this->parent->loadView('login');
   
      return false;
    }
    
    // Get the item that the question is about
    $item = new BOMItem($this->parent->inInteger('id'), $this->parent);
    
    // Valid item?
    if(!$item)
    {
      // Stop rendering
      $this->parent->go('./?');
      exit();
    }
    
    //
    // Provide user data
    //
          
    $this->addValue('price', $this->parent->in('price')); 
    $this->addValue('price_title', $this->parent->in('price_title')); 
    $this->addValue('item_title', $this->parent->in('item_title')); 
    $this->addValue('company_title', $this->parent->in('company_title')); 
    $this->addValue('code_visible', $this->parent->inInteger('code_visible')); 
    $this->addValue('rrp_visible', $this->parent->inInteger('rrp_visible')); 
    
    
    //
    // Do Work
    //
    
    // Provide SKU and ID
    $this->addValue('sku', $item->sku);
    $this->addValue('name', $item->name);
    $this->addValue('id', $item->id);
    $this->addValue('rrp', $item->rrp_price);
    
    // Description
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
    $this->addValue('description', $item->description);
    
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
    
    // Provide "Done" flag
    $this->addValue('done', $this->parent->inInteger('done'));
    
    $this->parent->stats->increment('com.b2bfront.stats.users.tickets-printed', 1);
    
    return true;
  }
}  
?>