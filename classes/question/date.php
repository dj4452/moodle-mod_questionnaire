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
 * This file contains the parent class for date question types.
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questiontypes
 */

namespace mod_questionnaire\question;
defined('MOODLE_INTERNAL') || die();
use \html_writer;

class date extends question {

    protected function responseclass() {
        return '\\mod_questionnaire\\responsetype\\date';
    }

    public function helpname() {
        return 'date';
    }

    /**
     * Override and return a form template if provided. Output of question_survey_display is iterpreted based on this.
     * @return boolean | string
     */
    public function question_template() {
        return 'mod_questionnaire/question_date';
    }

    /**
     * Override and return a form template if provided. Output of response_survey_display is iterpreted based on this.
     * @return boolean | string
     */
    public function response_template() {
        return 'mod_questionnaire/response_date';
    }

    /**
     * Return the context tags for the check question template.
     * @param \mod_questionnaire\responsetype\response\response $response
     * @param string $descendantdata
     * @param boolean $blankquestionnaire
     * @return object The check question context tags.
     *
     */
    protected function question_survey_display($response, $descendantsdata, $blankquestionnaire=false) {
        // Date.
        $questiontags = new \stdClass();
        if (!empty($response->answers[$this->id])) {
            $dateentered = $response->answers[$this->id][0]->value;
            $setdate = questionnaire_check_date($dateentered, false);
            if ($setdate == 'wrongdateformat') {
                $msg = get_string('wrongdateformat', 'questionnaire', $dateentered);
                $this->add_notification($msg);
            } else if ($setdate == 'wrongdaterange') {
                $msg = get_string('wrongdaterange', 'questionnaire');
                $this->add_notification($msg);
            } else {
                $response->answers[$this->id][0]->value = $setdate;
            }
        }
        $choice = new \stdClass();
        $choice->onkeypress = 'return event.keyCode != 13;';
        $choice->name = 'q'.$this->id;
        $choice->value = (isset($response->answers[$this->id][0]->value) ? $response->answers[$this->id][0]->value : '');
        $questiontags->qelements = new \stdClass();
        $questiontags->qelements->choice = $choice;
        return $questiontags;
    }

    /**
     * Return the context tags for the check response template.
     * @param \mod_questionnaire\responsetype\response\response $response
     * @return object The check question response context tags.
     */
    protected function response_survey_display($response) {
        $resptags = new \stdClass();
        if (isset($response->answers[$this->id])) {
            $answer = reset($response->answers[$this->id]);
            $resptags->content = $answer->value;
        }
        return $resptags;
    }

    /**
     * Check question's form data for valid response. Override this is type has specific format requirements.
     *
     * @param object $responsedata The data entered into the response.
     * @return boolean
     */
    public function response_valid($responsedata) {
        if (isset($responsedata->{'q'.$this->id})) {
            $checkdateresult = '';
            if ($responsedata->{'q'.$this->id} != '') {
                $checkdateresult = questionnaire_check_date($responsedata->{'q'.$this->id});
            }
            return (substr($checkdateresult, 0, 5) != 'wrong');
        } else {
            return parent::response_valid($responsedata);
        }
    }

    protected function form_length(\MoodleQuickForm $mform, $helpname = '') {
        return question::form_length_hidden($mform);
    }

    protected function form_precise(\MoodleQuickForm $mform, $helpname = '') {
        return question::form_precise_hidden($mform);
    }

    /**
     * True if question provides mobile support.
     *
     * @return bool
     */
    public function supports_mobile() {
        return true;
    }

    /**
     * @param $qnum
     * @param $fieldkey
     * @param bool $autonum
     * @return \stdClass
     * @throws \coding_exception
     */
    public function get_mobile_question_data($qnum, $autonum = false) {
        $mobiledata = parent::get_mobile_question_data($qnum, $autonum);
        $mobiledata->questionsinfo['isdate'] = true;
        return $mobiledata;
    }

    /**
     * @param $mobiledata
     * @return mixed
     */
    public function add_mobile_question_choice_data($mobiledata) {
        $mobiledata->questions = [];
        $mobiledata->questions[0] = new \stdClass();
        $mobiledata->questions[0]->id = 0;
        $mobiledata->questions[0]->choice_id = 0;
        $mobiledata->questions[0]->question_id = $this->id;
        $mobiledata->questions[0]->content = '';
        $mobiledata->questions[0]->value = null;
        return $mobiledata;
    }
}