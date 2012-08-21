/*
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Question type class for the true-false question type.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.qtype_manip = M.qtype_manip || {};

M.qtype_manip.fields = M.qtype_manip.fields || [];
M.qtype_manip.slots = M.qtype_manip.slots || [];

M.qtype_manip.initUpload = function(Y, qubaid, slot) {

    /* 哎呀! Le plan :
     * Au "load", on note tous les itemid
     * Puis quand on appuie sur le bouton magique :
     *  - on change les itemid
     *  - on change la note pour dire "fichier soumis automatiquement"
     *  - on cache le bouton du filemanager pour cette question-la?
     *     - Non : on laisse la possibilité à l'usager de soumettre un autre fichier.
     * Vermeilleux.
     */

    /* For each question in the page, we save the relevant information */
    M.qtype_manip.fields[slot] = '#q' + slot + ' input[name=q' + qubaid + ':' + slot + '_attachment]';
    M.qtype_manip.slots.push(slot);

    var questions = Y.all('div.manip');
    var question = questions.shift();

    /* Show the button only once, after the first occurence of this question type */
    if (question.get('id') != 'q' + slot)
        return;

    // Add the magic button
    var form = Y.one('#responseform div');
    var button = Y.Node.create(
            '<div class="que"><div class="info"></div>' +
            '<div class="content"><div class="formulation">' +
            '<input id="manip-button" type="submit" value="' +
            M.util.get_string('copyfile', 'qtype_manip') + '"/>' +
            '<span>' + M.util.get_string('copyfiletext', 'qtype_manip') + '</span>' +
            '</div></div></div>');
    form.insert(button, question.next());

    var button = Y.one('#manip-button');
    var buttonOnClick = function(e) {
        e.preventDefault();
        //console.log(M.qtype_manip.slots);
        var q1id = M.qtype_manip.slots[0];
        var q1field = M.qtype_manip.fields[q1id];
        //console.log('id:'+q1id + ' field:'+q1field);
        var value = Y.one(q1field).get('value');
        //console.log('q1Value:'+value);
        var firstField = null;
        for(var i in M.qtype_manip.slots) {
            if (firstField == null) {
                firstField = i;
            } else {
                //console.log('i:'+i);
                var slot = M.qtype_manip.slots[i];
                var field = M.qtype_manip.fields[slot];
                //console.log('old : '+Y.one(field).get('value'));
                Y.one(field).set('value', value);
                //console.log('new : '+Y.one(field).get('value'));
                var textField = Y.one('#q' + slot + ' .attachment .filemanager-container > div');
                textField.set('text', 'File copied from question #' + q1id);
            }
        }
        return false;
    }

    button.on('click', buttonOnClick);

    /* L'itemid a la forme "q46:2_attachment" // 46 est le qubaid, 2 est le
     * slotid et attachement est le nom de la variable.
     */
    var varName = 'q' + qubaid + ':' + slot + "_attachment";
    var hiddenItemID = Y.one('#q' + slot + ' input[name=' + varName + ']');
    var demo = Y.one('#q' + slot + ' input[id^=btnadd-]');
}

M.qtype_manip.initQuestionForm = function(Y) {
    var setRegex = function() {
        var _select = Y.one('#id_regex'),
            _other = Y.one('#id_regexother');

        if (_select.get('value') != 'other') {
            _other.set('disabled', 'true');
        } else {
            _other.set('disabled', null);
        }
    }
    // do it on page load
    setRegex();
    // do it onChange
    Y.one('#id_regex').on('change', setRegex);
};
