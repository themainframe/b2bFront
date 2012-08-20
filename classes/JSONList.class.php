<?php
/** 
 * JSON List Class
 * Unserialises data stored in .json Property List files.
 *
 * NB: http://en.wikipedia.org/wiki/JSON
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class JSONList extends Base
{
  /**
   * The underlying data
   * @var array
   */
  public $data = array();

  /**
   * Load a property list from a JSON file
   * @param string $path The path to the .json file to load.
   * @return PropertyList
   */
  public function __construct($path)
  {
    // Fail if the file does not exist
    if(!Tools::exists($path))
    {
      return false;
    }
    
    // Get contents
    $jsonText = Tools::getText($path);
    
    // Parse the file into an array
    $this->data = json_decode($jsonText, true);
  }
}
?>