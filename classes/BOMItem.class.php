<?php
/** 
 * Business Object Model
 * Item Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BOMItem extends BOMObject
{
  /**
   * Load an item from the database with an ID
   * @param integer $itemID The ID to initialise this object with.
   * @param BFClass* $parent The parent object.
   * @return BOMItem|boolean
   */
  public function __construct($itemID, & $parent)
  {
    // Superclass constructor
    parent::__construct($parent);
    
    // Initialisation possible?
    if(!$itemID)
    {
      return;
    }
    
    // Load the item
    $itemRow = $parent->db->getRow('bf_items', $itemID);
    
    // Missing
    if(!$itemRow)
    {
      $this->parent->log('BOM', 'No such item: ' . $itemID);
      $this->attributes = false;
      
      return false;
    }
    
    // Set each value
    foreach($itemRow as $key => $value)
    {
      $this->{$key} = $value;
    }
  }
  
  /**
   * Load item image collection
   * @return boolean
   */
  protected function loadImages()
  {
    // Create images collection
    $this->images = array();
    
    // Load each image
    $imageCollection = $this->parent->db->query();
    $imageCollection->select('*', 'bf_item_images')
                    ->where('`item_id` = \'{1}\'', $this->id)
                    ->order('priority', 'asc')
                    ->execute();
                    
    $imageCollectionHash = $imageCollection->getInHash('image_id');
    
    $imageCollection->rewind();
    
    // Get priorities
    $priorities = array();
    while($imageLink = $imageCollection->next())
    {
      $priorities[$imageLink->image_id] = $imageLink->priority;
    }
    
    // Find images
    $images = $this->parent->db->query();
    $images->select('*', 'bf_images')
           ->whereInHash($imageCollectionHash)
           ->execute();
           
    // Store each
    while($image = $images->next())
    {
      $this->images[$priorities[$image->id]] = $image;
    }
    
    ksort($this->images);
    
    return $this->images;
  }
  
  /**
   * Load item tags collection
   * @return boolean
   */
  protected function loadTags()
  {
    // Create tags collection
    $this->tags = array();
    
    // Load each tag
    $tagCollection = $this->parent->db->query();
    $tagCollection->select('*', 'bf_item_tag_applications')
                  ->where('`item_id` = \'{1}\'', $this->id)
                  ->execute();
                    
    $tagCollectionHash = $tagCollection->getInHash('item_tag_id');
    
    // Find images
    $tags = $this->parent->db->query();
    $tags->select('*', 'bf_item_tags')
         ->whereInHash($tagCollectionHash)
         ->execute();
           
    // Store each
    while($tag = $tags->next())
    {
      $this->tags[] = $tag;
    }
    
    return $this->tags;
  }
  
  /**
   * Load sibling items
   * @return array
   */
  protected function loadSiblings()
  {
    // Empty parent?
    if($this->parent_item_id == -1)
    { 
      // No siblings
      return array();
    }
  
    // Collect sibling items (Ie. Items with the same parent)
    $siblings = $this->parent->db->query();
    $siblings->select('*', 'bf_items')
             ->where('`parent_item_id` = \'{1}\' AND `id` <> \'{2}\'',
                     $this->parent_item_id, $this->id)
             ->execute();
             
    // Build BOMItems with them and populate an array
    $itemSiblings = array();
    
    while($sibling = $siblings->next())
    {
      $itemSiblings[$sibling->id] = 
        self::initWithRow($sibling, & $this->parent);
    }
    
    return $itemSiblings;
  }
  
  /**
   * Load my price
   * @return float
   */
  protected function loadMyPrice()
  {
    // Create a pricer
    $pricer = new Pricer(& $this->parent);
    
    // Calculate my price
    $price = $pricer->myPrice($this);
    
    return $price;
  }
  
  /**
   * Load the brand
   * @return BOMBrand
   */
  protected function loadBrand()
  {
    return new BOMBrand($this->brand_id, & $this->parent);
  }
  
  /** 
   * Load the classification title
   * @return string
   */
  protected function loadClassificationName()
  {
    // Get the classification
    $classification = $this->parent->db->query();
    $classification->select('*', 'bf_classifications')
                   ->where('`id` = \'{1}\'', $this->classification_id)
                   ->limit(1)
                   ->execute();
    
    // Has one?
    if($classification->count == 1)
    {
      $classificationObject = $classification->next();
      return $classificationObject->name;
    }
    
    return '';
  }
  
  /**
   * Load all properties as an associative array
   * @return array
   */
  protected function loadProperties()
  {
    // Load classification attributes for this item
    $classificationAttributes = $this->parent->db->query();
    $classificationAttributes->select('*', 'bf_classification_attributes')
                             ->where('`classification_id` = \'{1}\'',
                                $this->classification_id)
                             ->execute();
                             
    // Build a collection of properties
    $properties = array();
    
    while($classificationAttribute = $classificationAttributes->next())
    {
      $properties[$classificationAttribute->id] = 
        $classificationAttribute->name;
    }
    
    // Load classification attribute applications for this item
    $classAttribApplications = $this->parent->db->query();
    $classAttribApplications->select('*', 'bf_item_attribute_applications')
                            ->where('`item_id` = \'{1}\'', $this->id)
                            ->execute();
                          
    // Build an associative array
    $attributes = array();
    
    while($classAttribApplication = $classAttribApplications->next())
    {
      $attributes[$properties[$classAttribApplication->classification_attribute_id]] = 
        $classAttribApplication->value;
    }
    
    return $attributes;
  }

  /**
   * Load all properties as a linear array
   * @return array
   */
  protected function loadLinearProperties()
  {
    // Load classification attributes for this item
    $classificationAttributes = $this->parent->db->query();
    $classificationAttributes->select('*', 'bf_classification_attributes')
                             ->where('`classification_id` = \'{1}\'',
                                $this->classification_id)
                             ->execute();
                             
    // Build a collection of properties
    $properties = array();
    
    while($classificationAttribute = $classificationAttributes->next())
    {
      $properties[$classificationAttribute->id] = 
        $classificationAttribute->name;
    }
    
    // Load classification attribute applications for this item
    $classAttribApplications = $this->parent->db->query();
    $classAttribApplications->select('*', 'bf_item_attribute_applications')
                            ->where('`item_id` = \'{1}\'', $this->id)
                            ->execute();
                          
    // Build an associative array
    $attributes = array();
    
    while($classAttribApplication = $classAttribApplications->next())
    {
      $prop = array(
        'name' => $properties[$classAttribApplication->classification_attribute_id],
        'id' => $classAttribApplication->id,
        'attribute_id' => $classAttribApplication->classification_attribute_id,
        'value' => $classAttribApplication->value
      );
    
      $attributes[$classAttribApplication->classification_attribute_id] = $prop;
    }
    
    return $attributes;
  }
}
?>