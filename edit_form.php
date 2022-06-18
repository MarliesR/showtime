<?php

class block_showtime_edit_form extends block_edit_form {
        
    protected function specific_definition($mform) {
        
        // Section header title according to language file.
        $mform->addElement('header', 'config_header');

        // A sample string variable with a default value.
        $mform->setDefault('config_date', 'default value');
        $mform->setType('config_date', PARAM_RAW);    
        $mform->addElement('date_selector', 'config_assesstimefinish', 'Semesterstart'); 
        
        $this->add_action_buttons();
        //hi
    }

}