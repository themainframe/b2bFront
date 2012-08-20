<?php
/**
 * FormValue
 * Provides form value validation in a chainable, passive manner.
 * 
 * Example:
 * 
 *    $price = new FormValue('2.22', 'Price');   
 *
 *    if($price->numeric()->min(1)->max(3)->ok())
 *    {
 *      ...
 *    }
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class FormValue extends Base
{
  /**
   * Value
   * @var mixed
   */
  private $value = null;
  
  /**
   * Description
   * @var string
   */
  private $description = '';
  
  /**
   * The internal state of the validator
   * @var boolean
   */
  private $hasFailed = false;
  
  /**
   * True if the field is required
   * @var boolean
   */
  private $required = false;
  
  /**
   * Create a new Form Value with a specified value
   * @param mixed $value The form value
   * @param string $description Optionally a description of what the field represents.
   * @param BFClass $parent Optionally A reference to the parent object.
   * @return FormValue
   */
  public function __construct($value, $description = '', $parent = null)
  {
    // Set properties
    $this->value = $value;
    $this->description = $description;

    // Parent property
    $this->parent = $parent;
  }
  
  /**
   * Produce the string representation of this FormValue object
   * @return string
   */
  public function __toString()
  {
    return $this->value . '';
  }
  
  /**
   * Failed to validate
   * Produce a message indicating the problem with the value
   * @param string $problem Optionally the problem with the value
   * @return boolean
   */
  private function failValidation($problem = 'Completed')
  {
    if($this->description)
    {
      $this->value = $this->description . ' must be ' . $problem . '.';
    }
    else
    {
      $this->value = 'This field must be ' . $problem . '.';
    }
    
    // Mark as failed
    $this->hasFailed = true;
    
    return true;
  }
  
  /**
   * Check if the value is positive
   * @return FormValue
   */
  public function pos()
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'a positive value';
  
    if($this->value < 0)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Check if the value is numeric
   * @return FormValue
   */
  public function numeric()
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'a numeric value';
  
    if(!is_numeric($this->value))
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Fail if the value contains another
   * @param string $needle The value to search for.
   * @return FormValue
   */
  public function doesNotContain($needle)
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'free of \'' . $needle . '\' occurrences';
  
    if(strpos($this->value, $needle) !== false)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Succeed if and only if the value contains another
   * @param string $needle The value to search for.
   * @return FormValue
   */
  public function contains($needle)
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'a superstring of \'' . $needle . '\'';
  
    if(strpos($this->value, $needle) === -1)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Check if the field is actually filled.
   * @return FormValue
   */
  public function done()
  {
    // The requirement for this test to pass
    $requirement = 'completed';
  
    if($this->value == '')
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Check if the field is a valid email address
   * @return FormValue
   */
  public function email()
  {
    // The requirement for this test to pass
    $requirement = ' a valid Email address';
  
    if(!filter_var($this->value, FILTER_VALIDATE_EMAIL))
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Check if the value is longer than or equal to a specified length
   * @param integer $minimumLength The minimum length of the string.
   * @return FormValue
   */
  public function min($minimumLength)
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = $minimumLength . ' characters or more';
  
    if(strlen($this->value) < $minimumLength)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Check if the value parses to a date that is in the future
   * @param string $timeString Optionally a string to append to the vale before testing.
   * @return FormValue
   */
  public function futureDate($timeString = '')
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = ' in the future';
    
    // Parse the date
    $timestamp = strtotime($this->value . ($timeString ? ' ' . $timeString : ''));
    
    if(!$timestamp || $timestamp < time())
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Check if the value is shorter than or equal to a specified length
   * @param integer $maximumLength The minimum length of the string.
   * @return FormValue
   */
  public function max($maximumLength)
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = $maximumLength . ' characters or less';
  
    if(strlen($this->value) > $maximumLength)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /** 
   * Check if the value is unique for a given field in a table.
   * Requires the parent object to be set.
   * @param string $tableName The name of the table.
   * @param string $columnName Optionally the name of the column 'name' by default.
   * @return FormValue
   */
  public function unique($tableName, $columnName = 'name')
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'unique';

    // Parent set?
    if($this->parent == null || !$this->parent)
    {
      $this->failValidation($requirement . ' - Could not verify this');
      return $this;
    }
    
    // Check uniqueness
    $this->parent->db->select($columnName, $tableName)
                     ->where('`{1}` = \'{2}\'', $columnName, $this->value)
                     ->limit(1)
                     ->execute();
     
    if($this->parent->db->count == 1)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }

  /** 
   * Check if the value is only held by one row in the given table
   * Otherwise identical to FormValue::unique()
   * Requires the parent object to be set.
   * @param string $tableName The name of the table.
   * @param string $columnName Optionally the name of the column 'name' by default.
   * @return FormValue
   */
  public function one($tableName, $columnName = 'name')
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'unique';

    // Parent set?
    if($this->parent == null || !$this->parent)
    {
      $this->failValidation($requirement . ' - Could not verify this');
      return $this;
    }
    
    // Check uniqueness
    $this->parent->db->select($columnName, $tableName)
                     ->where('`{1}` = \'{2}\'', $columnName, $this->value)
                     ->execute();
     
    if($this->parent->db->count > 1)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /** 
   * Check if a row id exists for a given table.
   * Requires the parent object to be set.
   * @param string $tableName The name of the table.
   * @return FormValue
   */
  public function exists($tableName)
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'a real object';

    // Parent set?
    if($this->parent == null || !$this->parent)
    {
      $this->failValidation($requirement . ' - Could not verify this');
      return $this;
    }
    
    // Check for existing row
    $this->parent->db->select('id', $tableName)
                     ->where('`id` = \'{1}\'', $this->value)
                     ->limit(1)
                     ->execute();
     
    if($this->parent->db->count < 1)
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Check that the value is a path to a file that exists.
   * Behaves in the same way as Tools::exists() in that the root is BF_ROOT if defined.
   * @return FormValue
   */
  public function fileExists()
  {
    // Failed already?
    if($this->hasFailed)
    {
      return $this;
    }
  
    // The requirement for this test to pass
    $requirement = 'a file that already exists';
    
    if(!Tools::exists($this->value))
    {
      $this->failValidation($requirement);
    }
    
    return $this;
  }
  
  /**
   * Run a batch of validations against the value.
   * The array should be a collection of function names as keys with their arguments stored
   * inside the value as a nonassociative array.
   * May be used in a chain.
   * @param array $validations An associative array of validations to perform.
   * @return FormValue
   */
  public function batch($validations)
  {
    // Execute each validation
    foreach($validations as $testName => $testArguments)
    {
      if(!is_array($testArguments) || !method_exists($this, $testName))
      {
        continue;
      }

      // OK to execute
      call_user_func_array(array(& $this, $testName), $testArguments);
    }
    
    return $this;
  }
   
  /**
   * Terminates the chain of validations.
   * @return boolean
   */
  public function ok()
  {
    return !$this->hasFailed;
  }

  /**
   * Terminates the chain of validations.
   * Inverted value.
   * @return boolean
   */
  public function failed()
  {
    return $this->hasFailed;
  }
  
}
?>