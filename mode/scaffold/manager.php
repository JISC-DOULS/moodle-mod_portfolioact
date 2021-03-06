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
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/renderer.php');


global $OUTPUT;
global $PAGE;
global $DB;
global $CFG;



$action = optional_param('action', 'list', PARAM_ALPHA);


$scaffold_plugin = new portfolioact_mode_scaffold();

require_once($CFG->dirroot.'/mod/portfolioact/mode/scaffold/db/access.php');

$context = context_module::instance($scaffold_plugin->cm->id);
require_capability('portfolioactmode/scaffold:editscaffolds', $context );

$url = new moodle_url('/mod/portfolioact/mode/scaffold/manager.php',
    array('course'=>$scaffold_plugin->course->id));
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('scaffoldmanager', 'portfolioactmode_scaffold'));
$PAGE->set_title($scaffold_plugin->portfolioact->name);

echo $scaffold_plugin->renderer->header();
echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_editlist();

if ($action == 'updated') {
    echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_updated();
}

$scaffolds = portfolioactmode_scaffold_get_scaffolds_with_count
    ($scaffold_plugin->course->id, 'name');


if (!empty($scaffolds) ) {
        $table = new portfolioactmode_scaffold_table($scaffolds, $scaffold_plugin->cm->id);
        echo $scaffold_plugin->renderer->render($table);
} else {
       echo $scaffold_plugin->renderer->render_noscaffolds();

}

echo $scaffold_plugin->renderer->render_portfolioactmode_scaffold_createnewbutton
    ($scaffold_plugin->cm->id);

echo $scaffold_plugin->renderer->footer();
