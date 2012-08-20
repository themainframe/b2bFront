<?php
/**
 * Module: Inventory
 * Mode: Item Tags Modify
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_item_tags')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=inventory&mode=tags');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

?>

<h1><?php print $row->name; ?></h1>
<br />

<script type="text/javascript">
  
  // Create colour picker(s)
  $(function() {
     $('#colour_select').ColorPicker({
      'flat' : true,
      'color' : '<?php print ($row->font_list_colour == '' ? '#ffffff' : $row->font_list_colour); ?>',
      'onChange' : function (hsb, hex, rgb) {
    		$('#f_list_colour').val('#' + hex);
    	}
     });
  });

</script>

<form action="./?act=inventory&mode=tags_modify_do" method="post" enctype="multipart/form-data">
<input type="hidden" name="f_id" value="<?php print $row->id; ?>" />

<div class="panel">
  <div class="title">Item Tag Information</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr>
          <td class="key">
            <strong>Name</strong><br />
            A unique name for the Item Tag.
          </td>
          <td class="value">
            <input name="f_name" type="text" style="width: 200px;" value="<?php print $row->name; ?>" />
          </td>
        </tr>
        <tr class="last">
          <td class="key">
            <strong>Icon</strong><br />
            Choose an icon that will represent this Item Tag.
            <br /><br />
            <span class="grey">More icons may be placed in /share/icon/tags/</span>
            
          </td>
          <td class="value">
             
<?php
  
  // Generate an icon picker
  $iconPicker = new FormIconPicker($BF, 'f_icon', '/share/icon/tags/', $row->icon_path);
  print $iconPicker->render();

?>

          </td>
        </tr>
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Masthead Appearance</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        
        <tr>
          <td class="key">
            <strong>Masthead Display</strong><br />
            Enable this option to show a link to all items tagged with this Item Tag on the homepage.<br />
            <span class="grey">Some skins may not support this option.</span>
          </td>
          <td class="value">
            <input type="checkbox" name="f_masthead" <?php print Tools::booleanToCheckState($row->masthead); ?> value="1" />
          </td>
        </tr>
        
        <?php
        
          if($row->masthead_image_path != '')
          {
        
        ?>
        
        <tr>
          <td class="key">
            <strong>Masthead Image</strong><br />
            The current image used to represent this Item Tag on the Masthead/Homepage.
          </td>
          <td class="value">
            <img src="<?php print $BF->config->get('com.b2bfront.site.url', true); ?>/<?php print $row->masthead_image_path; ?>" alt="Masthead Image" style="margin: 15px 0px 15px 0px;" />
          </td>
        </tr>
        
        <?php
        
          }
          
        ?>
        
        
        <tr class="last">
          <td class="key">
            <strong>Change Masthead Image</strong><br />
            Choose an image to display on the masthead.<br />
            <span class="grey">Some skins may not support this option.</span><br />
            <span class="grey">Omit this field to keep the original image.</span>
          </td>
          <td class="value">
            <input type="file" name="f_image" />
          </td>
        </tr>
        
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">List View Appearance</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr>
          <td class="key">
            <strong>Name Formatting</strong><br />
            Choose how the names of items with this Item Tag should appear in list views.
          </td>
          <td class="value">
            <table class="suboptions">
            
              <thead>
              
                <tr class="header">
                  <td>
                    <strong>Formatting</strong>
                  </td>
                  <td class="value">
                    <strong>On</strong>
                  </td>
                </tr>
                
              </thead>
            
              <tbody>
                
                <tr>
                  <td style="background: #fff;">
                    <img src="/acp/static/icon/edit-bold.png" class="middle" alt="Bold" />
                    &nbsp;&nbsp;&nbsp;Bold Typeface
                  </td>
                  <td class="value">
                    <input <?php print Tools::booleanToCheckState($row->font_list_bold); ?> 
                      type="checkbox" name="f_list_bold" value="1" />
                  </td>
                </tr>
                
                <tr>
                  <td style="background: #fff;">
                    <img src="/acp/static/icon/edit-italic.png" class="middle" alt="Italics" />
                    &nbsp;&nbsp;&nbsp;Italic Typeface
                  </td>
                  <td class="value">
                    <input <?php print Tools::booleanToCheckState($row->font_list_italic); ?> 
                      type="checkbox" name="f_list_italic" value="1" />
                  </td>
                </tr>
                
                <tr>
                  <td style="background: #fff;">
                    <img src="/acp/static/icon/edit-small-caps.png" class="middle" alt="Small Caps" />
                    &nbsp;&nbsp;&nbsp;Small Caps Typeface
                  </td>
                  <td class="value">
                    <input <?php print Tools::booleanToCheckState($row->font_list_small_caps); ?> 
                      type="checkbox" name="f_list_small_caps" value="1" />
                  </td>
                </tr>

              </tbody>
            </table>
          </td>
        </tr>
        
        <tr class="last">
          <td class="key">
            <strong>Background Colour</strong><br />
            Choose the background colour of items with this tag in list views.<br />
            You should choose a <em>light</em> colour (I.e. 'S' less than 40 and 'B' lower than 80).
          </td>
          <td class="value">
            <p style="padding:10px 0px 10px 0px; margin:0;" id="colour_select">
            
            </p>
            <input type="hidden" name="f_list_colour" id="f_list_colour" value="#ffffff" />
          </td>
        </tr>
        
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save changes to this item tag now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>