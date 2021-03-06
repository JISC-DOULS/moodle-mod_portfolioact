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

$string['pluginname'] = 'Google Drive Export';
$string['enabled'] = 'Enable export to Google';
$string['enabled_desc'] = 'Users can export from activity to Google Drive.';
$string['admingoogle_domain'] = 'Use a Google Apps Domain for portfolioact export?';
$string['admingoogle_domain_desc'] = 'Set to domain name to enable e.g. my.domain.com .';
$string['save'] = 'Export to a Google Drive Account';

$string['google:anydomain'] = 'When restricting portfolioact export to a Google domain users with
 this capability are not restricted.';

$string['proceed'] = 'Proceed';
$string['title'] = 'Export to Google';
$string['googlesigninheader'] = "You will need to sign into your Google Account to export the activity.";
$string['googlesignindomain'] = 'You must have signed up for a Google Apps account on the {$a} domain.';
$string['googlesignin'] = 'Export activity into your account';
$string['postedok'] = 'File posted successfully.';
$string['postednotok'] = 'File post was not successful.';
$string['noscaffongoogledocs'] = 'Could not create the {$a} on Google Drive';
$string['exportgooglesuccess'] = 'The activity was successfully exported to your Google Drive Account.';
$string['linktogoogle'] = 'Access Google Drive';
$string['exportgoogleerror'] = 'Sorry.  An error occurred while exporting the activity to the Google service. ';

$string['loadinggoogle'] = 'Contacting Google...';
$string['bepatient'] = 'This process can take several minutes. Please be patient.';
$string['activityquotaexceeded'] = 'The activity cannot be exported as it would exceed your Google Drive account storage quota.';
$string['unknownerror'] = 'We do not have any more information about this error.';
$string['connectionlost'] = 'We cannot connect you to Google. Please try and login again.';
$string['exportgooglesuccesssome_errors'] = ' *The activity was sent to Google but not all the files were successfully transferred.';
$string['exportgooglesucces'] = 'The activity has been successfully exported to Google';
$string['exportfailed'] = 'Export failed.';
$string['retryneeded'] = 'Please try exporting the activity again.';
$string['connectionerror'] = 'An error occurred connecting to Google';
$string['unknownerror'] ='Unknown error';
$string['javascript_disabled'] = 'This functionality expects that you have Javascript enabled in your browser. <a href="{$a}&nojs=1">Select this link to manually export.</a>';
