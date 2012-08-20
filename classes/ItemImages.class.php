<?php
/** 
 * ItemImages Class
 * Provides services for loading and manipulating images
 * within the website.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ItemImages extends Base
{
  /**
   * A closure that allows loading of a thumbnail for a row object.
   * Anonymous Function (Closure)
   * @var Closure
   */
  public $loadThumbnail = null;
    
  /**
   * Initialise Images object
   * @param BFClass $parent A reference to the parent object.
   * @return FormListBuilder
   */
  public function __construct(& $parent)
  {
    // Initialise closures
    $this->loadThumbnail = function($row, & $parent, $size = 'lst')
    {
      // Size valid?
      if(!$size)
      {
        // Default
        $size = 'lst';
      }
      
      // Build a cache path to hit
      $cachePath = 'item_' . $row->id . '.image_0.' . $size;
      
      // Read the cache
      $cacheResult = $parent->cache->getValue($cachePath);
      
      // Hit?
      if($cacheResult)
      {
        return Tools::getImageThumbnail($cacheResult, $size);
      }
      
      // Otherwise, retrieve the rows and cache them
      $getImages = $parent->db->query();
      $getImages->select('*', 'bf_item_images')
                ->where('`item_id` = \'{1}\'', $row->id)
                ->order('priority', 'asc')
                ->execute();
      
      // Get priorities
      $priorities = array();
      while($imageLink = $getImages->next())
      {
        // Priority already exists?
        $priority = $imageLink->priority;
        while(in_array($priority, $priorities))
        {
          $priority ++;  
        }
        
        $priorities[$imageLink->image_id] = $priority;
      }
      
      $getImages->rewind();
      
      $imagesHash = $getImages->getInHash('image_id'); 
               
      // Load all images
      $images = $parent->db->query();
      $images->select('*', 'bf_images')
             ->whereInHash($imagesHash)
             ->execute();
             
      // Memorize images as array
      $imgs = array();
      while($image = $images->next())
      {
        $imgs[$image->id] = $image;
      }
      
      // Prioritize images
      asort($priorities);
      
      // Cache all images
      $index = 0;
      $urls = array();
      
      foreach($priorities as $imgID => $priority)
      {
        // Build path
        $path = 'item_' . $row->id . '.image_' . $index . '.' . $size;
        
        // Set
        $parent->cache->addValue($path, $imgs[$imgID]->url, 7200);
        
        // Add URL to list
        $urls[] = $imgs[$imgID]->url;
        
        $index ++;
      }
  
      // Missing image?
      if($index == 0)
      {
        // Set default image and cache
        $urls[0] = $parent->config->get('com.b2bfront.site.default-image', true);
        $parent->cache->addValue('item_' . $row->id . '.image_0' . '.' . $size, $urls[0], 7200);
      }
      
      return Tools::getImageThumbnail($urls[0], $size);
    };
  
    // Parent property
    $this->parent = $parent;
  }
}
?>