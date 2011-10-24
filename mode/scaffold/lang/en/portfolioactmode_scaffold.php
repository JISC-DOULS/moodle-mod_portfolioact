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
 * English strings for portfolioact
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package portfolioact
 * @subpackage portfolioactmode_template
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'portfolio activity';
$string['modulenameplural'] = 'portfolio activities';
$string['portfolioactmode_scaffold'] = 'Scaffold';

$string['managescaffolds'] = 'Manage Scaffolds';
//manager.php and addscaffold.php
$string['scaffoldmanager'] = 'Scaffold Manager';
$string['listheading'] = 'Portfolio Activity Scaffolds';
$string['scaffoldmanagertablecaption'] = 'Table shows the scaffolds for this course';
$string['name'] = 'Name';
$string['created'] = 'Created';
$string['modified'] = 'Modified';
$string['used'] = 'Used';
$string['actions'] = 'Actions';
$string['createscaffold'] = 'Create scaffold';
$string['back'] = 'Back';
$string['newscaffoldadded'] = 'New Scaffold Added';
$string['scaffolddeleted'] = 'Scaffold \'{$a}\' deleted';
$string['confirmdelete'] = 'Are you sure you want to delete Scaffold \'{$a}\'?';
$string['ok'] = 'OK';
$string['no'] = 'Cancel';
$string['noscaffolds'] = 'There are no scaffolds in the system. Select the button to start.';
$string['scaffoldname'] = 'Scaffold Name';
$string['updated'] = 'Updated';


//editscaffold.php
$string['editscaffoldsettings'] = 'Edit activity scaffold settings';
$string['noscaffoldsavailablemessage'] = 'Before you can edit the Settings for this Activity you must create a Scaffold to use';
$string['noscaffoldsavailablecontinue'] ='Go to Scaffold Manager';
$string['noscaffoldsavailablemessage'] = 'Before you can edit the Settings for this Activity you must create a Scaffold to use';
$string['noscaffoldsavailablecontinue'] ='Go to Scaffold Manager';
$string['scaffoldsettingscreated'] ='Scaffold Settings Created';
$string['scaffoldsettingsupdated'] ='Scaffold Settings Updated';
$string['editscaffoldbackbutton'] = "Back to settings";
$string['youmustchooseascaffold'] = "You must choose a Scaffold.";

//designer.php
$string['settingsheading'] = 'Edit the settings for Activity:  \'{$a}\'';
$string['select']= 'Select..';
$string['choosescaffold'] = 'Choose Scaffold';
$string['editscaffold'] = 'Scaffolds';
$string['editscaffold_help'] = 'You must link the Activity to a Scaffold.';
$string['editscaffoldnoscaffolds'] = 'Scaffolds';
$string['editscaffoldnoscaffolds_help'] = 'Select the scaffold this activity will use.';
$string['scaffoldeditor'] = 'Scaffold Editor';
$string['scaffoldsaved'] = 'Your Scaffold has been saved';
$string['updatescaffoldname'] ='Update Scaffold name';
$string['emptydirectoriesmessage'] = 'Empty directories';
$string['emptydirectoriesmessage_help'] = 'On some systems empty directories may not be unzipped when the Scaffold is exported as a file. Consider if you really need to include empty directories.';

//view.php
$string['modulename'] = 'Scaffold';
$string['viewhead'] = "Scaffold Activity";
$string['notyetsetup'] = "Not yet set up";
$string['emptyscaffold'] = 'The scaffold has no files';

//delete.php
$string['blockscaffolddeleteplural'] = 'This scaffold \'{$a}\' cannot be deleted as  it is used in some Activities. They must be edited to use another scaffold or deleted first:';
$string['blockscaffolddeletesingular'] = 'This scaffold \'{$a}\' cannot be deleted as  it is used in an Activity. It must be edited to use another scaffold or deleted first:';
$string['actid'] = 'Activity ID';
$string['actname'] = 'Activity Name';
$string['cancel'] = 'Cancel';
$string['mode'] = 'Mode';
$string['scaffmode'] = 'scaffold mode';
$string['tmplmode'] = 'template mode';

//Navbar strings
$string['scaffoldmanager'] = 'Scaffold Manager';
$string['scaffolddesigner'] = 'Scaffold Designer';
$string['editactivitysettings'] = 'Edit Activity Settings';
$string['deletescaffold'] = 'Delete Scaffold';
$string['editactivitysettings'] = 'Edit Activity Settings';

//General error messages
$string['thisscaffolddoesnotexist'] = "This scaffold does not exist. It may have been deleted by another user.";
$string['pluginname'] = 'Scaffold';
$string['scaffold:editscaffolds'] = 'Manage Scaffolds';
