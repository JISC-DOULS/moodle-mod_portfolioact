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
 * Workshop module renderering methods are defined here
 *
 * @package    portfolioact
 * @subpackage portfolioact_google_save
 * @copyright  2011 The open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_portfolioact_filesave_renderer extends plugin_renderer_base {

    /**
     * Returns the html for the save button
     *
     * @param array $params
     * @return string
     */

    public function render_save_button($params) {

        $buttontext = get_string('save', 'portfolioactsave_file', $params['exportfileype']);
        $url = new moodle_url('/mod/portfolioact/save/save.php',
            array('savetype'=>'file', 'actid'=>$params['actid'], 'id'=>$params['cmid']));
        return $this->output->single_button($url,
            $buttontext, 'get', array('disabled'=>false, 'title'=>$buttontext));

    }

    /**
     * The header for this page
     * @return string
     */

    public function render_page_header() {
        return $this->output->heading(get_string('title', 'portfolioactsave_file'), 2);;

    }

    /**
     * Returns be patient message
     *
     * @return string
     *
     */

    public function render_be_patient() {
            return html_writer::tag('p', get_string('bepatient', 'portfolioactsave_file'));
    }

    /**
     * Return error message
     *      *
     * @param string $modetype
     * @param string $message
     * @return string
     */

    public function render_export_error($modetype, $message = '') {//TODO message?
        $modetype = ucfirst(strtolower($modetype));
        return html_writer::tag('p', get_string('fileerror',
            'portfolioactsave_file', $modetype), array('class'=>'portfolioactsave_google_error'));
    }

}
