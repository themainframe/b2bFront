<?php
/** 
 * View Class
 * Represents a viewport for content.
 * Provides simple iteration and replacement of template "tags".
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class View extends Base
{
  /**
   * The name of this view
   * @var string
   */
  public $name = '';

  /**
   * The actual view contents
   * @var string
   */
  private $viewTemplate = '';
  
  /**
   * The values to be applied
   * @var array
   */
  private $values = array();
  
  /**
   * Reveal subviews by wrapping them in a <div />
   * The <div /> will be given the CSS class "subview"
   * @var boolean
   */
  private $revealSubviews = false;
  
  /**
   * The requirement of the view to be rendered prior to output.
   * @var boolean
   */
  private $requiresRendering = true;
  
  /**
   * Enable smart template tags mode
   * This hides template tags inside <!-- .. --> to prevent them being revealed
   * due to poorly-coded model files
   * @var boolean
   */
  private $smartTags = false;
  
  /**
   * The number of decimal places to use for doubles by default.
   * @var integer
   */
  private $doublePrecision = 2;
  
  /**
   * Create a new view object
   * @param string $viewName The name of the view to be loaded.
   * @param BFClass* $parent A reference to the parent object.
   * @param boolean $skipRendering Optionally skip rendering of the view when requested.
   * @return View
   */
  function __construct($viewName, $parent, $skipRendering = false)
  {
    // Set parent
    $this->parent = $parent;
    
    // Set name
    $this->name = $viewName;
    
    // Does the view need rendering
    $this->requiresRendering = !$skipRendering;
    
    // Load data from config
    $this->smartTags = 
      $this->parent->config->get('com.b2bfront.site.smart-tags', true);
    $this->doublePrecision = 
      $this->parent->config->get('com.b2bfront.site.double-precision', true);
    $this->revealSubviews = 
      $this->parent->config->get('com.b2bfront.site.reveal-subviews', true);
      
    // Get skin name
    $skinName = $this->parent->config->get('com.b2bfront.site.skin', true) ;
    
    if(!$skinName || !Tools::exists(BF_ROOT . '/skins/' . $skinName . '.skin'))
    {
    	$skinName = 'default';
    }
    
    // Is view name already a direct path?
    if(file_exists($viewName))
    {
      // OK already
      $viewPath = $viewName;
    }
    else
    {    
      // Build path
      $viewPath = BF_ROOT . 'skins/' . 
                  $skinName . 
                  '.skin/views/' . $viewName . '.view.php';
    }
        
    if(file_exists($viewPath))
    {
      // Enable collection of output
      ob_start();
      
      // Read view file
      include $viewPath;
      
      //Finish reading output
      $this->viewTemplate = ob_get_clean();
    }
    else
    {
      throw new Exception("Unable to find view: " . $viewName);
    }
  }
  
  /**
   * Assign an array of values to the view
   * $values should be an associative array of string => mixed.
   * @param array $values Optionally the values passed to apply to the view.
   * @return boolean
   */
  public function assign($values = array())
  {
    // Add each value
    foreach($values as $key => $value)
    {
      $this->values[$key] = $value;
    }
    
    return true;
  }
  
  /**
   * Causes the view to render iteself
   * @return boolean
   */
  public function render()
  {
    if($this->requiresRendering)
    {
      // Plugin event:
      $this->values = 
        $this->parent->pluginServer->viewWillRender(& $this->parent, $this->name, $this->values);
    
      // Parse the whole text
      $this->viewTemplate = $this->parse($this->viewTemplate);
      
      // Done
      $this->parent->pluginServer->viewDidRender(& $this->parent, $this->name);
    }
    
    return true;
  }
  
  /**
   * Parse text for tags and iteration
   * @param string $text The text to parse.
   * @return string
   */
  private function parse($text)
  {
    // Parse iteration ({Each}..{/Each})
    $callback = array($this, 'iterationCallback');
    
    foreach($this->values as $key => $value)
    {
      // Check the type
      if(!is_array($value))
      {
        // Only allow array types here.
        continue;
      }

      $text = preg_replace_callback('/{Each:\s*(' . $key . ')\s+as\s+([\d\w.]+)}(.+?)' . 
                                    '{\/Each: ' . $key . '}/s',
                                    $callback, $text);
    }
  
    // First search for simple values
    $callback = array($this, 'simpleValueCallback');
    
    // Define the tag format to use for smart tags
    $smartTagPrefix = $this->smartTags ? '<!--\s*' : '';
    $smartTagSuffix = $this->smartTags ? '\s*-->' : '';
    
    // Parse simple values
    $text = preg_replace_callback('/' . $smartTagPrefix . '{(QRCode|String|Integer|Decimal):\s(' . 
                                  '[\d\w.\'\s\-]+)((\s[\d\w]+=[\d\w.]+)*)}' . $smartTagSuffix . '/',
                                  $callback, $text);
  
    
    // Load any special tags that are undefinable by the model
    $text = preg_replace_callback('/' . $smartTagPrefix . '{(Subview):\s(' . 
                                  '[\d\w]+)((\s[\d\w]+=[\d\w.]+)*)}' . $smartTagSuffix . '/',
                                  $callback, $text);

    // Search for conditionals
    $callback = array($this, 'conditionalCallback');
    $text = preg_replace_callback('/{If:\s*(([\w\'\d.\-]+)\s*(==|>|<|>=|<=|in|%|<>)\s*([\w\'\d.\-]+))}(.+?)' . 
                                  '{\/If:\s*\\1}/s',
                                  $callback, $text);
  
    
    return $text;
  }
   
  
  /**
   * Automatically replace simple values
   * Array anatomy of $matches:
   *
   *   [0] => The entire match (Including smart tags if enabled)
   *   [1] => The type of value (String, Integer, Decimal...)
   *   [2] => The key name (As passed to $this->values)
   *   [3] => The list of attributes (See below)
   *
   * The format of [3] is
   *
   *   attrib1=value1 attrib2=value2...
   *
   * Parsed to associative (string => string) array by View::parseAttributes()
   *
   * @param array $matches The matches triggered by the render() method
   * @return string
   */
  private function simpleValueCallback($matches)
  {
    // Get the type of value
    $valueType = $matches[1];
    
    // Get the attribute list if any
    $attributes = isset($matches[3]) ? $this->parseAttributes($matches[3]) : array();
    
    // Retrieve the value from the values array
    $value = $this->getLiteral($matches[2]);
    
    switch($valueType)
    {
      case 'Subview':
        
        // Reset value
        $value = $matches[2];
      
	    // Get skin name
	    $skinName = $this->parent->config->get('com.b2bfront.site.skin', true) ;
	    
	    if(!$skinName || !Tools::exists(BF_ROOT . '/skins/' . $skinName . '.skin'))
	    {
	    	$skinName = 'default';
	    }
      
        // Load a subview into this view.
        $viewPath = BF_ROOT . 'skins/' . 
                    $skinName . 
                    '.skin/views/' . $value . '.view.php';
    
        if(file_exists($viewPath))
        {
          // Enable collection of output
          ob_start();
          
          // Read view file
          include $viewPath;
          
          // Parse?
          $subviewText = $attributes['static'] ?  ob_get_clean() : $this->parse(ob_get_clean());
          
          //Finish reading output
          $value = $this->wrapSubview($subviewText, $value);
        }
        else
        {
          throw new Exception('View: ' . $this->name . ': Unable to find subview file: ' . $value);
        }

        break;
    
      case 'String':
        
        $value = strval($value);
        
        if($attributes['limit'])
        {
          // Check for a 'continues' value
          $continues = $attributes['continues'] && strlen($value) > $attributes['limit']
                       ? $attributes['continues'] : '';
          
          // Limit the maximum length
          $value = substr($value, 0, intval($attributes['limit'])) . $continues;
        }
        
        if($attributes['lcase'])
        {
          // Lowercase version
          $value = strtolower($value);
        }
        
        break;
        
      case 'QRCode':
        
        // Create QR Code if possible
        if(!include_once(BF_ROOT . '/libraries/phpqrcode/qrlib.php'))
        {
          // Failed, output nothing:
          $value = '';
        }
        else
        {
          // Create QR
          try 
          {
            $location = Tools::randomPath(BF_ROOT . '/temp/', 'png', 'qrc');
            QRcode::png($value, $location, 'L', 4, 2);
            
            // Get URL
            $QRCodeURL = $this->parent->config->get('com.b2bfront.site.url', true) . 
              '/' . Tools::relativePath($location);
            
            // Replace
            $value = '<img src="' . $QRCodeURL . '" />';
          
            // Set TTL
            $this->parent->setFileTTL(Tools::relativePath($location), 10);
          }
          catch(Exception $exception)
          {
            $this->parent->log('QRCode', 'Failed to generate QR Code using phpqrcode library.');
          }
        }
      
        
        break;
        
      case 'Integer':
      
        // Cast to integer
        $value = intval($value);
        
        // Any offset?
        if($attributes['offset'])
        {
          $value += intval($attributes['offset']);
        }
        
        break;
        
      case 'Decimal':
      
        // Check for dps attribute
        $dp = $attributes['dps'] ? intval($attributes['dps']) : $this->doublePrecision;
        
        // Cast to decimal
        $value = number_format(doubleval($value), $dp, '.', '');
        
        break;
    }
    
    return $value;
  }
 
  /**
   * Shows or Hides conditional blocks
   * Array anatomy of $matches:
   *
   *   [0] => The entire match (Including smart tags if enabled)
   *   [1] => The whole condition
   *   [2] => The LHS of the condition
   *   [3] => The comparator
   *   [4] => The RHS of the condition
   *   [5] => The content of the block
   *
   * @param array $matches The matches triggered by the render() method
   * @return string
   */
  private function conditionalCallback($matches)
  {
    // Get literal values
    $LHS = $this->getLiteral($matches[2]);
    $RHS = $this->getLiteral($matches[4]);

    // Get the (parsed) content
    $content = $this->parse($matches[5]);
    
    // Compare
    switch($matches[3])
    {
      case '==':
      
        if($LHS == $RHS)
        {
          return $content;
        }
        
        break;

      case '<':
      
        if($LHS < $RHS)
        {
          return $content;
        }
        
        break;
      
      case '>':
      
        if($LHS > $RHS)
        {
          return $content;
        }
        
        break;
      
      case '>=':
      
        if($LHS >= $RHS)
        {
          return $content;
        }
        
        break;
        
      case '<>':
      
        if($LHS != $RHS)
        {
          return $content;
        }
        
        break;
      
      case '<=':
      
        if($LHS <= $RHS)
        {
          return $content;
        }
        
        break;
      
      case 'in':
      
        if(strpos($RHS, $LHS) !== false)
        {
          return $content;
        }
        
        break;
        
      case '%':
      
        if($LHS % $RHS == 0)
        {
          return $content;
        }
        
        break;
    }
    
    return '';
  }
  
  /**
   * Convert a string to a literal based on the current value table
   * @param string $text The text to convert
   * @return mixed
   */
  private function getLiteral($text)
  {
    // Check if it is a string
    if($text[0] == '\'' && $text[strlen($text)-1] == '\'')
    {
      return substr($text, 1, strlen($text) - 2);
    }
    
    // Check if it a numeric value
    if(is_numeric($text))
    {
      return $text;
    }
    
    // Check if it is a value
    if(array_key_exists($text, $this->values))
    {
      return $this->values[$text];
    }
    
    // Empty text?
    if($text == 'empty')
    {
      return '';
    }
    
    // Otherwise return 1 as a comparator
    // This is true by default
    return '';
  }
  
  /**
   * Replaces iteration
   * Array anatomy of $matches:
   *
   *   [0] => The entire match (Including smart tags if enabled)
   *   [1] => The key name (As passed to $this->values)
   *   [2] => The local iteration variable name (loop control)
   *   [3] => The text to repeat containing values.
   *
   * @param array $matches The matches triggered by the render() method
   * @return string
   */
  private function iterationCallback($matches)
  {
    // Verify the key name is an array
    if(!isset($this->values[$matches[1]]) || !is_array($this->values[$matches[1]]))
    {
      // Log this
      $this->parent->log('View: ' . $this->name . ' - No suitable data for iteration: ' . $matches[1]);
      return '';
    }
    
    // Obtain the data
    $data = $this->values[$matches[1]];
    $nameSpace = $matches[2];
    $staticText = $matches[3];
    
    // Collect output
    $output = '';
    
    // Always reset values after parsing to clean up the namespace
    $valueCopy = $this->values;
    
    // Set up increment
    $this->values[$nameSpace . '.index'] = 0;
    
    // Loop over the data
    foreach($data as $row)
    {
      if(is_array($row))
      {
        // For every column, update the global namespace
        foreach($row as $columnKey => $columnValue)
        {
          $this->values[$nameSpace . '.' . $columnKey] = $columnValue;
        }       
      }
      else
      {
        // Just set the single value
        $this->values[$nameSpace] = $row;
      }
      
      // Also add an increment accessor
      $this->values[$nameSpace . '.index'] ++;
      
      // Parse the text and buffer output.
      $output .= $this->parse($staticText); 
    }
    
    // Restore namespace
    $this->values = $valueCopy;
    
    return $output;
  }

  /**
   * Read an attribute string and convert the values to an associative array
   * of string => string.
   * @param string $attributes The attribute string
   * @return array
   */
  private function parseAttributes($attributes)
  {
    // Split by space character
    $attributeSet = preg_split('/([\s])/', $attributes);
    
    // Parse to array
    $attributeArray = array();
    foreach($attributeSet as $attribute)
    {
      // Split by Equals
      $attributePair = preg_split('/(=)/', $attribute);
      $attributeArray[$attributePair[0]] = $attributePair[1];
    }
    
    return $attributeArray;
  }
  
  /**
   * Produce the <div /> wrapping for a subview if subview revealing is on.
   * @param string $subviewText The text in the subview.
   * @param string $subviewName Optionally the name of the subview.
   * @return string
   */
  private function wrapSubview($subviewText, $subviewName = 'Subview')
  {
    if(!$this->revealSubviews)
    {
      // No changes
      return $subviewText;
    }
    
    return '<div class="subview" title="' . $subviewName . '">' . $subviewText . '</div>';
  }
  
  /**
   * Cast the instance to a string
   * @return string
   */
  public function __toString()
  {
    return $this->viewTemplate;
  } 
  
}
?>