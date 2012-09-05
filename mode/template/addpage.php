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
 * @subpackage portfolioactmode_template
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'  );
require_once($CFG->dirroot.'/mod/portfolioact/locallib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/renderer.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/local_forms.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/db/access.php');


global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;

$templateid = required_param('templateid', PARAM_INT);

$portfolioacttemplate = new portfolioact_mode_template();
$template = new portfolioact_template($templateid);

$context = get_context_instance(CONTEXT_MODULE, $portfolioacttemplate->cm->id);
require_capability('portfolioactmode/template:edittemplates', $context );

$url = new moodle_url('/mod/portfolioact/mode/template/addpage.php',
    array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid));
$PAGE->set_url($url);
$navurl = new moodle_url('/mod/portfolioact/mode/template/manager.php',
    array('id'=>$portfolioacttemplate->cm->id, 'action'=>'list'));
$PAGE->navbar->add(get_string('templatemanager', 'portfolioactmode_template'),
    $navurl);
$navurl = new moodle_url('/mod/portfolioact/mode/template/designer.php',
    array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid));
$PAGE->navbar->add(get_string('templatedesigner', 'portfolioactmode_template'), $navurl);
$PAGE->navbar->add(get_string('createpage', 'portfolioactmode_template'));
$PAGE->set_title($portfolioacttemplate->portfolioact->name);



$mform = new  portfolioact_template_create_page_form(qualified_me());


if ($mform->is_cancelled()) {

    $url = new moodle_url('/mod/portfolioact/mode/template/designer.php',
        array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid));

    redirect($url);

} else if ($fromform=$mform->get_data()) {

    $newpagename = $fromform->pagename;
    $template->add_page($newpagename);

     $url = new moodle_url('/mod/portfolioact/mode/template/designer.php',
        array('id'=>$portfolioacttemplate->cm->id, 'templateid'=>$templateid, 'action'=>'updated'));

    redirect($url);


} else {
    echo $portfolioacttemplate->renderer->header();
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich2
        (get_string('listheading', 'portfolioactmode_template'));
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_generich3
        ($template->name);

     $mform->display();
}

echo $portfolioacttemplate->renderer->footer();
