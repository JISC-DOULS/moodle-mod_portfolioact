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

class portfolioactsave_google_renderer extends plugin_renderer_base {

    /**
     * Returns the html for the save button
     *
     * @param array $params
     * @return string
     */

    public function render_save_button($params) {
        $buttontext = get_string('save', 'portfolioactsave_google');
        $url = new moodle_url('/mod/portfolioact/save/save.php',
            array('savetype'=>'google', 'actid'=>$params['actid'], 'id'=>$params['cmid']));
        return $this->output->single_button($url,
            $buttontext, 'get', array('disabled'=>false, 'title'=>$buttontext));

    }

    /**
     * The header for this page
     * @return string
     */

    public function render_page_header() {
        return $this->output->heading(get_string('title', 'portfolioactsave_google'), 2, 'portfolioacthead');

    }

    /**
     * Return a link to sign in to Google
     *
     * @param string $url
     * @return string
     */

    public function render_googlesignin($url, $domain = false) {
        $str = $this->output->heading(get_string('googlesigninheader',
        'portfolioactsave_google'), 3);
        //Message for google apps domain logins
        if ($domain) {
            $str .= html_writer::tag('p',
            get_string('googlesignindomain', 'portfolioactsave_google', $domain));
        }
        $str.= '<a href="' . $url . '">' . get_string('googlesignin',
            'portfolioactsave_google') . '</a>';
        return $str;

    }

    /**
     * Return a user information message about the interaction with Google
     * @param string $message
     * @return string
     *
     */

    public function render_resultok($message) {
        return html_writer::tag('p', $message);
    }

    /**
     * Return a user information message about the interaction with Google
     * @param string $message
     * @return string
     *
     */

    public function render_resultnotok($message) {
        return html_writer::tag('p', $message);
    }

    public function render_export_error($modetype, $message) {//TODO message?
        $modetype = ucfirst(strtolower($modetype));
        return html_writer::tag('p',
            get_string('exportgoogleerror', 'portfolioactsave_google', $modetype),
                array('class'=>'portfolioactsave_google_error'));
    }

    public function render_export_google_success($modetype) {
        $modetype = ucfirst(strtolower($modetype));
        return html_writer::tag('p', get_string('exportgooglesuccess',
            'portfolioactsave_google', $modetype));

    }

    /**
     * Returns html for an ajax spinner
     *
     * Returns html for an ajax spinner while we wait for Google Docs
     * @param string $state
     * @return string
     */

    public function render_ajax_spinner($state) {
        if ($state == 'hide') {
            return $this->pix_icon('ajax-loader',
                get_string('loadinggoogle', 'portfolioactsave_google'),
                    'portfolioactsave_google',
                    array('class'=>
                       'portfolioactsave_google_spinner portfolioactsave_google_spinner_hide'));
        } else {
                return $this->pix_icon('ajax-loader',
                get_string('loadinggoogle', 'portfolioactsave_google'),
                    'portfolioactsave_google',
                    array('class'=>
                       'portfolioactsave_google_spinner portfolioactsave_google_spinner_show'));

        }
    }

    /**
     * Returns be patient message
     * @param string $state
     * @return string
     *
     */

    public function render_be_patient($state) {
        if ($state == 'hide' ) {
            return html_writer::tag('p', get_string('bepatient', 'portfolioactsave_google'),
                array('class'=>'portfolioactsave_google_patient_message') );
        } else {
                return html_writer::tag('p', get_string('bepatient', 'portfolioactsave_google'),
                array('class'=>'portfolioactsave_google_patient_message_show') );

        }
    }

    /**
     * Return a p element for messages
     *
     * @return string
     */

    public function render_message_area() {
        return html_writer::tag('p', '', array('class'=>'portfolioactsave_google_message'));
    }

    /**
     * Return a p element for messages
     *
     * @return string
     */

    public function render_message_area_success($text = '') {
        return html_writer::tag('p', $text, array('class'=>'portfolioactsave_google_message_success'));
    }

    /**
     * Return a p element for messages
     *
     * @return string
     */

    public function render_message_area_error() {
        return html_writer::tag('p', '', array('class'=>'portfolioactsave_google_message_error'));
    }


    /**
     * Return a button to iniate second stage of Google Export
     *
     *
     * @return string
     */

    public function google_export_button() {
        $buttontext = get_string('proceed', 'portfolioactsave_google');

        return html_writer::tag('button', $buttontext,
            array('class' => 'portfolioactsave_google_export'));
    }

    /**
     * Return a message after export to google
     *
     * @param $text string
     * @return string
     */

    public function export_message($text) {
        return html_writer::tag('p', $text);

    }

    /**
     * Return the html to advise user they need to enable Javscript
     *
     * @return string
     */

    public function render_javascript_disabled_message($url) {
        return html_writer::tag('p', get_string('javascript_disabled', 'portfolioactsave_google', $url),
            array('class'=>'portfolioactsave_google_javascript_disabled'));

    }


}
