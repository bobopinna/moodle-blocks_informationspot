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
 * Form for editing Informationspot block instances.
 *
 * @package   block_informationspot
 * @copyright 2014 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/repository/lib.php');

class block_informationspot_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
        global $CFG, $PAGE, $COURSE;

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configspottitle', 'block_informationspot'));
        $mform->setType('config_title', PARAM_TEXT);

        $fileoptions = array('subdirs'=>false,
                             'maxfiles'=>1,
                             'maxbytes'=>$COURSE->maxbytes,
                             'accepted_types'=>'web_image',
                             'return_types'=>FILE_INTERNAL);
        $mform->addElement('filemanager', 'config_imagespot', get_string('configspotfile', 'block_informationspot'), $fileoptions);
        $mform->addElement('editor', 'config_text', get_string('configspottext', 'block_informationspot'));
        $mform->setType('config_text', PARAM_RAW);
        $mform->addElement('text', 'config_buttontext', get_string('configspotbutton', 'block_informationspot'));
        $mform->setType('config_buttontext', PARAM_TEXT);
        $mform->addElement('text', 'config_buttonlink', get_string('configspotlink', 'block_informationspot'));
        $mform->setType('config_buttonlink', PARAM_URL);
     
        $choices = array();
        $choices['always'] = get_string('always', 'block_informationspot');
        if ($PAGE->context === context_course::instance(SITEID)) {
            $choices['beforelogin'] = get_string('beforelogin', 'block_informationspot');
            $choices['afterlogin'] = get_string('afterlogin', 'block_informationspot');
            //$choices['onetimebefore'] = get_string('onetimebefore', 'block_informationspot');
            //$choices['onetimeafter'] = get_string('onetimeafter', 'block_informationspot');
        } else {
           // $choices['onetime'] = get_string('onetime', 'block_informationspot');
        }
        if (count($choices) > 1) {
            $mform->addElement('select', 'config_showspot', get_string('configshowspot', 'block_informationspot'), $choices, 'always');
        } else {
            $mform->addElement('hidden', 'config_showspot', 'always');
            $mform->setType('configshowsspot', PARAM_TEXT);
        }
    }

    function set_data($defaults) {
        global $COURSE;

        if (!$this->block->user_can_edit()) {
            if  (!empty($this->block->config->title)) {
                 // If a title has been set but the user cannot edit it format it nicely
                 $title = $this->block->config->title;
                 $defaults->config_title = format_string($title, true, $this->page->context);
                 // Remove the title from the config so that parent::set_data doesn't set it.
                 unset($this->block->config->title);
            }
            if  (!empty($this->block->config->imagespot)) {
                 $fileoptions = array('subdirs'=>false,
                                      'maxfiles'=>1,
                                      'maxbytes'=>$COURSE->maxbytes,
                                      'accepted_types'=>'web_image',
                                      'return_types'=>FILE_INTERNAL);
                 $draftitemid = file_get_submitted_draft_itemid('config_imagespot');
                 file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_informationspot', 'image', 0, $fileoptions);
                 $defaults->config_imagespot = $draftitemid;
                 unset($this->block->config->imagespot);
            }
            if  (!empty($this->block->config->text)) {
                 $text = $this->block->config->text;
                 $defaults->config_text = format_text($text, true, $this->page->context);
                 unset($this->block->config->text);
            }
            if  (!empty($this->block->config->buttontext)) {
                 $buttontext = $this->block->config->buttontext;
                 $defaults->config_buttontext = format_string($buttontext, true, $this->page->context);
                 unset($this->block->config->buttontext);
            }
            if  (!empty($this->block->config->buttonlink)) {
                 $buttonlink = $this->block->config->buttonlink;
                 $defaults->config_buttonlink = $buttonlink;
                 unset($this->block->config->buttonlink);
            }
        }

        parent::set_data($defaults);

        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }

        if (isset($title)) {
            $this->block->config->title = $title;
        }
        if (isset($draftitemid)) {
            $this->block->config->imagespot = $draftitemid;
        }
        if (isset($title)) {
            $this->block->config->title = $title;
        }
        if (isset($text)) {
            $this->block->config->text = $text;
        }
        if (isset($buttontext)) {
            $this->block->config->buttontext = $buttontext;
        }
        if (isset($buttonlink)) {
            $this->block->config->buttonlink = $title;
        }
    }
}
