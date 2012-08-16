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

M.qtype_manip.createForm = function() {
    
}

M.qtype_manip.initUpload = function(Y, qubaid, slot) {

    /* 哎呀! Le plan : 
     * Ajouter un bloc avec les directives ... 
     * 1: uploader un fichier a la premiere question
     * 2: cliquer sur le bouton "utiliser le meme fichier partout"
     * 3: soumettre
     *
     * Au "load", on note tous les itemid ...
     * Puis quand on appuie sur le bouton magique :
     *  - on change les itemid
     *  - on change la note pour dire "fichier soumis automatiquement"
     *  - on cache le bouton du filemanager pour cette question-la?
     * Vermeilleux.
     */

     var questions = Y.all('div.manip');
     var question = questions.shift();
     
     // run only on the first relevant question of the page
     if (question.get('id') != 'q' + slot) {
         //console.log('found wrong one : ' + slot);
         if (typeof(this.slots) == 'undefined') {
             this.slots = [];
         }
         this.slots[slot] = qubaid;
         console.log(this.slots);
         return;
     }
     
     // Add the magic button
     var form = Y.one('#responseform div');
     var button = Y.Node.create(
         '<div class="que"><div class="info"></div>' + 
            '<div class="content"><div class="formulation">' + 
                '<input id="#manip-button" type="submit" value="Copy file to other questions"/>' +
                '<span>After uploading a file to the question above, click this button to copy the file to all other questions of the same type on this page.</span>' + 
         '</div></div></div>');
     form.insert(button, question.next());
     
     var button = Y.one('#manip-button');
     var buttonOnClick = function(e) {
         e.preventDefault();
         //console.log(slot + qubaid);
         
         questions.each(function() {
             // faire le tour de this.slots pour batir le varName
             // puis changer l'item id hidden en prenant la valeur courante.
             console.log(this.get('id'));
         })
         
         return false;
     }
     Y.on('click', buttonOnClick, button);

    /* L'itemid a la forme "q46:2_attachment" // 46 est le qubaid, 2 est le 
     * slotid et attachement est le nom de la variable.
     */
    var varName = 'q' + qubaid + ':' + slot + "_attachment";
    
    var hiddenItemID = Y.one('#q' + slot + ' input[name=' + varName + ']');
    //console.log('name :: ' + _input.get('name'));
    //console.log('value :: ' + _input.get('value'));

    var demo = Y.one('#q' + slot + ' input[id^=btnadd-]');
    //console.log(demo);
    //hiddenItemID.on('valuechange', function (e) {
    //demo.on('valuechange', function (e) {
    //    console.log('test positif! Nouvelle valeur : ' + hiddenItemID.get('value'));
    //});

    /*
     * 2. Pour que la page affiche les fichiers ... (plus compliqué, mais esthétique..?)
     *    Copier le meme code 
     *    
     *  CAVEAT : évidemment, ca marche pas du tout si le javascript est désactivé.
     */
    
    
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