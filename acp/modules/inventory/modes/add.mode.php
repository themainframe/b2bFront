<?php
/**
 * Module: Inventory
 * Mode: Add Item Options Selection
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

?>

<h1>Add an Item</h1>
<br />

<div class="panel">
  <div class="title">
    <p>Choose Item Type</p>
  </div>
  <div class="contents">

    <h3>Choose what type of item you would like to create...</h3>
    <p>
      You can create three types of items using two interfaces - click on an option below to start creating an item.
    </p>

    <div class="twobox">
      
      <div class="box" onclick="window.location='./?act=inventory&mode=add_standard';">
        <div style="background: #e5e5e5 url(/acp/static/image/aui-menu-item.png) repeat-x;">
          
          <h2>
            <img src="/acp/static/icon/node-select-child.png" alt="Child" />
            Child <span style="color: #afafaf">or</span><img src="/acp/static/icon/node-lone.png"
             alt="Standard" style="padding: 0px 3px 0px 3px;" />Standard
            <span style="color: #afafaf">Item</span>
          </h2>
          <p style="padding: 10px 0px 0px 0px;">
            This type of item can be a variation of an existing parent item, or can be a stand-alone
            item which is not associated with a parent at all.<br /><br />
            
            For example, you could create a Standard item for a product with no variations at all, I.e.
            a product that only comes in one type (for example, size or colour).
          </p>
        </div>
      </div>
      
      <div class="box" onclick="window.location='./?act=inventory&mode=add_parent';">
        <div style="background: #e5e5e5 url(/acp/static/image/aui-menu-item.png) repeat-x;">
          <h2>
            <img src="/acp/static/icon/node-select.png" alt="Parent" />
            Parent <span style="color: #afafaf">Item</span>
          </h2>
          <p style="padding: 10px 0px 0px 0px;">
            This type of item is used as a template for other items and is invisible to non-staff users.
            It has associated variables that child items can inherit and assign values to.<br /><br />
            
            For example, you could create a Parent Item for a pair of boots then create three child items
            to represent the <em>small</em>, <em>medium</em> and <em>large</em> versions of the boots.
          </p>
        </div>
      </div>
      
      <br class="clear" />  
      
    </div>  
    
    <p>
      If you are unsure about which product type to choose,
      click <a href="./?act=inventory&mode=addoptions-help">here</a> for more information about item relationships and variations.
    </p>
  </div>
</div>

<br />