<?php
/**
 * Module: System
 * Mode: Configuration Modification
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

// Find the selected configuration value domain
$configDomainID = $BF->inInteger('domain');
$BF->db->select('*', 'bf_config_domains')
           ->where('id = \'{1}\'', $configDomainID)
           ->limit(1)
           ->execute();
          
// Found domain?
if($BF->db->count != 1)
{
  header('Location: ' . Tools::getModifiedURL(array('mode' => 'config')));
  exit();
}
          
$configDomain = $BF->db->next();

// Now find all settings
$BF->db->select('*', 'bf_config')
           ->where('domain_id = \'{1}\' AND admin_editable = \'1\'', $configDomain->id)
           ->execute();

?>

<h1><?php print $configDomain->title; ?></h1>
<br />

<form method="post" action="./?act=system&mode=config_modify_do&id=<?php print $configDomain->id; ?>">

<div class="panel">
  <div class="title">Configuration Values</div>
  <div class="contents fieldset">
    <table class="fields">
      <tbody>
      
<?php
  
  while($configRow = $BF->db->next())
  {
  
?>  
      
        <tr<?php print ($BF->db->last() ? ' class="last"' : ''); ?>>
          <td class="key">
            <strong><?php print $configRow->nice_name; ?></strong><br />
            <?php print $configRow->description; ?>
          </td>
          <td class="value">
<?php

    switch($configRow->type)
    {
    
      case 'integer':
        
        // Integer Value
        
        print '          <input class="integer" name="f_' . $configRow->id .
              '" style="width: 50px;" value="' . $configRow->value . '" />' . "\n";
        print '          &nbsp; <span class="grey">Default: ' . 
              htmlentities($configRow->default) . '</span>' . "\n";
        
        break;
        
      case 'boolean':
        
        // Boolean (checkbox) Value
        
        print '          <input type="checkbox" value="1" name="f_' . $configRow->id . '"' .
              ($configRow->value == '1' ? ' checked="checked"' : '') . ' />' . "\n";
        print '          &nbsp; <span class="grey">Default: ' . 
              ($configRow->default == '1' ? 'On' : 'Off') . 
              '</span>' . "\n";
        
        break;
        
      case 'text':
      
        // Text Value
        
        print '          <input name="f_' . $configRow->id .
              '" style="width: 250px;" value="' . $configRow->value . '" />' . "\n";
        print '          &nbsp;<br /><br /> <span class="grey">Default: ' . 
              htmlentities($configRow->default) . '</span>' . "\n";
        
        break;
        
      case 'choice':
      
        // Multi-Choice Value
        // These are sourced from another table by keyname and ID
        
        //  public function __construct($name, $query, $value, $text, $presets = array())
        $choiceOptionRow = $BF->db->getRow('bf_config_choices', $configRow->choice_id);
        $choiceOptions = $BF->db->query();
        $choiceOptions->select('*', $choiceOptionRow->table_name)
                      ->execute();
                      
        // Render dropdown
        $choiceDropDown = new DataDropDown('f_' . $configRow->id, 
                                           $choiceOptions,
                                           'id', 
                                           $choiceOptionRow->column_name);
                                           
        // Set current value
        $choiceDropDown->setOption('defaultSelection', $configRow->value);  
        
        // Render and output the dropdown
        print '          ' . $choiceDropDown->render();
        
        break; 
        
    }

?>
          </td>
        </tr>

<?php

  }
  
?>

      </tbody>
    </table>

  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click one of the buttons to the right to proceed</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <input onclick="window.location='./?act=system&mode=config';" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>
