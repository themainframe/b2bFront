<?php
/**
 * FormIconPicker
 * Renders a HTML UI part to choose an icon.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class FormIconPicker extends Configurable
{
  /**
   * The name of this form input object
   * @var string
   */
  protected $name = '';
  
  /**
   * The directory that contains possible icon choices
   * @var string
   */
  protected $path = '/share/icon/';

  /**
   * The currently selected icon
   * @var string
   */
  protected $selected = '';

  /**
   * Create a new FormIconPicker UI Part
   * @param BFClass $parent A reference to the parent object.
   * @param string $inputName The name of the hidden input element to generate.
   * @param array $path Optionally the path to the icons to use.
   * @param string $selected Optionally the currently selected icon.
   * @return FormListBuilder
   */
  public function __construct($parent, $inputName, $path = '', $selected = '')
  {
    // Set properties
    $this->name = $inputName;
    $this->selected = $selected;
    
    // Path set?
    if($path)
    {
      $this->path = $path;
    }
    
    // Defaults
    $this->defaults();
    
    // Parent property
    $this->parent = $parent;
  }
  
  /**
   * Set the default configuration on this object
   * @return boolean
   */
  public function defaults()
  {
    return true;
  }
  
  /**
   * Render the UI part
   * @return string
   */
  public function render()
  {
    // Build HTML
    $output = "\n";
    
    // Scripting
    $output .= '<script type="text/javascript">' . "\n";
    $output .= '  $(function() {' . "\n";
    $output .= '    $("#fip_' . $this->name . ' img").click(function() {' . "\n";
    $output .= '      $("#fip_' . $this->name . ' img").removeClass("selected");' . "\n";
    $output .= '      $(this).addClass("selected");' . "\n";
    $output .= '      $("#fip_' . $this->name . '_i").val($(this).attr("rel"));' . "\n";
    $output .= '    });' . "\n";
    $output .= '    $("#fip_' . $this->name . '").scrollTo(".selected", 1000);';
    $output .= '  });' . "\n";
    $output .= '</script>' . "\n";
        
    // Provide the selection of icons
    $output .= '<div id="fip_' . $this->name . '" class="iconpicker">' . "\n";
    
    // Load path selected
    $iconFiles = Tools::listDirectory($this->path);
    foreach($iconFiles as $icon)
    {
      $output .= '  <img alt="Icon" rel="' . $this->path . $icon . 
                 '" ' . ($this->path . $icon == $this->selected ? 'class="selected"' : '' ) . 
                 ' src="' . $this->path . $icon . '" />' . "\n";
    }
    
    $output .= '</div>' . "\n";
        
    // Create a hidden input element
    $output .= '<input type="hidden" id= "fip_' . $this->name . '_i" name="' . 
               $this->name . '" value="' . $this->selected . '" />' . "\n";
     
    // Output
    return $output;
  }
}
?>