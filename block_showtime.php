<?php
class block_showtime extends block_base {
    public function init() {
        $this->title = get_string('showtime', 'block_showtime');
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = 'Moodle Dedication';            
            } else {
                $this->title = $this->config->title;
            }
    
            if (empty($this->config->text)) {
                $this->config->text = get_string('defaulttext', 'block_showtime');
            }    
        }
    }

    public function get_content() {
        require_once('block_showtime_lib.php');
        global $USER;

        if ($this->content !== null) {
          return $this->content;
        }
    
        $Semesterstart = '1645480800';

        $this->content         =  new stdClass;
        $this->content->text   = 'Time spent on Moodle since '. gmdate("d-m-Y", $Semesterstart) . ':';
        $this->content->footer = '';

        $showtime = new block_showtime_manager('5000');
        $showFullTime = $showtime->get_student_moodletime($USER,true);
        $this->content->text .= html_writer::tag('p', block_showtime_utils::format_showtime($showFullTime));
    
        return $this->content; 
    }

    public function instance_allow_multiple() {
        return true;
      }
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.
}