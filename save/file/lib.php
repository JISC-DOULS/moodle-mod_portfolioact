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
 * Library for portfolioact mode sub-plugins
 *
 * @package    portfolioact
 * @subpackage portfolioact_google_save
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__).'/renderer.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once($CFG->dirroot.'/mod/portfolioact/mode/lib.php');

/**
 * Class for save plugins
 *
 * This extends the mode plugin class and provides a renderer
 * and other functionality to manage the scaffold specific settings for
 * this activity
 *
 * @package portfolioact
 * @subpackage portfolioact_google_save
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



class portfolioact_file_save extends portfolioact_save_plugin {

    private static $instance;
    protected $savetype = 'file';
    protected $outputtype;

    protected function __construct($actid, $cmid) {
        global $PAGE, $DB;
        $this->renderer = $PAGE->get_renderer('mod_portfolioact_filesave');
        $this->actid = $actid;
        $this->cmid = $cmid;
        $this->mode = portfolioact_mode::get_plugin_mode($actid);

        if ($this->mode == 'template') {//does it have a scaffold?

            $portfolioact_mode_template = portfolioact_mode::get_mode_instance
            ($actid, 'template');

            if (! is_null($portfolioact_mode_template->settings->scaffold)) {
                $this->outputtype = 'scaffold';
            }

        } else {
            $this->outputtype = $this->mode;
        }

        parent::__construct($actid, $cmid);

    }

    public static function get_instance($actid, $cmid) {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c($actid, $cmid);
        }

        return self::$instance;

    }

    /**
     * Return the html string for the button
     *
     * @see portfolioact_save_plugin->output_save_button().
     *
     *
     * @return string
     */


    public function output_save_button() {

        if ($this->outputtype == 'scaffold') {
            $exportfiletype = get_string('filetypezip', 'portfolioactsave_file');
        } else {
            $exportfiletype = get_string('filetypefile', 'portfolioactsave_file');
        }

        return $this->renderer->render_save_button(array('actid'=>$this->actid,
            'exportfileype' => $exportfiletype, 'cmid'=>$this->cmid));
    }

    /**
     * Provides the title for this type
     *
     * @see portfolioact_save_plugin->get_title()
     * @return string
     */

    public function get_title() {
        return get_string('title', 'portfolioactsave_file');

    }

    /**
     * Export the data as a file
     *
     * @see portfolioact_save_plugin->export_data()
     *
     *
     */

    public function export_data($option = null) {
        global $CFG;

        switch ($this->mode) {

            case 'template'://exportdata will be an html string

                $ret = $this->setrtffromhtml();

                if ($ret === false) {
                    $this->error_message = 'error';//TODO
                    return false;
                }

                if (! class_exists('portfolioact_mode_template')) {
                    include($CFG->dirroot.'/mod/portfolioact/mode/template/lib.php');
                }
                $settings = portfolioact_mode_template::get_settings($this->actid);
                $scaffid = $settings->scaffold;
                //this->modeinstancename

                if (is_null($scaffid)) { //no scaffold attached

                    $filename = $this->shortcoursename . '_' . str_replace(" ", "_",
                    $this->portfolioact->name) . '.rtf';

                    //this approach just spits it out.
                    $this->immediateoutput = array('filename' => $filename,
                        'filedata' => $this->rtf,
                         'mimetype' => 'text/rtf');

                    return true;

                } else {//a scaffold is attached
                    if (! class_exists('portfolioact_mode_scaffold')) {
                        include($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
                    }
                    $scaffdata = portfolioact_mode_scaffold::get_data_for_export
                    ($this->actid, $scaffid);

                    if (! class_exists('portfolioact_scaffold')) {
                        include($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
                    }

                    $filename =  str_replace(" ", "_", $this->portfolioact->name) . '.rtf';

                    $scaffold = new portfolioact_scaffold($scaffid);

                    //$scaffold->name
                    $outputfilename = $this->shortcoursename . '_' . str_replace(" ", "_",
                    $this->portfolioact->name) . '.zip';

                    $ret = $this->create_archive_from_scaffold($scaffdata,
                    array( 'filename' => $filename , 'contents' => $this->rtf));

                    if ($ret === false) {
                        $this->error_message = 'error';//TODO
                        return false;
                    } else {

                        $this->immediateoutput = array('fullpath' => $this->result['filepath'],
                            'filename' => $outputfilename,
                              'mimetype' => 'application/zip');
                    }
                }

                return true;

                break;

            case 'scaffold'://export data will be an array tree of moodle files/folders

                $ret = $this->create_archive_from_scaffold($this->exportdata);
                if ($ret === false) {
                    return false;
                } else {

                    $filename = $this->shortcoursename . '_' . str_replace(" ", "_",
                    $this->portfolioact->name) . '.zip';
                    $this->immediateoutput = array('fullpath' => $this->result['filepath'],
                        'filename' => $filename,
                         'mimetype' => 'application/zip');
                }

                break;

            default:

                throw new coding_exception('Unsupported mode for File export');

        }

        return true;

    }

    /**
     * Converts the exportdata to rtf
     *
     * Converts the exportdata to rtf.
     * Expects the exportdata to be a valid html string
     *
     * @return boolean
     */
    protected function setrtffromhtml() {
        global $CFG;

        if (! is_string($this->exportdata)) {
            return false;
        }

        $convertorpath = $CFG->dirroot.'/local/html2rtf/html2rtf.php';
        include_once($convertorpath);

        if (! class_exists('html2rtf')) {
            $url = new moodle_url('/mod/portfolioact/view.php', array('id' => $this->cmid));
            throw new moodle_exception('missinglocal', 'portfolioact', $url, 'html2rtf');

        }

        $rtfopts = array(
            'font-name-default' => 'Arial',
            'font-size-default' => '24',
        );
        $images = array();
        // Find plugin images in document.
        $fs = get_file_storage();
        $root = str_replace('/', '\/', preg_quote($CFG->wwwroot));
        $pattern = '/src="' . $root . '.*?pluginfile\.php.*?\"/';
        preg_match_all($pattern, $this->exportdata, $matches);
        foreach ($matches[0] as $match) {
            $match = str_replace('src="', '', rtrim($match, '"'));
            $filepath = str_replace($CFG->wwwroot . '/mod/portfolioact/mode/template/pluginfile.php', '', $match);
            $file = $fs->get_file_by_hash(sha1($filepath));
            if ($file) {
                $images[$match] = $file->get_content();
            }
        }

        try {
            // Fix Special Chars by adding utf-8 meta tag to content.
            $this->exportdata = '<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $this->exportdata;
            $this->rtf = html2rtf::convert($this->exportdata, $rtfopts, $images);
        } catch (Exception $e ) {
            throw $e;
        }

        return true;

    }



    /**
     * Create the archive for scaffolds
     *
     * Pass in a set of files as returned by file_storage::get_area_tree to
     * use and an optional array
     * with the extra file to inject in the root. The latter case supports templates
     * with attached scaffolds.
     *
     * @param mixed $scaffdata - the tree structure of files to archive
     * @param $extra array a file as string to add into the root and its name
     * @return boolean
     */

    protected function create_archive_from_scaffold($scaffdata, $extra = null) {

        global $CFG, $USER;

        include_once($CFG->dirroot.'/lib/filestorage/zip_archive.php');
        if (! class_exists('zip_archive')) {
            throw new coding_exception('Cannot seem to find the zip archive class');
        }

        $zipname = $this->shortcoursename . '_' .
            str_replace(" ", "_", $this->modeinstancename) . '.zip';

        $this->za = new zip_archive;

        $archivepath = portfolioactsave_get_temp_file_name();

        if ($archivepath === false) {
            return false;
        }

        //must open the archivewith utf8 otherwise add_directory will try to convert
        //see note in add_dir_to_zip()
        $ret = $this->za->open($archivepath, file_archive::OVERWRITE, 'utf-8');
        if ($ret === false) {
            $this->error_message = 'error';//TODO
            return false;
        }

        //1. add extra file being injected in root

        if (! is_null($extra)) {

            // rename the extra injected file if it has the same
            //name as one in the root of the scaffold.

            $filesinroot = array_keys($scaffdata['files']);

            $n=2;

            $testfile = $extra['filename'];
            while (in_array($testfile , $filesinroot)) {
                $match =
                preg_match('/(.+)\.([a-z0-9]+)$/i', $testfile, $matches);
                if ($match) {
                    if ($n==2) {
                        $orignal1 = $matches[1];
                    }
                    $testfile  = $orignal1 . $n . '.' . $matches[2];
                } else {//this won't happen as long as we pass activityname.rtf here
                    if ($n==2) {
                        $orignal2 = $testfile;
                    }
                    $testfile = $orignal2 . $n;

                }
                $n++;
            }
            $extra['filename'] = $testfile;

            $ret = $this->za->add_file_from_string($extra['filename'], $extra['contents']);
            if ($ret === false) {
                $this->error_message = 'error';//TODO
                return false;
            }

        }

        //2. add the rest
        if (isset($scaffdata['files'])) {
            foreach ($scaffdata['files'] as $file) {
                $ret = $this->za->add_file_from_string($file->get_filename(), $file->get_content());
                if ($ret === false) {
                    $this->error_message = 'error';//TODO
                    return false;
                }
            }
        }

        if (isset($scaffdata['subdirs'])) {
            foreach ($scaffdata['subdirs'] as $subdir) {
                $result = $this->add_dir_to_zip($subdir);

                if ($result === false) {//just stop at that point
                    $this->error_message = 'error';//TODO
                    return false;
                }
            }
        }

        $this->za->close();
        $this->result = array('filepath' => $archivepath,
            'filename' => $zipname, 'mimetype' => 'application/zip');

        return true;

    }


    /**
     * Recursively add directories and their contents to the Zip
     *
     * @param $dir
     * @return boolean
     */
    private function add_dir_to_zip($dir) {

        //must open the archivewith utf8 otherwise add_directory will try to convert
        //the dir names to that encoding but get_filepath will not and they will
        //be out of synch
        $ret = $this->za->add_directory(rtrim($dir['dirfile']->get_filepath(), '/'));

        foreach ($dir['files'] as $file) {
            $loc = $file->get_filepath() . $file->get_filename();
            $loc = preg_replace('/^\//', '', $loc);
            $ret = $this->za->add_file_from_string($loc, $file->get_content());
            if ($ret === false) {
                return false;
            }
        }

        if (! empty($dir['subdirs'])) {
            foreach ($dir['subdirs'] as $dir) {
                $this->add_dir_to_zip($dir);
            }
        }
        return true;
    }



}
