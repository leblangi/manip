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
    private $qa;

    public function get_expected_data() {
        // debugging('get_expected_data');
        return array('attachment' => question_attempt::PARAM_FILES);
    }

    public function get_correct_response() {
        // debugging('get_correct_response');
        return null;
    }
    

    // Si on veut forcer le type de question à n'être évalué qu'en defferedfeedback.
    /*
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return question_engine::make_archetypal_behaviour('fileanalysis', $qa);
    }
     */
    
    public function summarise_response(array $response) {
        //debugging('summarise_response');
        // TODO: déterminer le comportement voulu...
        debugging('FFFFFFFFFFFFFFFFF summarise_response ($response)', DEBUG_NORMAL, array());
        if (!array_key_exists('attachment', $response)) {
            return null;
        } else if ($response['attachment']) {
            return get_string('filesubmitted', 'qtype_manip');
        } else {
            return get_string('filenotsubmitted', 'qtype_manip');
        }
    }

    public function classify_response(array $response) {
        // TODO: déterminer si c'est possible de classer les réponses.
        debugging('classify_response');
        if (!array_key_exists('answer', $response)) {
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
        debugging('is_complete_response');
        debugging('is_complete_response (1) :: '. var_export($response, true));
        
        if (array_key_exists('attachement', $response) && is_object($response['attachment'])) {
            $this->attachment = $response['attachment']->__toString();
            return true;
        }

        return false;
    }
    
    public function is_gradable_response(array $response) {
        debugging('is_gradable_response');
        // TODO: return false si le fichier est corrompu, illisible, etc.
        if ($response['attachment'])
            $this->attachment = $response['attachment'];
        return true;
    }

    public function get_validation_error(array $response) {
        // TODO: retourner une string qui explique l'erreur rencontrée par is_gradable_response, s'il y a lieu.
        debugging('get_validation_error');
        if ($this->is_gradable_response($response)) {
            return '';
        }
        //TODO: message d'erreur approprié.
        return get_string('filenotreceived', 'qtype_manip');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        debugging('is_same_response (prev) :: '. var_export($prevresponse, true) .' (new) ::'. var_export($newresponse, true));        
        // debugging('is_same_response (new->attachement) ::'. var_export($newresponse['attachment']->__toString(), true));        
        
        return array_key_exists('attachment', $prevresponse) && 
            array_key_exists('attachment', $newresponse) && 
            is_object($prevresponse['attachment']) && is_object($newresponse['attachment']) &&
            ($prevresponse['attachment']->__toString() == $newresponse['attachment']->__toString());
    }
    /*
    public function set_qa($qa) {
        $this->qa = $qa;
    }
    */

    public function grade_response(array $response) {
        //global $USER;
        // UGLY HACK? $attemptobj is defined in /mod/quiz/processattempt.php. 
        // TODO: make sure it works if used elsewhere and adapt code...
        //global $attemptobj; 
        
        //echo 'grade_response :: '; print_r($response); echo ' ::';
        debugging('grade_response ($response) :: '. print_r($response, true));
        //debugging('grade_response ($this) :: '. print_r($this, true), DEBUG_ALL, array());
        //debugging('grade_response ($attemptobj) ::'. print_r($attemptobj, true));
        //$q = $attemptobj->get_quizobj();
        //debugging('grade_response ($q) ::'. print_r($q, true));
        //$c = $q->get_context();
        //debugging('grade_response ($c) ::'. print_r($c, true));
        //$contextid = $c->id;
        //debugging('grade_response ($cid) ::'. print_r($contextid, true));
        
        //$files = $this->qa->get_last_qt_files('attachment', $contextid);
        //debugging('files ... '. print_r($files, true));
                
        // tel que vu sur http://docs.moodle.org/dev/File_API#Read_file
        // $fs = get_file_storage();
        /*
        $fileinfo = array(
            'component' => 'question',     // usually = table name
            'filearea' => 'response_attachment',     // usually = table name
            'itemid' => $this->id,               // usually = ID of row in table
            'contextid' => $this->contextid, // ID of context
            'filepath' => '/',           // any path beginning and ending in /
            'filename' => 'response.docx' // any filename
        );
        
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                      $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
         */
        
        // TODO: DIMANCHE SOIR : ici! 
        //$context = context::instance_by_id($this->contextid);
        //debugging('grade_response (context) :: '. print_r($context, true));
        
        //$qa = new question_attempt_step();
        //$files = $qa->get_qt_files('attachment', $this->contextid);
        //debugging('grade_response ($files) :: '. print_r($files, true));
        
        // todo: get stuff out of $files and into $file ... 

        if ($file) {
            $contents = $file->get_content();
            debugging('grade_response :: file read OK');
            // TODO: copier le fichier ailleurs si c'est nécessaire d'y accéder via le disque.
            // $file->copy_content_to($pathname);
            // puis passer ça à la moulinette de la regex choisie (qui est dans $this->regex)
            // ICI : ça serait sans doute mieux de faire un appel vers une autre fonction pour faire tout ça!
        } else { 
            // TODO: donner un message d'erreur 
            debugging('fichier non lisible');
        }

        
        $fraction = 1.0;

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        //echo 'check_file_access'; print_r($qa); print_r($filearea); print_r($options); 
        // debugging(var_export($filearea, true) . var_export($options, true));
        // TODO: traiter le cas de l'attachment fourni en réponse?
        debugging("checked file access for (c) ::". print_r($component, true) ." (f) :: ". print_r($filearea, true));
        if ($component == 'question' && $filearea == 'response_attachment') {
            debugging('response_attachment : '. print_r($options, true) . print_r($args, true));
            return true;            
        } elseif ($component == 'question' && $filearea == 'answerfeedback') {
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
