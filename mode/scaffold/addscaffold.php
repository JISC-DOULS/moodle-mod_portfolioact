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
 * Manages adding new templates
 *
 * @package    portfolioact
 * @subpackage portfolioactmode_scaffold
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/renderer.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/local_forms.php');

require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/db/access.php');

global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;


$scaffold_plugin = new portfolioact_mode_scaffold();

$context = get_context_instance(CONTEXT_MODULE, $scaffold_plugin->cm->id);
require_capability('portfolioactmode/scaffold:editscaffolds', $context );

$url = new moodle_url('/mod/portfolioact/mode/template/addscaffold.php',
    array('id'=>$scaffold_plugin->cm->id));
$PAGE->set_url($url);
$navurl = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
    array('id'=>$scaffold_plugin->cm->id, 'action'=>'list'));
$PAGE->navbar->add(get_string('scaffoldmanager', 'portfolioactmode_scaffold'), $navurl);
$PAGE->navbar->add(get_string('createscaffold', 'portfolioactmode_scaffold'));
$PAGE->set_title($scaffold_plugin->portfolioact->name);

$mform = new  portfolioact_scaffold_create_form(qualified_me());

if ($mform->is_cancelled()) {

    $url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
        array('id'=>$scaffold_plugin->cm->id, 'action'=>'list'));

    redirect($url);

} else if ($fromform=$mform->get_data()) {

    $newscaffoldname = $fromform->scaffoldname;
    $scaffold_plugin->add_new($newscaffoldname, $scaffold_plugin->course->id);

    $url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
        array('id'=>$scaffold_plugin->cm->id, 'action'=>'updated'));

    redirect($url);

} else {
     echo $scaffold_plugin->renderer->header();
     echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_editlist();
     $mform->display();
}

echo $scaffold_plugin->renderer->footer();
