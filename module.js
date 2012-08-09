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

M.qtype_manip.initUpload = function (Y) {
    
}

M.qtype_manip.initQuestionForm = function (Y) {
    
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