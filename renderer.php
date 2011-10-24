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
 * @package    mod
 * @subpackage portfolioact
 * @copyright  2011 The open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_portfolioact_renderer extends plugin_renderer_base {

    /**
     * Return a message in case of Activity which is not yet set up.
     * @param $cmid;
     * @param boolean $canedit
     * @param string $mode
     * @return string
     */

    public function render_portfolioactmode_notyetsetup($cmid, $canedit, $mode) {

        $string = html_writer::tag('p', get_string('notyetsetup',
                'portfolioact') );

        if ($canedit) {
            $url = new moodle_url('/mod/portfolioact/mode/'.$mode.'/edit'.$mode.'.php',
                array('id'=>$cmid));
            $edit = get_string('setitup', 'portfolioact');
            $string.= $this->output->single_button($url, $edit, 'get',
            array('disabled'=>false,   'title'=>$edit));

        }

        return $string;
    }

}
