<?php
/**
 * DataColumn
 * Represents a single column in a DataTable object.
 *
 * Changes: Dynamic Method Invocation style replaces previous single-method
 *          execution style, improved flexibility and readability.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 2.0
 * @author Damien Walsh
 */
class DataColumn extends Configurable
{
  /**
   * The DataTable object this belongs to.
   * @var DataTable
   */
  protected $dataTable = null;

  /**
   * The name for this column to match in the data result
   * @var string
   */
  protected $dataName = '';
  
  /**
   * The name this column will be displayed with
   * @var string
   */
  protected $niceName = '';
  
  /**
   * Predefined static content
   * @var string
   */
  protected $content = '';
  
  /**
   * Column Index
   * @var integer
   */
  protected $columnIndex = -1;
  
  /**
   * Row Index (Cardinality)
   * @var integer
   */
  protected $rowIndex = -1;
  
  /**
   * Any CSS to be applied to the column's <td /> element.
   * @var array
   */
  protected $css = array();
  
  /**
   * The "true" checkmark to use for boolean formatting
   * @var string
   */
  private $checkmarkTrue = '<span style="color:#149517">Yes</span>';
 
  /**
   * The "false" checkmark to use for boolean formatting
   * @var string
   */
  private $checkmarkFalse = '<span style="color:#951414">No</span>';
  
  /**
   * Create a new DataTable Column
   * @param DataTable $dataTable A ref to the DataTable that this column belongs to.
   * @param string $dataName The name of the column in the data result this column represents.
   * @param string $niceName The nice name for the column to be displayed.
   * @param array $css Optionally any inline CSS attributes to apply as an associative array.
   * @param BFClass* $parent Optionally a parent object
   * @return DataColumn
   */
  public function __construct($dataTable, $dataName, $niceName, $css = array(),
    $parent = null, $columnIndex = -1)
  {
    // Save arguments
    $this->dataTable = & $dataTable;
    $this->dataName = $dataName;
    $this->niceName = $niceName;
    $this->css = $css;
    $this->parent = $parent;
    $this->columnIndex = $columnIndex;
  }
  
  /**
   * Render the data column header cell.
   * $orderDirection is the direction by which the column is CURRENTLY ordered.
   * Note: $orderDirection should be assigned either 'a' (ASC), 'd' (DESC) or ''.  Default 'd'.
   * @param string $columnID The identifier for the column in HTML and URLs.
   * @param string $orderDirection Optionally the direction by which this column is ordered.
   * @return string
   */
  public function renderHeader($columnID, $orderDirection = '')
  {
    // Create output
    $output = '      <td';
   

    
    // Style?
    if(!empty($this->css))
    {
      $output .= ' style="';
      foreach($this->css as $attribute => $value)
      {
        $output .= $attribute . ': ' . $value . '; ';
      }
      $output .= '"';
    }
    
    // Create a new ordering direction
    if($orderDirection == '' || $orderDirection == 'd')
    {
      $newOrderDirection = 'a';
    }
    else
    {
      $newOrderDirection = 'd';
    }
    
    // Create the CSS class for the anchor
    switch($orderDirection)
    {
      case 'a':
        $anchorClass = ' class="asc"';
        break;
        
      case 'd':
        $anchorClass = ' class="desc"';
        break;
        
      default:
        $anchorClass = '';
        break;
    }   
    
    // Generate a URL to order this column
    $modifiedURL = Tools::getModifiedURL(array(
                                          $this->dataTable->get_name() . '_order' => (string) $columnID,
                                          $this->dataTable->get_name() . '_order_d' => $newOrderDirection
                                        ));
    
    $output .= '>' . "\n";
    
    // Editable
    if($this->getOption('editable') || $this->getOption('virtualEditable'))
    {
      // Add the editable pencil
      $output .= '<img src="/acp/static/icon/pencil-tiny.png" style="margin-right: 5px;" /> ' . "\n";
    }
    
    
    // Is this column checkbox or content/variable ?
    if($this->getOption('formatAsCheckbox'))
    {
      // Write a checkbox and JS to autocheck all.
      $output .= '        <input type="checkbox" value="1" class="' . 
                 $this->dataTable->get_name() . '_all" />' . "\n";
      $this->dataTable->preText  = '        <script type="text/javascript">' . "\n";
      $this->dataTable->preText .= '          $(function() {' . "\n";
      $this->dataTable->preText .= '          $("input.' . $this->dataTable->get_name() . 
                                   '_all").live(\'click\', function() {' . "\n";
      $this->dataTable->preText .= '              if($(this).attr("checked")) {' . "\n";
      $this->dataTable->preText .= '               $("input.dt_cb_' . 
                                   $this->dataTable->get_name() . '").attr("checked", true);' . "\n";
      $this->dataTable->preText .= '              } else {' . "\n";
      $this->dataTable->preText .= '               $("input.dt_cb_' . 
                                   $this->dataTable->get_name() . '").attr("checked", false);' . "\n";
      $this->dataTable->preText .= '              }' . "\n";                 
      $this->dataTable->preText .= '            });' . "\n";
      $this->dataTable->preText .= '          });' . "\n";
      $this->dataTable->preText .= '        </script>' . "\n";
    }
    else
    {
      // Do not offer ordering capability if this is a static content column
      if(empty($this->content) && !$this->getOption('fixedOrder'))
      {
        $output .= '        <a href="' . $modifiedURL . '"' . $anchorClass . '>' . 
                   $this->niceName . '</a>' . "\n";
      }
      else
      {
        $output .= '        ' . $this->niceName . "\n";
      }
    }



    
    $output .= '      </td>' . "\n";
    
    return $output;
  }
  
  /**
   * Execute a callback to modify the value of a cell in this column first.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function callback($value, $resultRow = null, $configValue = null)
  {
    // Run the function to update the value
    if(is_callable($configValue))
    {
      // Call the function, passing the current row
      $value = $configValue($resultRow, & $this->parent, $value, $this->columnIndex);
    }
    
    return $value;
  }

  /**
   * Convert the contents of the cell to the cardinality of the row
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function cardinality($value, $resultRow = null, $configValue = null)
  {
    // Set value
    return $this->dataTable->currentRow;
  }
  
  /**
   * Directly modify the content of the cell
   * Old contents can be accessed as {old} for wrapping purposes.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function newContent($value, $resultRow = null, $configValue = null)
  {
    return $this->content($resultRow, str_replace('{old}', $value, $configValue));
  }
  
  /**
   * Format the contents of the cell as a price
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsPrice($value, $resultRow = null, $configValue = null)
  {
    return number_format($value, 2, '.', '');
  }  

  /**
   * Format the contents of the cell as the size of the file referred to by the current value
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsFileSize($value, $resultRow = null, $configValue = null)
  {
    return ceil((filesize(BF_ROOT . '/' . $value) / 1024)) . ' KB';
  }  

  /**
   * Convert the value of the cell to the string length of the previous value.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsStringLength($value, $resultRow = null, $configValue = null)
  {
    return strlen($value);
  }  

  /**
   * Truncate the value of the cell to a maximum defined string length.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatMaxLength($value, $resultRow = null, $configValue = null)
  {
    return Tools::truncate($value, intval($configValue));
  }  

  /**
   * Format the value of the cell as a date, optionally using a defined format.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsDate($value, $resultRow = null, $configValue = null)
  {
    // Defined format mode?
    if(!$this->getOption('formatAsDateFormat'))
    {
      // Use default
      $dateFormat = 'M j, Y, g:i a';
    }
    else
    {
      $dateFormat = $this->getOption('formatAsDateFormat');
    }
  
    return date($dateFormat, $value);
  }  
  
  /**
   * Format the value of the cell as a boolean checkmark (tick or cross).
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsBoolean($value, $resultRow = null, $configValue = null)
  {
    return ($value ? $this->checkmarkTrue : $this->checkmarkFalse);
  }  
  
  /**
   * Format the value of the cell as a defined toggle image.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsToggleImage($value, $resultRow = null, $configValue = null)
  {
    return '         <img class="middle" src="' . 
           $this->getOption('toggleImage' . ($value ? 'True' : 'False')) . 
           '" title="' . $this->getOption('toggleImage' . ($value ? 'True' : 'False') . 'Title') . 
           '" alt="Image" />';
  }
  
  /**
   * Format the value of the cell as a defined toggle string.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsToggleText($value, $resultRow = null, $configValue = null)
  {
    return '         ' . $this->getOption('toggleText' . ($value ? 'True' : 'False'));
  }   

  /**
   * Format the value of the cell as a HTML checkbox input element.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsCheckbox($value, $resultRow = null, $configValue = null)
  {
    // Unchecked by default
    $checkedOption = '';
  
    // Do any rules govern the checking of the box?
    if(is_array($this->getOption('checkIfIn')))
    {
      // Yes
      if(in_array($value, $this->getOption('checkIfIn')))
      {
        $checkedOption = ' checked="checked"';
      }
    }
  
    return  '        <input' . $checkedOption . ' value="1" type="checkbox" id="' . 
            $this->dataTable->get_name() . 
            '_' . $value . '" name="' . $this->dataTable->get_name() . 
            '_' . $value . '" class="dt_cb_' . $this->dataTable->get_name() . '" />';
  } 
  
  /**
   * Format the value of the cell as a HTML image element.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsImage($value, $resultRow = null, $configValue = null)
  {
    return '         <a class="thumb" rel="' . str_replace('-lst', '-lrg', $value) . 
      '"><img class="thumb" class="middle data_image" src="' . 
      $value . '" alt="Image" /></a>';
  }  

  /**
   * Format the value of the cell as a positive or negative coloured value.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsPosNeg($value, $resultRow = null, $configValue = null)
  {
    // Wrap a span around $value depending on value
    
    if($value > 0)
    {
      $value = '         <span style="color: #489d42">' . ($this->getOption('hidePosNegPlus') ? '' : '+') . $value . '</span>'; 
    }

    if($value < 0)
    {
      $value = '         <span style="color: #e43939">' . $value . '</span>'; 
    }

    if($value == 0)
    {
      $value = '         <span style="color: #000">' . $value . '</span>'; 
    }
  
    return $value;
  }  
  
  /**
   * Format the value of the cell as an image thumbnail; suffix specified by the option value.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsImageThumbnail($value, $resultRow = null, $configValue = null)
  {
    // Set $value as image thumbnail conversion
    $value = Tools::getImageThumbnail($value, $configValue);
    
    return $value;
  }  

  /**
   * Format the value of the cell as a positive or negative percentage coloured value.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsPosNegPercentage($value, $resultRow = null, $configValue = null)
  {
    // Wrap a span around $value depending on value and convert to percentage
    $value = number_format($value, 2);
  
    // Wrap a span depending on value
    if($value > 100.00)
    {
      $newValue = '         <span style="color: #489d42">+' . $value . '&#37;</span>'; 
    }

    if($value < 100.00)
    {
      $newValue = '         <span style="color: #e43939">-' . $value . '&#37;</span>'; 
    }

    if($value == 100.00)
    {
      $newValue = '         <span style="color: #000">' . $value . '&#37;</span>'; 
    }
      
    return $newValue;
  } 

  /**
   * Format the value of the cell as a HTML anchor element.
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $configValue Optionally the configuration value of the callee.
   * @return string
   */
  private function formatAsLink($value, $resultRow = null, $configValue = null)
  {
    // linkURL and a reference to a result row object are required
    if(!$this->getOption('linkURL') || !$resultRow)
    {
      // Failed to use formatAsLink, return as-is
      return $value;
    }
    
    // Get Link text and replace out fields
    $linkText = urldecode($this->getOption('linkURL'));
    foreach($resultRow as $fieldName => $fieldValue)
    {
      $linkText = str_replace('{' . $fieldName . '}', $fieldValue, $linkText);
      
      // Replace root path with site URL
      $linkText = str_replace(BF_ROOT, 
        $this->parent->config->get('com.b2bfront.site.url', true), $linkText);
    }
    
    return  '        <a ' . 
            ($this->getOption('linkNewWindow') ? 'class="new" target="_blank" ' : '') . 
            'href="' . $linkText . '">' . $value . '</a>';
  } 
  
  /**
   * Proxy a value and modify it as required for this column
   * @param mixed $value The value to modify and return.
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @return string
   */
  public function value($value, $resultRow = null)
  {
    // Check for "executable" options
    foreach($this->configuration as $key => $data)
    {
      if(method_exists($this, $key))
      {
        $value = $this->{$key}($value, $resultRow, $data);
      }
    }

    // Clear Field?
    if($this->getOption('clearField') != '' && 
       $resultRow->{$this->getOption('clearField')} == '1')
    {
      return '<td></td>';
    }
    
    // Replace Field?
    if($this->getOption('replaceFieldIf') != '' && 
       $resultRow->{$this->getOption('replaceFieldIf')} == '1')
    {
      if(isset($resultRow->{$this->getOption('replaceFieldWith')}))
      {
        // Other field
        return '<td>' . $resultRow->{$this->getOption('replaceFieldWith')} . '</td>';
      }
      else
      {
        // Direct content
        return '<td>' . $this->content($resultRow, $this->getOption('replaceFieldWith')) . '</td>';
      }
    }   
    
    // Build output HTML for the cell
    $output = '      <td';
    
    // Style?
    if($this->getOption('cellCss'))
    {
      $output .= ' style="';
      $cssArray = $this->getOption('cellCss');
      
      foreach($cssArray as $cssAttribute => $cssValue)
      {
        $output .= $cssAttribute . ': ' . $cssValue . '; ';
      }
      $output .= '">' . "\n";
    }
    else
    {
      $output .= '>' . "\n";
    }
    
    if($this->getOption('editable'))
    {
      //
      // A run-down of what exactly the attributes used for editables are:
      //
      //    table           The table name
      //    rowid           The row ID (`id`) column to use as the key
      //    field           The field to update
      //    cache           A cache level ID to increment if not empty
      //    empty           The behaviour to exhibit if the field is edited to be empty
      //
    
      $output .= '<span id="' . $this->dataTable->get_name() . '_' . $this->dataName . '_' . $resultRow->id .
                 '" table="' . $this->getOption('editableTable') . '" rowid="' . $resultRow->id . 
                 '" field="' . $this->dataName . '" cache="' . $this->getOption('editableCache') . '" empty="' . $this->getOption('editableEmptyAction') . 
                 '" unselectable="on" class="editable ' . ($this->getOption('editable_cb') ? 'editable_cb' :'') . '">' . 
                 
                 ($this->getOption('editable_cb') ? ('<input type="checkbox" value="1" ' .
                     ($value ? 'checked="checked"' : '') . 
                  ' />') : $value)
                 
                  . '</span>';
    }
    else
    {
      // Standard
      $output .= $value;
    }
        
    // Done all modifications (if any)
    return $output . '</td>' . "\n";
  }
  
  /**
   * Provide static non-databound content
   * @param object $resultRow Optionally a stdClass object with public string properties.
   * @param string $replacementContent Optionally alternative content to use.
   * @return string
   */
  public function content($resultRow = array(), $replacementContent = null)
  {
    // Use correct content source
    $content = (isset($replacementContent) ? $replacementContent : $this->content);
    
    // Replace in fields
    foreach($resultRow as $fieldName => $fieldValue)
    {
      $content = str_replace('{' . $fieldName . '}', $fieldValue, $content);
    }
  
    return $content;
  }
}
?>