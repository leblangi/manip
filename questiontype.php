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
 * Question type class for the true-false question type.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * The manip question type class.
 *
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_manip extends question_type {
    
    public function extra_question_fields() {
        return array('question_manip', 'regex', 'correct', 'incorrect');
    }
    
    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();
        $context = $question->context;

        // Fetch old answer ids so that we can reuse them
        $oldanswers = $DB->get_records('question_answers',
                array('question' => $question->id), 'id ASC');

        // Save the correct answer - update an existing answer if possible.
        $answer = array_shift($oldanswers);
        if (!$answer) {
            $answer = new stdClass();
            $answer->question = $question->id;
            $answer->answer = '';
            $answer->feedback = '';
            $answer->id = $DB->insert_record('question_answers', $answer);
        }

        $answer->answer   = 'correct'; //get_string('true', 'qtype_manip');
        $answer->fraction = 1.0; // $question->correctanswer;
        $answer->feedback = $this->import_or_save_files($question->feedbackcorrect,
                $context, 'question', 'answerfeedback', $answer->id);
        $answer->feedbackformat = $question->feedbackcorrect['format'];
        $DB->update_record('question_answers', $answer);
        $correctid = $answer->id;

        // Save the incorrect answer - update an existing answer if possible.
        $answer = array_shift($oldanswers);
        if (!$answer) {
            $answer = new stdClass();
            $answer->question = $question->id;
            $answer->answer = '';
            $answer->feedback = '';
            $answer->id = $DB->insert_record('question_answers', $answer);
        }

        $answer->answer   = 'incorrect'; //get_string('false', 'qtype_manip');
        $answer->fraction = 0.0; // 1 - (int)$question->correctanswer;
        $answer->feedback = $this->import_or_save_files($question->feedbackincorrect,
                $context, 'question', 'answerfeedback', $answer->id);
        $answer->feedbackformat = $question->feedbackincorrect['format'];
        $DB->update_record('question_answers', $answer);
        $incorrectid = $answer->id;

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }

        debugging('QQQQQQQQQQ $question :: '. print_r($question, true));
        
        // Save question options in question_manip table
        if ($options = $DB->get_record('question_manip', array('questionid' => $question->id))) {
            $options->regex = $question->regex;
            $options->correct = $correctid;
            $options->incorrect = $incorrectid;
            $DB->update_record('question_manip', $options);
        } else {
            $options = new stdClass();
            $options->questionid    = $question->id;
            $options->regex = $question->regex;
            $options->correct = $correctid;
            $options->incorrect = $incorrectid;
            $DB->insert_record('question_manip', $options);
        }

        // $this->save_hints($question); // TODO: à confirmer - pas de hints a priori...

        return true;
    }

    /**
     * Loads the question type specific options for the question.
     */
    public function get_question_options($question) {
        global $DB, $OUTPUT;
        // Get additional information from database
        // and attach it to the question object
        if (!$question->options = $DB->get_record('question_manip',
                array('questionid' => $question->id))) {
            echo $OUTPUT->notification('Error: Missing question options!');
            return false;
        }
        // Load the answers
        if (!$question->options->answers = $DB->get_records('question_answers',
                array('question' =>  $question->id), 'id ASC')) {
            echo $OUTPUT->notification('Error: Missing question answers for manip question ' .
                    $question->id . '!');
            return false;
        }

        return true;
    }

    public function get_regex() {
        return array(
          'newline' => 'add a new line',
          'newpage' => 'add a new page',
        );
    }
    
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        //debugging('initialise_question_instance ($question, $questiondata) :: '. print_r($question, true) .' :: '. print_r($questiondata, true));
        $answers = $questiondata->options->answers;

        $question->feedbackcorrect = $answers[$questiondata->options->correct]->feedback;
        $question->feedbackincorrect = $answers[$questiondata->options->incorrect]->feedback;
        $question->feedbackcorrectformat =
                $answers[$questiondata->options->correct]->feedbackformat;
        $question->feedbackincorrectformat =
                $answers[$questiondata->options->incorrect]->feedbackformat;
        $question->correctanswerid =  $questiondata->options->correct;
        $question->incorrectanswerid = $questiondata->options->incorrect;
    }

    public function response_file_areas() {
        return array('attachment');
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('question_manip', array('questionid' => $questionid));

        parent::delete_question($questionid, $contextid);
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        //debugging('move_files!');
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, 'qtype_manip', 'graderinfo', $questionid);
        //$this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
    }
    
    public function is_usable_by_random() {
        return false;
    }
    
    public function get_possible_responses($questiondata) {
        /* TODO: soit utiliser des constantes pour les fractions (1.0 et 0.0),
         *       soit permettre au prof de spécifier les fractions. */
        return array(
            $questiondata->id => array(
                0 => new question_possible_response('correct' /* get_string('correctanswer', 'qtype_manip') */,
                        1.0 /* $questiondata->options->answers[$questiondata->options->correctanswer]->fraction*/
                        ),
                1 => new question_possible_response('incorrect' /* get_string('incorrectanswer', 'qtype_manip') */,
                        0.0 /* $questiondata->options->answers[$questiondata->options->incorrectanswer]->fraction */
                        ),
                null => question_possible_response::no_response()
            )
        );
    }
}
