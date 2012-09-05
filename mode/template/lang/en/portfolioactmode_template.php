<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * English strings for portfolioact_template
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['portfolioactfieldset'] = 'Module options';
$string['portfolioactname'] = 'portfolio activity name';
$string['portfolioact'] = 'portfolio activity';
$string['pluginadministration'] = 'Portfolio Activity administration';
$string['pluginname'] = 'portfolio activity';

$string['portfolioactmode_template'] = "Template";

//the settings menu
$string['managetemplates'] = 'Manage Templates';

$string['edittemplatesettings'] = 'Edit activity template settings';


//new jwq - do we need this & wording
//in general do we disntinguish between scaffold and template activities for the user?
$string['noportfolioact_template'] = 'no portfolio template activity';
$string['modulenameplural'] = 'portfolio template activities';
$string['modulename'] = 'portfolio template activity';
$string['listheading'] = 'Portfolio Activity Templates';
$string['createtemplate'] = 'Create template';
$string['templatemanagertablecaption'] = 'Table shows the templates for this course';

$string['name'] = 'Name';
$string['created'] = 'Created';
$string['modified'] = 'Modified';
$string['used'] = 'Used';
$string['actions'] = 'Actions';
$string['createtemplate'] = 'Create template';
$string['back'] = 'Back';
$string['newtemplateadded'] = 'New Template Added';
$string['templatedeleted'] = 'Template \'{$a}\' deleted';
$string['confirmdelete'] = 'Are you sure you want to delete Template \'{$a}\'?';
$string['ok'] = 'OK';
$string['no'] = 'Cancel';
$string['notemplates'] = 'There are no templates in the system yet. Click the button to start.';

//editsettings screen
$string['settingsheading'] = 'Edit the settings for Activity:  \'{$a}\'';
$string['choosetemplate'] = 'Choose template';
$string['choosescaffold'] = 'Choose scaffold';
$string['editscaffold'] = 'Scaffolds';
$string['editscaffold_help'] = 'Optional. You can link the Activity Template to a Scaffold so that when it is exported it uses the structure of that scaffold.';
$string['editscaffoldnoscaffolds'] = 'Scaffolds';
$string['editscaffoldnoscaffolds_help'] = 'Optional. You need to create some scaffolds before you can use them here.';
$string['notemplatesavailablemessage'] = 'Before you can edit the Settings for this Activity you must create a Template to use';
$string['notemplatesavailablecontinue'] ='Go to Template Manager';
$string['edittemplatebackbutton'] = "Back to settings";
$string['youmustselectatemplate'] = "You must select a template";

$string['edittemplate'] = 'Templates';
$string['edittemplate_help'] = 'Required. You must  choose a Template. Important Note: changing a template will cause all student data currently entered for this activity to be lost.';
$string['choosedatamode'] = 'Template type';
$string['editdatamode'] = 'Template type';
$string['editdatamode_help'] = 'Required. Choose \'Activity linked\' to link this template exclusively to this Activity. Choose \'Course linked\' to have every instance of this Template share the same data. ';
$string['choosepages'] = 'Select Pages to display for this Template';
$string['editpages'] = 'Pages';
$string['editpages_help'] = 'Select the Pages you want in this activity, if you have not selected to use \'All\'. Use control-click to select multiple pages. ';
$string['alltext'] = 'All';
$string['choosesavemode'] = 'Show save button?';
$string['editsavemode'] = 'Export allowed';
$string['editsavemode_help'] = 'Required. Indicate whether the Activity should allow export.';
$string['hidesavebutton'] = 'No';
$string['showsavebutton'] = 'Yes';
$string['savemode'] = 'Allow export?';
$string['select']= 'Select..';
$string['templatesettingscreated'] ='Template Settings Created';
$string['templatesettingsupdated'] ='Template Settings Updated';
$string['pagespecific'] = 'Use all pages or customise';
$string['editpagespecific'] = 'Choose pages:';
$string['customtext'] = 'Select pages';
$string['editpagespecific_help'] = 'If you choose \'Select pages\' you can customize which pages to include.';//Note to translator. Note the dependency of this label on the one above.
$string['confirmformsave'] = 'Are you absolutely sure you want to do this? ';
$string['confirmformsavetemplate'] = ' Changing the Template for this Activity will result in any data already entered being lost.';
$string['confirmformsaveactivitymode'] = ' Changing the Template Type for this Activity will result in any data already entered being lost.';
$string['absolutelyconfirm'] = 'Yes. I understand the risks.';
$string['absolutelyconfirmnot'] = 'No. Get me out of here.';
$string['templatename'] = 'Template Name';


//Pages pages - designer.php;
$string['createpage'] = 'Create page';
$string['position'] = 'Position';
$string['templatedesignertablecaption'] = 'Manage the pages in your template';
$string['newpageadded'] = 'New Page Added';
$string['pagedeleted'] = 'Page \'{$a}\' deleted';
$string['confirmdeletepage'] = 'Are you sure you want to delete Page \'{$a}\'?';
$string['up'] = 'Up';
$string['down'] = 'Down';
$string['pagename'] = 'Page Name';
$string['updateposition'] = 'Save Position to database';
$string['startpages'] = 'This template does not yet have any pages. Click the button to start';
$string['positionupdated'] = 'Position updated';
$string['positionupdatefailed'] = 'The operation to update positions failed because another user has added a page since you loaded this screen. Please try again.';
$string['pagename'] = 'Page name';
$string['reorder'] = 'Reorder';
$string['updated'] = 'Updated';
$string['updatetemplatename'] ='Update Template name';

//Pages pages - addpage.php
$string['loading'] = 'Content loading graphic';

//Pages pages - pagedelete.php
$string['confirmpagedelete'] = 'Are you sure you want to delete Page \'{$a}\'?';
$string['pagedeleted'] = 'Page \'{$a}\' deleted';
$string['deletepage'] = 'Delete page';

//Items - pageeditor.php (and others)
$string['pagedesignertablecaption'] = "Manage the items in your page";
$string['startitems'] = 'This page does not yet have any items. Click the button to start';
$string['createitem'] = 'Create new item';
$string['newitemadded'] = 'New Item Added';
$string['itemname'] = 'Item Name';
$string['itemtype'] = 'Item type';
$string['itemtype_help'] = 'Required. The type of item you are adding.';
$string['chooseitemtype'] = 'Choose item type';
$string['contenttypes'] = 'Add content type:';
$string['editcontenttypes'] = 'Content types';
$string['editcontenttypes_help'] ='These are the different kinds of item you can add to your Template';
$string['itemafter'] = 'Add content after:';
$string['edititemafter'] = 'Content after';
$string['edititemafter_help'] ='Insert the new element after this element';
$string['addcontent'] = 'Add content';
$string['edititemname'] ='Item name';
$string['edititemname_help'] ='You must give your content a name. You can change this later.';
$string['itemsaved'] = "New item added";
$string['contentadderror'] = 'An error occurred while adding content. Please try again.';
$string['itemreference'] = 'Reference Item';
$string['itemtext'] = 'Text Item';
$string['itemcheckbox'] = 'Checkbox Item';
$string['iteminstruction'] = 'Instruction Item';
$string['pagename'] = 'Page Name';
$string['updatepagename'] ='Update Page name';
$string['chooseanitem'] = 'Choose one of the items in the templates';
$string['newitemstart'] = 'At the beginning';
$string['newitemend'] = 'At the end';

//Item delete itemdelete.php
$string['confirmitemdelete'] = 'Are you sure you want to delete Item \'{$a}\'?';
$string['itemdeleted'] = 'Item \'{$a}\' deleted';
$string['itemdelete'] = 'Item delete';
$string['blockitemdeleteplural'] = 'This item cannot be deleted as other items depend upon it. They must be deleted first:';
$string['blockitemdeletesingular'] = 'This item cannot be deleted as an other item depends on it. It must be deleted first:';
$string['itemid'] = 'Item ID';
$string['cancel'] = 'Cancel';
$string['usedintemplate'] = 'Used in Template';


//Item settings itemsettings.php
$string['itemsettings'] = "Item settings";
$string['newitem'] = 'New item';
$string['itemsettings_instruction'] = 'Instruction settings';
$string['itemsettings_text'] = 'Text entry settings';
$string['itemsettings_checkbox'] = 'Checkbox settings';
$string['itemsettings_reference'] = 'Reference settings';
$string['itemlargetextlabel'] = 'The text of the instruction';
$string['itemlargetextassist'] = 'Instruction settings';
$string['itemlargetextassist_help'] = 'This is the text which appears to the user as the instruction. The following codes will be replaced automatically: {date} , {courseshortname} , {activityname} , {username}, {pi} . The curly braces are required. \'date\' is the current date when the item is viewed or exported.';
$string['instructionspecialmessage'] = 'Tip: select the help icon for details of substitution codes you can use in the text.';
$string['itemnametype'] = 'Item name';
$string['itemnametype_help'] = 'The name of your item';
$string['newitemadded'] = 'New item added';
$string['newitemnotadded'] = 'Sorry. A problem occured. New item not added. Please try again later.';
$string['itembackbutton'] = 'Back to items';
$string['itemquestionlabel'] = 'Question text';
$string['itemquestionlabelassist'] = 'Question settings';
$string['itemquestionlabelassist_help'] = 'This is the text which appears alongside the question explaining to the student what they should do.';
$string['itemquestionformatlabel'] = 'Tick for HTML';
$string['itemquestionformatlabelassist'] = 'Question format';
$string['itemquestionformatlabelassist_help'] = 'Tick to enable HTML (rich text) for the student\'s response.';
$string['responsesaved'] = 'The Student\'s response will be saved with the output';
$string['responsenotsaved'] = 'The Student\'s response will not be saved with the output';
$string['itemsavewithexportlabel'] = 'Response is saved with output';
$string['itemsavewithexportlabelassist'] = 'Response is saved with output';
$string['itemsavewithexportlabelassist_help'] = 'Controls whether the text which the student enters is saved with the export or not. Choosing not makes this question work as a kind of memo for the student.';
$string['defaultstatechecked'] = 'Checkbox is checked by default';
$string['defaultstatenotchecked'] = 'Checkbox is not checked by default';
$string['defaultstatelabel'] = 'Checked by default?';
$string['defaultstatelabelassist'] = 'Checked by default?';
$string['defaultstatelabelassist_help'] = 'Indicate whether the checkbox should be checked to begin with.';
$string['choosesourceitem'] = 'Choose the source item';
$string['choosesourceitemassist'] = 'Choose the source item';
$string['choosesourceitemassist_help'] = 'Choose the source item for this reference item. Note that the data entered by users is only shared between the reference item and its source when the Activities are in Course Mode or when the Items are within the same Activity.';
$string['checkboxlabel'] = 'Enter the label:';
$string['checkboxlabelassist'] = 'Enter the label:';
$string['checkboxlabelassist_help'] = 'This is the label which appears next to the checkbox.';
$string['itemedited'] = "Item was saved";
$string['itemnotedited'] = "'Sorry. A problem occured. Item was not saved. Please try again later.";
$string['showandsave'] = "Display the instruction and save it with the output";
$string['saveonly'] = "Don't show the instruction. But save it with the export.";
$string['showonly'] = "Show the instruction. Do not save it with the export.";
$string['showandsavelabel'] = "Display and save options";
$string['showandsavelabelassist'] = "Display and save options";
$string['showandsavelabelassist_help'] = "Control the display and save options for the instruction.";
$string['noitemsyet'] = "There are no items yet to use as the source for a reference item. (Source items cannot be on the same page as the item which references them). Please go back and create an item.";

$string['itemdatepicker'] = 'Date Selector Item';
$string['itemsettings_datepicker'] = 'Date Selector';

$string['itemnumeric'] = 'Numeric Item';
$string['itemsettings_numeric'] = 'Numeric';
$string['enteranumericvalue'] = 'You must enter a numeric value';

$string['itemduration'] = 'Duration Item';
$string['itemsettings_duration'] = 'Duration';



//view.php
$string['modulename'] = 'Template';
$string['viewhead'] = "Template Activity";
$string['page'] = "Page";
$string['saveitems'] = 'Save items on this page';
$string['notyetsetup'] = 'This activty has not yet been set up';
$string['nopages'] = 'The Activity has a template but that template has no pages.';
$string['nopagesbutton'] = 'Edit Template';
$string['missingsettings'] = 'The template appears to be missing';
$string['noword'] = 'No';
$string['yesword'] = 'Yes';
$string['pageword'] = 'Page:  ';
$string['previous'] = 'Previous';
$string['next'] = 'Next';
$string['notyetsetup'] = "Not yet set up";
$string['emptyscaffold'] = "The scaffold associated with this template needs to be set up";


//General error messages
$string['saveerror'] = 'An error occurred while saving. Please try again.';
$string['cantsavedataforaninstruction'] = "Invalid attempt to save data for instruction";
$string['thistemplatedoesnotexist'] = "This template does not exist. It may have been deleted.";
$string['thistemplatedoesnotexistcreate'] = "This template does not exist. It may have been deleted by another user. Follow the link to assign another template to this activity";
$string['missingitem'] = 'Attempt to retrieve an item which does not exist.';
$string['itemnotexist'] = 'This item does not exist. It may have been deleted by another user.';
$string['unexpected'] = 'Unexpected error';


$string['javascript_disabled_position_pages'] = 'The functionality to re-order the pages requires you have Javascript enabled in your browser';
$string['javascript_disabled_position_items'] = 'The functionality to re-order the items requires you have Javascript enabled in your browser';

//delete.php
$string['blocktemplatedeleteplural'] = 'This template \'{$a}\' cannot be deleted as  it is used in some Activities. They must be edited to use another template or deleted first:';
$string['blocktemplatedeletesingular'] = 'This template \'{$a}\' cannot be deleted as  it is used in an Activity. It must be edited to use another template or deleted first:';
$string['actid'] = 'Activity ID';
$string['actname'] = 'Activity Name';
$string['cancel'] = 'Cancel';

//Navbar strings
$string['templatemanager'] = 'Template Manager';
$string['templatedesigner'] = 'Template Designer';
$string['pagedesigner'] = 'Page Designer';
$string['editactivitysettings'] = 'Edit Activity Settings';
$string['deletetemplate'] = 'Delete Template';


//Module Settings
$string['modename'] = 'Mode';
$string['pluginname'] = 'Template';
$string['template:edittemplates'] = 'Manage Templates';
