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
require_once($CFG->dirroot.'/lib/googleapi.php');

/**
 * Class for save plugins
 *
 * This extends the mode plugin class and provides a renderer
 * and other functionality to manage the scaffold specific settings for
 * this activity
 *
 * @package portfolioact
 * @subpackage portfolioactsave_google
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class portfolioact_google_save extends portfolioact_save_plugin {

    private static $instance;
    protected $savetype = 'google';
    public $error_files = 0;

    protected function __construct($actid, $cmid) {
        global $PAGE;
        $this->renderer = $PAGE->get_renderer('mod_portfolioact_googlesave');
        $this->actid = $actid;
        $this->cmid = $cmid;
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
        return $this->renderer->render_save_button(array('actid'=>$this->actid,
            'cmid'=>$this->cmid));

    }

    /**
     * Provides the title for this type
     *
     * @see portfolioact_save_plugin->get_title()
     * @return string
     */

    public function get_title() {
        return get_string('title', 'portfolioactsave_google');

    }


    /**
     * Export the data as a file
     *
     * @see portfolioact_save_plugin->export_data()
     *
     *
     */

    public function export_data($google_authsub) {

        global $CFG, $USER;
        $this->error_message = "";

        if (is_null($this->exportdata)) {
            $this->error_message = get_string('exportfailed', 'portfolioactsave_google');;
            return false;
        }

        $this->docs = new portfolioactsave_google_docs($google_authsub);

        $course_collection = $this->docs->collection_exists_in_root
        ($this->shortcoursename, 'root');

        if ($course_collection === false ) {
            $course_collection = $this->docs->create_sub_collection('root',
            $this->shortcoursename);
        }

        if ($course_collection === false) {
            $this->error_message = get_string('retryneeded', 'portfolioactsave_google');;
            // Error may be because authorisation revoked. Reset token.
            $google_authsub->log_out();
            return false;

        }

        switch ($this->mode) {

            case 'template'://exportdata will be an html string

                //for google we export an html file
                //$this->modeinstancename
                $filename = str_replace(" ", "_" , $this->portfolioact->name);

                //handle possible case that this template has a scaffold

                if (! class_exists('portfolioact_mode_template')) {
                    include($CFG->dirroot.'/mod/portfolioact/mode/template/lib.php');
                }
                $settings = portfolioact_mode_template::get_settings($this->actid);
                $scaffid = $settings->scaffold;

                if (! is_null($scaffid)) {//template has scaffold case

                    if (! class_exists('portfolioact_mode_scaffold')) {
                        include($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
                    }
                    if (! class_exists('portfolioact_scaffold')) {
                        include($CFG->dirroot.'/mod/portfolioact/mode/scaffold/lib.php');
                    }
                    $scaffold = new portfolioact_scaffold($scaffid);

                    $scaffdata = portfolioact_mode_scaffold::get_data_for_export
                    ($this->actid, $scaffid);

                    $sparebytes = $this->docs->getsparebytes();
                    if ($sparebytes === false  ) {
                        $this->error_message =
                            get_string('unknownerror', 'portfolioactsave_google');
                        return false;
                    }
                    $convertingmimetypes = $this->docs->getconvertable();
                    if ($convertingmimetypes === false) {
                        $this->error_message =
                            get_string('unknownerror', 'portfolioactsave_google');
                        return false;
                    }

                    $scaffoldnonconvertingsize
                    = portfolioactsave_google_getscaffsizenonconverting
                    ($scaffdata, $convertingmimetypes);

                    if ($scaffoldnonconvertingsize > $sparebytes) {
                        $this->error_message =
                            get_string('activityquotaexceeded', 'portfolioactsave_google');
                        return false;
                    }

                    $scaffold_collection_resource_id = $this->docs->get_scaffold_folder
                    ($this->portfolioact->name, $course_collection);

                    if ($scaffold_collection_resource_id === false) {
                        $this->error_message =
                            get_string('unknownerror', 'portfolioactsave_google');
                        return false;
                    }

                    //put the template one in the scaffold root
                    $ret = $this->docs->send_file_resumable_by_string($this->exportdata,
                        'text/html' , $filename ,
                    $scaffold_collection_resource_id );

                    //now treat as scaffold
                    $this->error_files = 0;
                    return $this->export_scaffold_to_google($scaffold_collection_resource_id,
                    $scaffdata);

                } else {//no scaffold associated

                    $ret = $this->docs->send_file_resumable_by_string($this->exportdata,
                         'text/html' , $filename , $course_collection );

                    if ($ret === false  ) {
                        $this->error_message =
                            get_string('unknownerror', 'portfolioactsave_google');
                        return false;
                    }
                }
                return true;
                break;

            case 'scaffold'://export data will be an array tree of moodle files/folders
                //$this->modeinstancename

                $sparebytes = $this->docs->getsparebytes();
                if ($sparebytes === false  ) {
                    $this->error_message = get_string('unknownerror', 'portfolioactsave_google');
                    return false;
                }
                $convertingmimetypes = $this->docs->getconvertable();
                if ($sparebytes === false  ) {
                    $this->error_message = get_string('unknownerror', 'portfolioactsave_google');
                    return false;
                }

                $scaffoldnonconvertingsize
                = portfolioactsave_google_getscaffsizenonconverting
                ($this->exportdata, $convertingmimetypes);

                if ($scaffoldnonconvertingsize > $sparebytes) {
                    $this->error_message =
                        get_string('activityquotaexceeded', 'portfolioactsave_google');
                    return false;
                }

                $scaffold_collection_resource_id = $this->docs->get_scaffold_folder
                ($this->portfolioact->name, $course_collection );
                $this->error_files = 0;
                return $this->export_scaffold_to_google($scaffold_collection_resource_id,
                $this->exportdata);

                break;

            default:

                throw new coding_exception('Unsupported mode for Google export');

        }

        return true;

    }

    /**
     * Export the scaffold to Google Docs
     *
     * If passed with no params will archive the exportdata. Or pass in
     * a set of files as returned by file_storage::get_area_tree to use and an array
     * with the extra file to inject in the root. The latter case supports templates
     * with attached scaffolds.
     *
     * @param string $scaffold_collection_resource_id
     * @param mixed $scaffdata - the tree structure of files to archive
     * @return boolean
     */

    protected function export_scaffold_to_google($scaffold_collection_resource_id, $scaffdata) {

        if ($scaffold_collection_resource_id === false) {
            $this->error_message = get_string('unknownerror', 'portfolioactsave_google');
            return false;
        }

        //1. create files in $scaffold_collection_resource_id

        if (isset($scaffdata['files'])) {
            foreach ($scaffdata['files'] as $file) {
                $ret = $this->docs->send_file_resumable_using_file($file,
                $scaffold_collection_resource_id );
                if ($ret === false) {
                    $this->error_message = get_string('unknownerror', 'portfolioactsave_google');
                    $this->error_files++;

                }
            }
        }

        //2. foreach directory in the root post it and its contents
        if (isset($scaffdata['subdirs'])) {
            foreach ($scaffdata['subdirs'] as $subdir) {
                $result = $this->post_google_directory($scaffold_collection_resource_id, $subdir);

                if ($result === false) {
                    $this->error_message = get_string('unknownerror', 'portfolioactsave_google');
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively post directories and their contents to Google
     * @param string $targetcollectionresourceid
     * @param mixed $dir
     * @return boolean
     */

    //at  the moment if one dir fails we stop. we don't try other dirs
    protected function post_google_directory($targetcollectionresourceid, $dir ) {

        $folderresourceid = $this->docs->create_sub_collection($targetcollectionresourceid,
        $dir['dirname']);
        if ($folderresourceid === false) {
            $this->error_files++;

        } else {

            foreach ($dir['files'] as $file) {
                $ret = $this->docs->send_file_resumable_using_file($file, $folderresourceid );
                if ($ret == false) {
                    $this->error_files++;
                }
            }

        }

        //spawning tree structure - might cause problems? bit of a tangle. TODO
        if (! empty($dir['subdirs'])) {
            foreach ($dir['subdirs'] as $dir) {
                $this->post_google_directory($folderresourceid, $dir);
            }
        }
        return true;
    }

    /**
     * Returns a link to google docs
     * @return string
     */
    public function get_google_link() {
        $link = 'http://docs.google.com';
        //If domain config is set direct specifically to that
        $context = get_context_instance(CONTEXT_MODULE, $this->cmid);
        $domain = get_config('portfolioactsave_google', 'google_domain');
        if ($domain != '' &&
            !has_capability('portfolioactsave/google:anydomain', $context)) {
            $link .= '/a/' . $domain;
        }
        return $link;
    }

}




/**
 * Class for manipulating google documents through the google data api
 * Docs for this can be found here:
 *
 * You MUST set CURLOPT_HEADER specifically on each and every request you make in
 * this class. @see portfolioactsave_google_docs::cleanoptX()
 *
 * @package    mod
 * @subpackage portfolioact
 * @copyright The Open University 2011
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolioactsave_google_docs {

    private $cache = array();

    const REALM            = 'http://docs.google.com/feeds/ http://spreadsheets.google.com/feeds/ http://docs.googleusercontent.com/';
    const DOCUMENTFEED_URL = 'http://docs.google.com/feeds/default/private/full';
    const USER_PREF_NAME   = 'google_authsub_sesskey';
    const ALTUSER_PREF_NAME   = 'portfolioact_authsub_sesskey';

    const GOOGLEFEEDS = 'https://docs.google.com/feeds';
    const RESUMABLEPOST_URL =  '/upload/create-session/default/private/full';
    const ROOTFOLDERFOLDERS = '/default/private/full/folder:root/contents/-/folder';
    const FULL = '/default/private/full';
    const ROOTFOLDERALL = '/default/private/full/folder:root/contents';
    const ROOTFOLDERIDENTIFIER = 'https://docs.google.com/feeds/default/private/full/folder%3Aroot';
    const METAINFORMATION = 'https://docs.google.com/feeds/metadata/default';

    const PARENTIDENTIFIER = 'http://schemas.google.com/docs/2007#parent';
    const GDNAMESPACE = 'http://schemas.google.com/g/2005';
    const DOCSNAMESPACE = 'http://schemas.google.com/docs/2007';

    protected $authsub = null;

    /**
     * Constructor.
     *
     * @param object A google_auth_request object which can be used to do http requests
     */
    public function __construct($authsub) {

        if (is_a($authsub, 'oauth2_client')) {
            $this->authsub = $authsub;
            $this->reset_curl_state();
        } else {
            throw new coding_exception('Google Curl Request object not given');
        }
    }

    /**
     * Resets state on oauth curl object and set GData protocol
     * version
     */
    public function reset_curl_state() {
        $this->authsub->reset_state();
        $this->authsub->setHeader('GData-Version: 3.0');
    }

    /**
     * Sends a file to Google Docs using the resumable method
     *
     * The Google resumable API method supports sending a file in chunks. Current usage
     * is to send the whole file in the first chunk
     * See http://code.google.com/apis/documents/docs/3.0/developers_guide_protocol.html
     * #ResumableUpload
     * This method uses an overriden version of put() which accepts a file handle so
     * we don't need to get the file path.
     *
     * @param stored_file $file
     * @param string $targetcollectionresourceid
     * @return boolean
     *
     */

    public function send_file_resumable_using_file($file, $targetcollectionresourceid) {

        /*
         using file handles also means we can handle files broken into
         chunks without writing to disk
         if $file->get_filesize(0 > toobig
         $str = $file->get_content()
         $chunks = the $str split into chunks
         loop:
         $fh = fopen('php://memory', 'rw');
         fwrite($fh, $chunk[0]);
         rewind($fh);
         send this chunk.
         end loop
         */

        global $CFG;
        $this->reset_curl_state();
        $filesize = $file->get_filesize();
        $this->authsub->setHeader("Content-Length: 0");
        $this->authsub->setHeader("Content-Type: ". $file->get_mimetype());
        $this->authsub->setHeader("Slug: ". $file->get_filename());
        $this->authsub->setHeader("X-Upload-Content-Type: ". $file->get_mimetype());
        $this->authsub->setHeader("X-Upload-Content-Length: ". $filesize);
        $url = self::GOOGLEFEEDS . self::RESUMABLEPOST_URL.'/folder:'.
        $targetcollectionresourceid.'/contents';

        $ret = $this->authsub->post($url, '',
        array('CURLOPT_HEADER'=>true));

        if ($this->authsub->info['http_code'] !== 200) {
            return false; //the request to get a unique uri failed
        }

        $return_headers = $this->http_parse_headers($ret);

        if (! array_key_exists('Location', $return_headers  )) {
            return false;
        }

        $fh = $file->get_content_file_handle();

        //send the file in one chunk
        $range_string = "bytes 0-". ($filesize - 1) . "/" . $filesize;
        $this->reset_curl_state();
        $this->authsub->setHeader("Content-Length: ".$filesize);
        $this->authsub->setHeader("Content-Type: ". $file->get_mimetype());
        $this->authsub->setHeader("Content-Range: ". $range_string );
        $ret = $this->authsub->put_by_filehandle($return_headers['Location'],
        array('filehandle'=>$fh,
            'filesize' => $filesize),
        array('CURLOPT_HEADER'=>true));

        if ($this->authsub->info['http_code'] === 201) {
            return true;
        } else {
            return false;
        }
    }






    /**
     * Sends a file to Google Docs using the resumable method
     *
     * The Google resumable API method supports sending a file in chunks. Current usage
     * is to send the whole file in the first chunk
     * See http://code.google.com/apis/documents/docs/3.0/developers_guide_protocol.html
     * #ResumableUpload
     *
     * The problem is that the curl put method expects a filepath
     * And PHP curl's put expects a PHP file resource
     *
     * $fh = fopen('php://memory', 'rw');
     *  fwrite($fh, $dataToPut);
     *  rewind($fh);
     *   http://www.osterman.com/wordpress/2007/06/13/php-curl-put-string
     *
     * @param string $filecontents
     * @param string $mimetype
     * @param string $targetcollectionresourceid
     * @param string $filename something to call the file
     * @return boolean
     *
     */

    public function send_file_resumable_by_string($filecontents, $mimetype, $filename,
    $targetcollectionresourceid) {

        global $CFG;
        $this->reset_curl_state();
        $filesize = strlen($filecontents);
        $this->authsub->setHeader("Content-Length: 0");
        $this->authsub->setHeader("Content-Type: ". $mimetype);
        $this->authsub->setHeader("Slug: ". $filename);
        $this->authsub->setHeader("X-Upload-Content-Type: ". $mimetype);
        $this->authsub->setHeader("X-Upload-Content-Length: ". $filesize);

        $ret = $this->authsub->post(self::GOOGLEFEEDS . self::RESUMABLEPOST_URL.'/folder:'.
        $targetcollectionresourceid.'/contents', '',
        array('CURLOPT_HEADER'=>true));

        if ($this->authsub->info['http_code'] !== 200) {
            return false; //the request to get a unique uri failed
        }

        $return_headers = $this->http_parse_headers($ret);

        if (! array_key_exists('Location', $return_headers  )) {
            return false;
        }

        //send the file in one chunk
        $range_string = "bytes 0-". ($filesize - 1) . "/" . $filesize;

        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $filecontents);
        rewind($fh);

        $range_string = "bytes 0-". ($filesize - 1) . "/" . $filesize;
        $this->reset_curl_state();
        $this->authsub->setHeader("Content-Length: ".$filesize);
        $this->authsub->setHeader("Content-Type: ". $mimetype);
        $this->authsub->setHeader("Content-Range: ". $range_string );
        $ret = $this->authsub->put_by_filehandle($return_headers['Location'],
        array('filehandle'=>$fh,
            'filesize' => $filesize),
        array('CURLOPT_HEADER'=>true));

        if ($this->authsub->info['http_code'] === 201) {
            return true;
        } else {
            return false;
        }

    }


    /**
     * Return http headers as array
     *
     * @param string $headers
     * @return array
     *
     */

    private function http_parse_headers($headers) {

        $patt1 = '/^HTTP/';
        $patt2 = '/(^.+?): *(.+)$/';
        $return_headers = array();
        //covers case of \r\n and \n
        $headers = str_replace("\r", "", $headers);
        $headers = explode("\n", $headers);

        foreach ($headers as $line) {
            if (preg_match($patt1, $line)) {
                continue;
            }
            if (preg_match($patt2, $line, $matches)) {
                $return_headers[$matches[1]] = $matches[2];
            }
        }

        return $return_headers;

    }

    /**
     * Test if a collection exists in the root
     *
     * Test if a collection exists in the root by doing a
     * specific search and matching for root folder as parent
     * If it does return its resource id, else return null.
     *
     * Note - Google by default returns 100 items in a feed
     * We are not testing here for possibility of > 100 items but in fact
     * it doesn't matter as we are taking the first anyway.
     *
     *
     * @param string $collectionname
     * @return boolean|string false or collection id
     */

    public function collection_exists_in_root($collectionname) {
        global $CFG, $OUTPUT;

        $this->reset_curl_state();
        //get all items in root matching the query string
        $url = self::GOOGLEFEEDS . self::ROOTFOLDERFOLDERS . '?title='.urlencode($collectionname).
             '&title-exact=true';

        $content = $this->authsub->get($url, null, array('CURLOPT_HEADER'=>false));

        $xml = new SimpleXMLElement($content);

        foreach ($xml->entry as $node) {
            if ( (string) $node->title == $collectionname) {
                $node->registerXPathNamespace("gd", self::GDNAMESPACE);
                $resource = $node->xpath("gd:resourceId");
                $resource_id = (string) $resource[0];
                $matches = array();
                if (preg_match('/^folder:(.+)$/', $resource_id, $matches)) {
                    return $matches[1];
                }

            }
        }

        return false;
    }


    /**
     * Return the resource id for a scaffold fodler.
     *
     * The scaffold folder is in the parent folder given by $course_collection
     * We check for $scaffoldname[0] and keep incrementing so we return the
     * first available one - which could be e.g $scaffoldname[0] , $scaffoldname[1]
     * The idea is we will never over-write a previous export.
     * THIS MUST GET THE WHOLE FEED (all pages) to be sure we have the highest
     *
     *
     *
     * @param string $scaffoldname
     * @param string $course_collection the resource id of the collection for the course
     * @return null|string null on error or a resource id for the new scaffold folder
     */

    public function get_scaffold_folder($scaffoldname, $course_collection) {
        global $CFG, $OUTPUT;

        $highest_scaff_counter = $this->get_highest_scaff($course_collection, $scaffoldname, -1);

        //We ignore the race condition
        //It doesn't really matter - we wouldn't over-write - just have two the same.
        if ($highest_scaff_counter == -1) {//no scaffname[n] matches create first one
            $scaffold_resource_id = $this->create_sub_collection($course_collection, $scaffoldname.
                '[0]');
        } else {//create the next one
            $highest_scaff_counter++;
            $newscaff = $scaffoldname. '[' . $highest_scaff_counter . ']';
            $scaffold_resource_id  = $this->create_sub_collection($course_collection, $newscaff );
        }

        if ($scaffold_resource_id === false) {
            return false;
        }

        return $scaffold_resource_id;

    }

    /**
     * Returns, in bytes, the amount of free space a user has in their Google Docs Account
     *
     * Returns, in bytes, the amount of free space a user has in their Google Docs Account.
     * Note that currently (July 2011) converted docs do not count at all towards
     * the quota.
     *
     * @return int|boolean false if error
     *
     */

    public function getsparebytes() {

        $this->reset_curl_state();
        $content = $this->authsub->get(self::METAINFORMATION, null, array('CURLOPT_HEADER'=>false));

        if (! isset($this->cache['ACCOUNTMETAINFORMATION'] )) {
            $this->cache['ACCOUNTMETAINFORMATION'] = $content;
        }

        $xml = new SimpleXMLElement($content);
        $xml->registerXPathNamespace("gd", self::GDNAMESPACE);
        $quotatotal = $xml->xpath("gd:quotaBytesTotal");

        $quotatotal_int = (string) $quotatotal[0];
        $quotaused = $xml->xpath("gd:quotaBytesUsed");
        $quotaused_int = (string) $quotaused[0];
        return ($quotatotal_int -  $quotaused_int);

    }

    /**
     * Return an array of mime types that google will convert by default
     *
     * @return array
     */


    public function getconvertable() {

        if (isset($this->cache['ACCOUNTMETAINFORMATION'] )) {
            $content = $this->cache['ACCOUNTMETAINFORMATION'];
        } else {
            $this->reset_curl_state();
            $content = $this->authsub->get(self::METAINFORMATION,
                null, array('CURLOPT_HEADER'=>false));
            if ($this->authsub->info['http_code'] !== 200) {
                return false;
            }
            $this->cache['ACCOUNTMETAINFORMATION'] = $content;
        }

        $xml = new SimpleXMLElement($content);
        $xml->registerXPathNamespace("docs", self::DOCSNAMESPACE);

        $importtypes = $xml->xpath("docs:importFormat");
        $convertabletypes = array();
        foreach ($importtypes as $importnode) {
            $atts = $importnode->attributes();
            $convertabletypes[] = (string) $atts['source'];
        }

        return $convertabletypes;

    }


    /**
     * Create a subcollection
     *
     * @param string $course_collection the target collection
     * @param string $subcollectionname
     * @return boolean|string
     */


    public function create_sub_collection($course_collection, $subcollectionname) {
        $this->reset_curl_state();
        $this->authsub->setHeader("Content-Type: application/atom+xml");
        $url = self::GOOGLEFEEDS . self::FULL . '/folder:'.$course_collection.'/contents';
        $data =  <<<EOF
        <?xml version='1.0' encoding='UTF-8'?>
        <entry xmlns="http://www.w3.org/2005/Atom">
        <category scheme="http://schemas.google.com/g/2005#kind"
           term="http://schemas.google.com/docs/2007#folder"/>
        <title>$subcollectionname</title>
        </entry>
EOF;

        $data = trim($data);
        $this->authsub->setHeader("Content-Length: ".strlen($data));
        //our resume method sets CURLOPT_HEADER to true and filelib curl does not reset this option
        //in its cleann_opt method which is called before every request

        $ret = $this->authsub->post($url, $data, array('CURLOPT_HEADER'=>false));

        if ( $this->authsub->info['http_code'] === 201 ) {

            $xml = new SimpleXMLElement($ret);
            $xml->registerXPathNamespace("gd", self::GDNAMESPACE);
            $resource = $xml->xpath("gd:resourceId");
            $resource_id = (string) $resource[0];
            $matches = array();
            if (preg_match('/^folder:(.+)$/', $resource_id, $matches)) {
                return $matches[1];
            }

        } else {
            return false;
        }

    }

    /**
     * Return the last number in sequence for the scaffold folders
     *
     * @param string $course_collection
     * @param string $scaffoldname
     * @param int $scaff_counter
     * @return int
     */


    //TODO - do we want to put some sort of upper limit on this?
    private function get_highest_scaff($course_collection, $scaffoldname,
        $scaff_counter = -1, $next = null) {

        if (is_null($next)) {

            $url = self::GOOGLEFEEDS . self::FULL .
                '/folder:' . $course_collection . '/contents/-/folder?title='.
            urlencode($scaffoldname).
                '&showfolders=true';
        } else {
            $url = $next;

        }
        $this->reset_curl_state();
        $content = $this->authsub->get($url, null, array('CURLOPT_HEADER'=>false));

        $xml = new SimpleXMLElement($content);

        $xml->registerXPathNamespace("default", "http://www.w3.org/2005/Atom");
        $nextnode = $xml->xpath("//default:link[@rel='next']");
        $nextlink="";
        if (! empty($nextnode)) {
            $nextlink = (string) $nextnode[0]['href'];
        }
        $resource_id="";

        foreach ($xml->entry as $node) {
            $node->registerXPathNamespace("default", "http://www.w3.org/2005/Atom");
            $actual_name = (string) $node->title;
            $matches = array();
            if ( preg_match('/^.+?\[(\d+)\]$/', $actual_name, $matches )) {
                if ($matches[1] > $scaff_counter ) {
                    $scaff_counter = $matches[1];
                }
            }
        }

        if (empty($nextlink)) {
            return $scaff_counter;
        } else {
            return $this->get_highest_scaff($course_collection,
                $scaffoldname,  $scaff_counter, $nextlink);
        }

    }

}





/**
 * Class extends google_authsub
 *
 * @package portfolioact
 * @subpackage portfolioactsave_google
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class portfolioactsave_google_authsub extends google_oauth {

    private static $context = '';//Store context to pickup in login checks

    public function set_context($context) {
        self::$context = $context;
    }

    /**
     * HTTP PUT method
     *
     * We want to over-ride the put method in Moodle's Curl so that
     * we can pass a file handle not a file path. As (without some
     * flaky code) we can't easily obtain the full file path from the
     * file API.
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return bool
     */
    public function put_by_filehandle($url, $params = array(), $options = array()) {
        $fh = $params['filehandle'];
        if (!is_resource($fh)) {
            return null;
        }

        $size = $params['filesize'];
        $options['CURLOPT_PUT']        = 1;
        $options['CURLOPT_INFILESIZE'] = $size;
        $options['CURLOPT_INFILE']     = $fh;
        if (!isset($this->options['CURLOPT_USERPWD'])) {
            $this->setopt(array('CURLOPT_USERPWD'=>'anonymous: noreply@moodle.org'));
        }
        $ret = $this->request($url, $options);
        return $ret;
    }

    /**
     * Override to force to google domain if admin setting active
     * @param string $returnaddr
     * @param string $realm
     * @param int $cmid optionally send cmid where static context not already set
     */
    public function get_login_url() {
        $orig = parent::get_login_url();
        //Check admin setting and capability to see if we force domain login
        $domain = get_config('portfolioactsave_google', 'google_domain');
        if ($domain != '' &&
            !has_capability('portfolioactsave/google:anydomain', self::$context)) {
            $orig->param('hd', urlencode($domain));
        }
        return $orig;
    }

}



/**
 * Used to include custom Javascript for this module
 *
 * @return array
 */

function portfolioactsave_google_get_js_module() {
    global $PAGE;
    return array(
        'name' => 'portfolioactsave_google',
        'fullpath' => '/mod/portfolioact/save/google/module.js',
        'requires' => array('base', 'dom',  'io', 'node', 'json',
        'node-event-simulate')
    );
}


/**
 * Returns total size of files which won't be converted by google in a scaffold
 *
 * @param file_storage $tree
 * @param array $convertingtypes array of convertable mime types
 * @return int
 */

function portfolioactsave_google_getscaffsizenonconverting($tree, $convertingtypes) {

    $files = array();
    $size = 0;

    if (isset($tree['files'])) {
        foreach ($tree['files'] as $file) {
            $files[] = $file;
        }
    }

    if (isset($tree['subdirs'])) {
        foreach ($tree['subdirs'] as $subdir) {
            $files = array_merge($files, portfolioactsave_google_getfiles($subdir));

        }
    }

    foreach ($files as $file) {
        if (! in_array($file->get_mimetype(), $convertingtypes  )) {
            $size+= $file->get_filesize();
        }

    }

    return $size;

}

/**
 * Gets the files in a file_storage directory calling itself recursively
 * on sub subdirectories
 *
 * @param mixed $dir
 * @param array $files
 * @return array of files
 */

function portfolioactsave_google_getfiles($dir, $files = array()) {

    foreach ($dir['files'] as $file) {
        $files[] = $file;
    }

    if (! empty($dir['subdirs'])) {
        foreach ($dir['subdirs'] as $dir) {
            portfolioactsave_google_getfiles($dir, $files);
        }
    }

    return $files;

}
