<?php
/**
 * DataDropDown
 * Represents a DropDown UI component to choose a single row.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class DataDropDown extends Configurable
{
  /**
   * The name of the object's rendered <select /> element.
   * @var string
   */
  protected $name = '';
  
  /**
   * The query object associated with the DataDropDown
   * @var Query
   */
  protected $query = null;
  
  /**
   * The field to be used as the value for the <select /> element.
   * @var string
   */
  protected $value = '';
  
  /**
   * The field to be used as the text for the <select /> element.
   * @var string
   */
  protected $text = '';

  /**
   * Preset options that are always displayed first
   * @var array
   */
  protected $presets = '';

  /**
   * Create a new DataDropDown object using a completed Query object.
   * @param string $name The name of the <select /> object in HTML to be created.
   * @param Query $query The finished query object
   * @param string $value The result field to use as the value.
   * @param string $text The result field to use as the text.
   * @param array $presets Optionally any preset options as an associative array.
   * @return DataDropDown
   */
  public function __construct($name, $query, $value, $text, $presets = array())
  {
    // Check the query object is completed
    if(!$query->get_result())
    {
      throw new Exception('Attempting to create UI part with an incomplete Query.');
    }
    
    // Check presets
    if(!array($presets))
    {
      $presets = array();
    }
    
    // Set properties
    $this->name = $name;
    $this->query = & $query;
    $this->value = $value;
    $this->text = $text;
    $this->presets = $presets;
  }
  
  /**
   * Render the DataDropDown object
   * @return string
   */
  public function render()
  {
    // Start collecting output
    $output  = "\n";
    $output .= '<select style="' . $this->getStyle() . '" id="dd_' . $this->name .
               '" name="' . $this->name . '" class="data_dropdown">' . "\n";
    
    // Is there a default selection?
    $defaultSelection = false;
    if($this->getOption('defaultSelection'))
    {
      $defaultSelection = $this->getOption('defaultSelection');
    }
    
    // Presets
    foreach($this->presets as $optionValue => $optionText)
    {
      if($this->getOption('maxTextLength'))
      {
        if(strlen($optionText) > $this->getOption('maxTextLength'))
        {
          $optionText = substr($optionText, 0, $this->getOption('maxTextLength')) . '...';
        }
      }
      
      $output .= '  <option' . ($defaultSelection === $optionValue ? ' selected="selected"' : ' ') . 
                 'value="' . $optionValue . '">' . $optionText . '</option>' . "\n";
    }
    
    // Data
    while($row = $this->query->next())
    {
      // Retrieve the fields specified to use in the HTML
      $optionValue = (isset($row->{$this->value}) ? $row->{$this->value} : '');
      $optionText = (isset($row->{$this->text}) ? $row->{$this->text} : '');
      
      if($this->getOption('maxTextLength'))
      {
        if(strlen($optionText) > $this->getOption('maxTextLength'))
        {
          $optionText = substr($optionText, 0, $this->getOption('maxTextLength')) . '...';
        }
      }
      
      $output .= '  <option' . ($defaultSelection === $optionValue ? ' selected="selected"' : ' ') . 
                 'value="' . $optionValue . '">' . $optionText . '</option>' . "\n";
    }
    
    $output .= '</select>' . "\n";
    
    return $output;
  }
  
  /**
   * Get the CSS Style for this object
   * @return string
   */
  private function getStyle()
  {
    $style = '';
    
    // Style?
    $cssArray = $this->getOption('css');
    
    if($cssArray)
    {
      foreach($cssArray as $attribute => $value)
      {
        $style .= $attribute . ': ' . $value . '; ';
      }
    }
    
    return $style;
  }
}
?>