<?php
/**
 * Module: Data
 * Mode: Import
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

<h1>Guide to Importing Data</h1>
<br />

<div class="panel">
  <div class="title">1 &nbsp; Formatting your Spreadsheet</div>
  <div class="contents">
    
    <p>
      
      For a successful import of data into the Inventory, you should focus on two factors:
    
    </p>
    
      <ul style="list-style: circle; margin-left: 30px;">
        <li>Ensuring that columns have the correct headings.</li>
        <li>Keeping the spreadsheet clean and free of advanced formatting features.</li>
      </ul>
      
    <p>
      
      For example, you should remove any merged cells, hidden columns or rows, graphs, charts and other images.
      
      <br /><br />
      
      Only the first sheet in a workbook will be read.  

    </p>
    
  
  </div>
</div>    

<br />

<div class="panel">
  <div class="title">2 &nbsp; Setting Column Headings</div>
  <div class="contents">
    
    <p>
      
      The top row of your spreadsheet should be composed of column headings.<br />
      This is <em>not</em> the <em>A, B, C...</em> identifiers above the sheet. This refers to a row that you create.
      
      <br /><br />
      
      Columns may appear in any order.
      
      <br /><br />
      
      Column headings are case insensitive. <em>(Eg. Trade Price is considered identical to trade price)</em>
      
      <br /><br />
      
      Dates should be formatted as DD/MM/YYYY (Eg. 11/6/2012).<br />
      Excel format dates are supported.
      
      <br /><br />
      
      Usable column headings are:
      
    </p>
    
      <ul style="list-style: circle; margin-left: 30px;">
        <li>SKU</li>
        <li>Name</li>
        <li>Trade Price</li>
        <li>Pro Net Price</li>
        <li>Pro Net Qty</li>
        <li>Wholesale Price</li>
        <li>RRP</li>
        <li>Stock Free</li>
        <li>Stock Held</li>
        <li>Brand</li>
        <li>Cost Price</li>
        <li>Barcode</li>
        <li>Description</li>
        <li>Due Date</li>
      </ul>
      
    <p>
      
      <strong>The SKU column must always appear.</strong><br />
      All other columns are optional.

    </p>
    
  </div>
</div>  


<br />

<div class="panel">
  <div class="title">3 &nbsp; Replacement Policy</div>
  <div class="contents">
    
    <p>
      
      When you import data into the Inventory using a spreadsheet, you may omit values that you do not wish to change.<br />
      You may also omit columns if there are no changes to be made to the fields in them.
      
      <br /><br />
      
      <strong>If no value is in a given cell</strong> then the value in the database that the cell represents will remain unchanged.<br />
      If you <em>want</em> to empty a cell, you can enter a single space character.
      
      <br /><br />
      
      Columns that are omitted from your spreadsheet will not be changed at all.
      
    </p>
    
  </div>
</div>      