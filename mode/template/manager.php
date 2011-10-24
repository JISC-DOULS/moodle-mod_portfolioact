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
 * Manages saving and editing templates
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
require_once($CFG->dirroot.'/mod/portfolioact/mode/template/db/access.php');


global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;


$action = optional_param('action', 'list', PARAM_ALPHA);


$portfolioacttemplate = new portfolioact_mode_template();

$context = get_context_instance(CONTEXT_MODULE, $portfolioacttemplate->cm->id);
require_capability('portfolioactmode/template:edittemplates', $context );

$url = new moodle_url('/mod/portfolioact/mode/template/manager.php',
    array('course'=>$portfolioacttemplate->course->id));
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('templatemanager', 'portfolioactmode_template'));
$PAGE->set_title($portfolioacttemplate->portfolioact->name);


echo $portfolioacttemplate->renderer->header();
echo $portfolioacttemplate->renderer->render_portfolioactmode_template_list();

$templates = portfolioactmode_template_get_templates_with_count
    ($portfolioacttemplate->course->id, 'name');

if (!empty($templates)) {

    if ($action == 'updated') {
        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_formmessage
            (get_string('updated', 'portfolioactmode_template'),
                'portfolioact-template-template-feedbackmessage');
    }

        echo $portfolioacttemplate->renderer->render_portfolioactmode_template_table
            ($templates, $portfolioacttemplate->cm->id );
} else {
    echo $portfolioacttemplate->renderer->render_portfolioactmode_template_simpletext(
        get_string('notemplates', 'portfolioactmode_template'), '');
}


echo $portfolioacttemplate->renderer->render_portfolioactmode_template_createnewbutton
       ($portfolioacttemplate->cm->id);


echo $portfolioacttemplate->renderer->footer();
