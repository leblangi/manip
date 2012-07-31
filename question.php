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
 * manip question definition class.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Represents a docx file manipulation question.
 *
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_manip_question extends question_graded_automatically {
    // TODO: manip
    //  * true/false -> good/wrong
    //  * conserver la regex..
    public $correctanswerid;
    public $incorrectanswerid;
    public $feedbackcorrect;
    public $feedbackcorrectformat;
    public $feedbackincorrect;
    public $feedbackincorrectformat;
    public $regex;
    public $attachment;
    public $result;

    public function get_expected_data() {
        // debugging('get_expected_data');
        return array('attachment' => question_attempt::PARAM_FILES);
    }

    public function get_correct_response() {
        // debugging('get_correct_response');
        return null;
    }
    

    // Si on veut forcer le type de question à n'être évalué qu'en deferredfeedback.
    /*
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return question_engine::make_archetypal_behaviour('deferredfeedback', $qa);
    }
    */
    
    public function summarise_response(array $response) {
        //debugging('summarise_response');
        if ($this->is_complete_response($response)) {
            return get_string('filesubmitted', 'qtype_manip');
        } else {
            return get_string('filenotsubmitted', 'qtype_manip');
        }
    }

    public function classify_response(array $response) {
        // TODO: déterminer si c'est possible de classer les réponses.
        // debugging('classify_response');
        if (!$this->is_complete_response($response)) {
           return array($this->id => question_classified_response::no_response());
        }
        
        list($fraction) = $this->grade_response($response);
        if ($this->result) {
            return array($this->id => new question_classified_response(0,
                    get_string('correctanswer', 'qtype_manip'), $fraction));
        } else {
            return array($this->id => new question_classified_response(1,
                    get_string('incorrectanswer', 'qtype_manip'), $fraction));
        }
    }

    public function is_complete_response(array $response) {
        debugging('is_complete_response'. print_r($response, true));

        // TODO: mettre les messages d'erreur dans le fichier de langue
        if (!array_key_exists('attachment', $response) || !is_object($response['attachment'])) {
            $this->error = 'noanswer';
            return false;
        }
        $stored_file = $response['attachment']->get_files();
        if (!$stored_file) {
            $this->error = 'filenotsubmitted';
            return false;
        }
        
        debugging('is_complete_response ($stored_file) :: '. print_r($stored_file, true));
        $file = array_shift($stored_file);
        debugging('is_complete_response ($file) :: '. print_r($file, true));
        $content = $file->get_content();
        if ($content === FALSE) {
            $this->error = 'filenotreadable';
            return false;
        }
        return true;
    }
    
    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string($this->error, 'qtype_manip');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        // debugging('is_same_response (prev) :: '. var_export($prevresponse, true) .' (new) ::'. var_export($newresponse, true));        
        // debugging('is_same_response (new->attachement) ::'. var_export($newresponse['attachment']->__toString(), true));        
        
        return array_key_exists('attachment', $prevresponse) && 
            array_key_exists('attachment', $newresponse) && 
            is_object($prevresponse['attachment']) && is_object($newresponse['attachment']) &&
            ($prevresponse['attachment']->__toString() == $newresponse['attachment']->__toString());
    }

    public function grade_response(array $response) {
        // debugging('grade_response ($response) :: '. print_r($response, true));
        $stored_file = $response['attachment']->get_files();
        debugging('grade_response ($stored_files) :: '. print_r($stored_files, true));
        $file = array_shift($stored_file);
        
        // ZipArchive seem to only be able to open files and stored_file does 
        // not let us read the file directly - so we have to copy_content_to 
        // somewhere else.
        $zipfilename = tempnam(sys_get_temp_dir(), 'm');        
        if (!$file->copy_content_to($zipfilename)) {
            debugging('file not readable, copy_content_to failed.');
            // TODO: Log this error which, really, should not happen.
            return array(0, question_state::$invalid); // TODO: test this out
        }
        
        $zip = new ZipArchive;
        if ($zip->open($zipfilename) === TRUE) {
            $content =  $zip->getFromName('word/document.xml');
            $zip->close();
        } else {
            debugging('zip file could not be opened');
            return array(0, question_state::$invalid); // TODO: test this out
        }
        
        $result = preg_match_all($this->regex, $content, $out);
        debugging('grade_response (result) :: '. $result);
        
        if ($result === FALSE) {
            return array(-1, question_state::$invalid); // TODO: test this out
        } elseif ($result > 0) {
            $fraction = 1.0;
        } else {
            $fraction = 0.0;
        }
        
        // Delete temporary file
        unlink($zipfilename);

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $answerid = reset($args); // itemid is answer id.
            $response = $qa->get_last_qt_var('answer', '');
            return $options->feedback && (
                    ($answerid == $this->correctanswerid && $response) ||
                    ($answerid == $this->incorrectanswerid && $response !== ''));

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}
