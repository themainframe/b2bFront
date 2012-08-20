<?php
/** 
 * Model: Parent Item
 * Provides a view of an item that is a parent of one or more child items
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ParentItem extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    // Get the ID
    $ID = $this->parent->inInteger('id');
    
    // Load the parent item
    $parentItem = new BOMParentItem($ID, & $this->parent);
    
    // Failed to load?
    if(!$parentItem)
    {
      $this->parent->go('./?');
      return false;
    }
    
    // No child items?
    if(count($parentItem->children) == 0)
    {
      $this->parent->go('./?');
    }
    
    // Add parent data
    foreach($parentItem->attributes as $key => $value)
    {
      $this->addValue($key, $value);
    }
    
    // Collect item images
    $itemImages = array();
      
    foreach($parentItem->images as $image)
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
        'prv' => Tools::getImageThumbnail($image->url, 'prv'),
        'id' => $image->id
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
    
    // Provide the brand information
    $this->addValue('brandName', $parentItem->brand->name);
    
    // Provide attributes
    // Validate emptiness of properties
    $properties = array();
    foreach($parentItem->linearProperties as $property)
    {
      if($property['value'] != '')
      {
        $properties[] = $property;
      }
    }
    
    // Provide attributes
    $this->addValue('attributes', $properties);  
  
    $this->addValue('hasAttributes', (count($properties) > 0 ? 1 : 0));
    
    // Load the drop down values for variations on this item
    $variationOptions = $this->parent->db->query();
    $variationOptions->select('*', 'bf_parent_item_variations')
                     ->where('`parent_item_id` = \'{1}\'', $ID)
                     ->execute();
                     
    // Compile variation option names and possible values
    $variationNames = array();
    $variationValues = array();
  
    // First run?
    $first = true;
    $variationOptValues = array();
    $mainVariation = null;
    
    while($variationOption = $variationOptions->next())
    {
      // Store name
      $variationNames[$variationOption->id]['name'] = $variationOption->name;
      
      // Store values for this option name
      $variationOptionValues = $this->parent->db->query();
      $variationOptionValues->select('*', 'bf_parent_item_variation_data')
                            ->where('`parent_item_variation_id` = \'{1}\'',
                                    $variationOption->id)
                            ->execute();
                            
      // Build Dropdown text
      $dropDownHTML  = "\n";
      $dropDownHTML .= '<select class="variation" name="' . $variationOption->id . 
                       '" id="' . $variationOption->id . '">' . "\n";
      
      // Keep track of duplicates
      $variationOptionValueCollection = array();
      
      while($variationOptionValue = $variationOptionValues->next())
      {
        if(in_array($variationOptionValue->value, $variationOptionValueCollection))
        {
          // Seen before...
          continue;
        }
        
        $dropDownHTML .= '  <option value="' . $variationOptionValue->id . 
                         '">' . $variationOptionValue->value . '</option>' . "\n";
        
        // Add
        $variationOptionValueCollection[] = $variationOptionValue->value;
        if($first)
        {
          $variationOptValues[] = $variationOptionValue->value;
          $mainVariation = $variationOption->id;
        }
      }
      
      // No longer first run
      $first = false;
      
      $dropDownHTML .= '</select>' . "\n";
      
      // Store option values HTML
      $variationNames[$variationOption->id]['values'] = $dropDownHTML;
    }
    
    // Make variation options available to view
    $this->addValue('variationNames', $variationNames);
    
    // Get the category
    $category = new BOMCategory($parentItem->category_id, $this->parent);
    if($category->parent_child_display_mode == 'table')
    {
      // $this->addValue('tableMode', 1);
      $this->addValue('variationOptValues', $variationOptValues);
      $this->addValue('mainVariationID', $mainVariation);
    }
    
    // Increment stats
    $this->parent->stats->increment('com.b2bfront.stats.website.item-views', 1);
    
    return true;
  }
}  
?>