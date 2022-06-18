<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/formslib.php');

// Form to select start and end date ranges and session time.
class dedication_block_selection_form extends moodleform {

    public function definition() {
        $mform = & $this->_form;

        $mform->addElement('header', 'general', get_string('form', 'block_dedication'));
        $mform->addHelpButton('general', 'form', 'block_dedication');

        $mform->addElement('html', html_writer::tag('p', get_string('form_text', 'block_dedication')));

        $mform->addElement('date_time_selector', 'mintime', get_string('mintime', 'block_dedication'));
        $mform->addHelpButton('mintime', 'mintime', 'block_dedication');

        $mform->addElement('date_time_selector', 'maxtime', get_string('maxtime', 'block_dedication'));
        $mform->addHelpButton('maxtime', 'maxtime', 'block_dedication');

        $limitoptions = array();
        for ($i = 1; $i <= 150; $i++) {
            $limitoptions[$i * 60] = $i;
        }
        $mform->addElement('select', 'limit', get_string('limit', 'block_dedication'), $limitoptions);
        $mform->addHelpButton('limit', 'limit', 'block_dedication');

        // Buttons.
        $this->add_action_buttons(false, get_string('submit', 'block_dedication'));
    }

}
