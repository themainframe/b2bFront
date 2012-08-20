<?php
/**
 * FormListBuilder
 * Renders a HTML UI part to create/edit a list of strings.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class FormListBuilder extends Configurable
{
  /**
   * The name of this form input object
   * @var string
   */
  protected $name = '';
  
  /**
   * A list of strings being edited initially
   * @var array
   */
  protected $list = array();

  /**
   * Create a new FormListBuilder UI Part
   * @param string $inputName The name of the hidden input element to generate.
   * @param array $initialList Optionally an array of strings to start with.
   * @param BFClass $parent Optionally A reference to the parent object.
   * @return FormListBuilder
   */
  public function __construct($inputName, $initialList = array(), $parent = null)
  {
    // Set properties
    $this->list = $initialList;
    $this->name = $inputName;
    
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
    $this->setOption('listDescription', 'List');  // Describes the whole list
    $this->setOption('valueDescription', 'Add:');  // Describes a single value
    $this->setOption('hintValue', 'Type a new value');  // A hint for the user to enter a new item.
    $this->setOption('emptyList', 'There are no items in this view.'); // Indicates an empty list.
    
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
    $output .= '    flb_setup(\'' . $this->name . '\');' . "\n";
    $output .= '  });' . "\n";
    $output .= '</script>' . "\n";
        
    // Create a hidden textarea
    $output .= '<textarea id="flb_' . $this->name . '_text" name="' . $this->name .
               '" style="display:none;">';
        
    // Write existing values
    foreach($this->list as $listItem)
    {
      $output .= $listItem . "\n";
    }   
            
    $output .= '</textarea>' . "\n";
    
    // Create the interface
    $output .= '<div class="formlistbuilder">' . "\n";
    $output .= '  <table style="width:100%;">' . "\n";
    $output .= '    <tbody>' . "\n";
    $output .= '      <tr>' . "\n";
    $output .= '        <td>' . "\n";
    $output .= '           &nbsp;' . $this->getOption('valueDescription') . "\n";
    $output .= '          <input class="flb_new" type="text" id="flb_' . $this->name . '_new" /> &nbsp;' . "\n";
    $output .= '          <input onclick="flb_addValue(\'' . $this->name . '\')" type="button" id="' . 
               $this->name . '_do_new" class="submit" value="Add" /> &nbsp;' . "\n";
    $output .= '        </td>' . "\n";
    $output .= '      </tr>' . "\n";
    $output .= '      <tr>' . "\n";
    $output .= '        <td>' . "\n";
    $output .= '          <table class="data">' . "\n";
    $output .= '            <thead>' . "\n";
    $output .= '              <tr class="header">' . "\n";
    $output .= '                <td style="padding: 8px 0px 0px 8px">' . $this->getOption('listDescription') . '</td>' . "\n";
    $output .= '             </tr>' . "\n";
    $output .= '            </thead>' . "\n";
    $output .= '            <tbody id="flb_' . $this->name . '_rows">' . "\n";
    
    // Output each of the preloaded list items
    foreach($this->list as $listItem)
    {
      $output .= '              <tr>' . "\n";
      $output .= '                <td>' . "\n";
      $output .= '                  <a class="tool flb_remove" title="Remove" rel="' . 
                 $this->name . '_' . $listItem . '">' . "\n";
      $output .= '                    <img src="/acp/static/icon/cross-circle.png" alt="Remove" /> &nbsp; Remove' . "\n";
      $output .= '                  </a>' . "\n";
      $output .= '                  ' . $listItem . "\n";
      $output .= '                </td>' . "\n";
      $output .= '              </tr>' . "\n";
    }
    
    $output .= '            </tbody>' . "\n";
    $output .= '          </table>' . "\n";
  
    $output .= '<div style="' . ($this->list ? 'display:none;' : '') . '" class="no_data" id="flb_' .
               $this->name . '_no_data">' . $this->getOption('emptyList') . '</div>' . "\n";
    
    $output .= '        </td>' . "\n";
    $output .= '      </tr>' . "\n";    
    $output .= '    </tbody>' . "\n";   
    $output .= '  </table>' . "\n";   
    $output .= '</div>' . "\n";  
    
     
    // Output
    return $output;
  }
}
?>