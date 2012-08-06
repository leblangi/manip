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
 * Defines the editing form for the true-false question type.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/question/type/edit_question_form.php');


/**
 * manip question editing form definition.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_manip_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        // TODO: manip : ajouter un sélecteur d'expression régulière pour le choix de la question.
        /* $mform->addElement('select', 'regexselection',
           get_string('regexselection', 'qtype_manip'), array(
           // TODO: manip - attention, c'est la chaîne "valeur" qui est sauvegardée dans la DB
           0 => get_string('regex_1', 'qtype_manip'),
           1 => get_string('regex_2', 'qtype_manip')));
         */

        /*
        $mform->addElement('text', 'regex', get_string('regex', 'qtype_manip'), array('size' => '75'));
        $mform->setType('regex', PARAM_RAW);
        // TODO: donner de l'aide à propos des regex dans l'aide
        $mform->addHelpButton('regex', 'regex', 'qtype_manip');
        */

        // TODO: finir la connexion avec le menu déroulant (À TESTER!)
        $qtype = question_bank::get_qtype('manip');
        $mform->addElement('select', 'regex',
                get_string('regex', 'qtype_manip'), $qtype->get_regex());
        $mform->addHelpButton('regex', 'regex', 'qtype_manip');

        $mform->addElement('text', 'regexother', get_string('regexother', 'qtype_manip'), array('size' => '75'));
        $mform->setType('regexother', PARAM_RAW);
        $mform->addHelpButton('regexother', 'regexother', 'qtype_manip');

        $mform->addElement('editor', 'feedbackcorrect', get_string('feedbackcorrect', 'qtype_manip'), array('rows' => 10), $this->editoroptions);
        $mform->setType('feedbackcorrect', PARAM_RAW);
        $mform->addHelpButton('feedbackcorrect', 'feedbackcorrect', 'qtype_manip');


        $mform->addElement('editor', 'feedbackincorrect', get_string('feedbackincorrect', 'qtype_manip'), array('rows' => 10), $this->editoroptions);
        $mform->setType('feedbackincorrect', PARAM_RAW);
        $mform->addHelpButton('feedbackincorrect', 'feedbackincorrect', 'qtype_manip');
    }

    // TODO: modifier le code ici pour que les choix de "réponse" soient "correct/incorrect".. ou autre.
    // ...Si on veut permettre d'avoir des fractions spécifiques.
//    protected function get_per_answer_fields($mform, $label, $gradeoptions, &$repeatedoptions, &$answersoption) {
//        $repeated = array();
//        $repeated[] = $mform->createElement('header', 'answerhdr', $label);
//        $repeated[] = $mform->createElement('text', 'answer', get_string('answer', 'question'), array('size' => 80));
//        $repeated[] = $mform->createElement('select', 'fraction', get_string('grade'), $gradeoptions);
//        $repeated[] = $mform->createElement('editor', 'feedback', get_string('feedback', 'question'), array('rows' => 5), $this->editoroptions);
//        $repeatedoptions['answer']['type'] = PARAM_RAW;
//        $repeatedoptions['fraction']['default'] = 0;
//        $answersoption = 'answers';
//        return $repeated;
//    }

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (!empty($question->options->correct)) {
            $correctanswer = $question->options->answers[$question->options->correct];
            $question->correctanswer = ($correctanswer->fraction != 0);

            $draftid = file_get_submitted_draft_itemid('correctanswer');
            $answerid = $question->options->correct;

            $question->feedbackcorrect = array();
            $question->feedbackcorrect['format'] = $correctanswer->feedbackformat;
            $question->feedbackcorrect['text'] = file_prepare_draft_area(
                    $draftid, // draftid
                    $this->context->id, // context
                    'question', // component
                    'answerfeedback', // filarea
                    !empty($answerid) ? (int) $answerid : null, // itemid
                    $this->fileoptions, // options
                    $correctanswer->feedback // text
            );
            $question->feedbackcorrect['itemid'] = $draftid;
        }

        if (!empty($question->options->incorrect)) {
            $incorrectanswer = $question->options->answers[$question->options->incorrect];

            $draftid = file_get_submitted_draft_itemid('incorrectanswer');
            $answerid = $question->options->incorrect;

            $question->feedbackincorrect = array();
            $question->feedbackincorrect['format'] = $incorrectanswer->feedbackformat;
            $question->feedbackincorrect['text'] = file_prepare_draft_area(
                    $draftid, // draftid
                    $this->context->id, // context
                    'question', // component
                    'answerfeedback', // filarea
                    !empty($answerid) ? (int) $answerid : null, // itemid
                    $this->fileoptions, // options
                    $incorrectanswer->feedback // text
            );
            $question->feedbackincorrect['itemid'] = $draftid;
        }

        if (!empty($question->options->regex)) {
            $qtype = question_bank::get_qtype('manip');
            if (!array_key_exists($question->options->regex, $qtype->get_regex())) {
                $question->options->regexother = $question->options->regex;
                $question->options->regex = 'other';
            }
        }

        return $question;
    }

    public function qtype() {
        return 'manip';
    }

}
