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
 * Main activity view
 *
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL  v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot.'/mod/portfolioact/db/access.php');




$cmid = $id = optional_param('id', 0, PARAM_INT);
$cm = get_coursemodule_from_id('portfolioact', $id, 0, false, MUST_EXIST);
$mode = portfolioact_mode::get_plugin_mode($cm->instance);
$subplug = portfolioact_mode::get_mode_instance($cm->instance, $mode);

//add_to_log($subplug->course->id, 'portfolioact', 'view', "view.php?id=" . $subplug->cm->id,


$PAGE->set_url('/mod/portfolioact/view.php', array('id' => $subplug->cm->id));
$PAGE->set_title($subplug->portfolioact->name);
$PAGE->set_heading($subplug->course->shortname);
$PAGE->set_button(update_module_button($subplug->cm->id, $subplug->course->id,
get_string('modulename', 'portfolioact')));


$context = get_context_instance(CONTEXT_MODULE, $subplug->cm->id);
$canedit = has_capability('mod/portfolioact:canedit', $context );



define('PORTFOLIOACTVIEWER', 1);



if (empty($subplug->settings)  ) {

    echo $OUTPUT->header();

    switch ($mode) {

        case 'scaffold':
            echo $subplug->renderer->render_portfolioactmode_scaffold_viewhead($subplug);
            break;

        case 'template':
            echo $subplug->renderer->render_portfolioactmode_template_viewhead($subplug);
            break;

    }


    echo $subplug->pa_renderer->render_portfolioactmode_notyetsetup($subplug->cm->id,
    $canedit, $mode);
    echo $OUTPUT->footer();
    exit;
}


//handle case the scaffold or tr4




//consume the view from the plugin.
$path =  $CFG->dirroot.'/mod/portfolioact/mode/' . $mode . '/view.php';
$func = 'portfolioactmode_'.$mode.'_view';
if (! function_exists($func)) {
    include_once($path);
}
if (! function_exists($func)) {
    throw new moodle_exception('missingfunction', 'portfolioact', null, $func);
}

$func($subplug);


// Finish the page
echo $OUTPUT->footer();
add_to_log($subplug->course->id, 'portfolioact', 'view', "view.php?id=" . $subplug->cm->id,
    'Mode: ' . $mode, $subplug->cm->id);
