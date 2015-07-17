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
 * Form for editing Information spot block instances.
 *
 * @package   block_informationspot
 * @copyright 2014 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_informationspot extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_informationspot');
    }

    function has_config() {
        return false;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function instance_allow_multiple() {
        return true;
    }

    function instance_has_config() {
        return false;
    }

    function get_content() {
        global $CFG, $PAGE, $OUTPUT;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->title = '';
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (!isset($this->config)) {
            $this->config = new stdClass();
        }

        if (!isset($this->config->showspot)) {
            $this->config->showspot = 'always';
        }

        if (($this->config->showspot == 'beforelogin') && (isloggedin())) {
            return $this->content;
        }

        if (($this->config->showspot == 'afterlogin') && (!isloggedin())) {
            return $this->content;
        }

        if (!empty($this->config->title)) {
            $this->content->text .= html_writer::tag('h1', $this->config->title);
        }

        $fs = get_file_storage();
        $imagefiles = $fs->get_area_files($this->context->id, 'block_informationspot', 'image', 0);
        // Get file which was uploaded in draft area.
        $imagefile = null;
        foreach ($imagefiles as $file) {
            if (!$file->is_directory()) {
                $imagefile = clone($file);
                break;
            }
        }
        if (!empty($imagefile)) {
            $imageurl = moodle_url::make_pluginfile_url($this->context->id,'block_informationspot', 'image', 0, $imagefile->get_filepath(), $imagefile->get_filename());
            
            $image = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'block_informationspot_image'));
            $this->content->text .= html_writer::tag('div', $image, array('class' => 'block_informationspot_imagecontainer'));
        }

        if (isset($this->config->text)) {

            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->text['format'])) {
                $format = $this->config->text['format'];
            }
            $this->content->text .=  html_writer::tag('div', format_text($this->config->text['text'], $format), array('class' => 'block_informationspot_text'));
            
            //$this->content->text .= html_writer::tag('p', $this->config->text, array('class' => 'block_informationspot_text'));
        }

        if (!empty($this->config->buttonlink)) {
            $buttontext = get_string('more', 'block_informationspot');
            if (!empty($this->config->buttontext)) {
                $buttontext = $this->config->buttontext;
            }
            $this->content->footer .= html_writer::link(new moodle_url($this->config->buttonlink), $buttontext, array('class' => 'block_informationspot_button btn'));
        }

        return $this->content;
    }


    /**
     * Serialize and store config data
     */

    function instance_config_save($data, $nolongerused = false) {
        global $COURSE;

        $config = clone($data);
        $fileoptions = array('subdirs'=>false,
                             'maxfiles'=>1,
                             'maxbytes'=>$COURSE->maxbytes,
                             'accepted_types'=>'web_image',
                             'return_types'=>FILE_INTERNAL);
        file_save_draft_area_files($config->imagespot, $this->context->id, 'block_informationspot', 'image',  0, $fileoptions);

        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_informationspot');
        return true;
    }

    function content_is_trusted() {
        return true;
    }

    /**
     * The block should not be dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }

}
