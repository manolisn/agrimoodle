<?php

//require_once('L10n.php');

class block_mdeditor extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_mdeditor');
    }

	// the block can be used only while displaying courses
	// FIXME only allow usage by course teachers, managers, etc.
	public function applicable_formats() {
        return array(
            'all' => false,
            'course-view' => true,
        );
    }
	
	// allow for some customization
//	public function specialization() {
//		if (!empty($this->config->title)) {
//			$this->title = $this->config->title;
//		} else {
//			$this->config->title = 'Metadata Editor';
//		}
//
//		if (empty($this->config->button_text)) {
//			$this->config->button_text = 'Configure Metadata Editor';
//		}
//	}
	
	
    public function get_content() {

        if ($this->content !== null) {
            return $this;
        }

		$this->content = new stdClass();
        global $PAGE, $COURSE, $DB;

		// Check to see if we are in editing mode
		$canmanage = $PAGE->user_is_editing($this->instance->id);
		
		if ($canmanage) {

//			if ($this->content->oldtext) {
//				$this->content->text = $this->content->oldtext;
//				$this->content->footer = $this->content->oldfooter;
//				return $this->content;
//			}
		
			// add required JS for editing widgets
			// loading should be performed in a more dynamic fashion
			$this->page->requires->js('/local/agrimoodle/js/jquery-1.7.2.min.js');
			// NOTE change this to min version on the production server
			//$this->page->requires->js('/local/agrimoodle/js/jquery-1.7.2.dev.js');

			$this->page->requires->js('/local/agrimoodle/js/jquery-ui3.js');
			

			// include css for the above jquery widgets
			$this->page->requires->css('/local/agrimoodle/css/jquery-ui3.css');
			$this->page->requires->css('/local/agrimoodle/css/jquery.toChecklist.css');
			
			// TODO: isn't this included automagically by moodle?
			$this->page->requires->css('/blocks/mdeditor/styles.css');

			$js = get_string('dialog_datepicker_locale', 'block_mdeditor');
			if ($js) {
				$this->page->requires->js("/local/agrimoodle/js/{$js}");
			}

			$this->page->requires->js('/local/agrimoodle/js/jquery.validate.js');
			$this->page->requires->js('/local/agrimoodle/js/jquery.toChecklist.js');

			$this->page->requires->js('/local/agrimoodle/js/jquery.dform-1.0.0.min.js');
			$this->page->requires->js('/blocks/mdeditor/js/composer.js');

			$course_id = $COURSE->id;

            // supported mods and their respective records
			$resources = $DB->get_records('resource',
										array('course' => $course_id),
										'',
										'id, name');
			$pages = $DB->get_records('page',
										array('course' => $course_id),
										'',
										'id, name');

            /* supporteed mods MUST be MANUALLY specified within
             * action/params_handler.php:block_mdeditor_get_action_params():
             *  $supported_types as well! */
            $mods = array('resource' => $resources,
                          'page' =>  $pages);
            $options = $this->create_options($mods);

			// construct the localization array to pass to JS init call (say_hello)
			$b = 'block_mdeditor';
			$localization = array(
				'edit_resource_desc' => get_string('block_edit_resource_desc', $b),
				'edit_resource_button' => get_string('block_edit_resource_button', $b),
				'edit_course_button' => get_string('block_edit_course_button', $b),
				// FIXME -- is it needed? if so we need to define it in the lang file 
				// 'no_resources' => get_string('block_edit_no_resouces', $b),
				'error_fetching_data' => get_string('block_msg_error_fetching_data', $b),
			);
			$javascript_required = get_string('block_javascript_required', $b);

            // do some JS initialization...
            $course_status = $this->get_meta_state($course_id, 'course');
            $this->page->requires->js_init_call('M.block_mdeditor.init',
                                                array('course_'.$course_id,
                                                      $course_status,
                                                      $options,
                                                      $localization));

			// FIXME -- not used?
			// $edit_button = get_string('block_edit_button', 'block_mdeditor');

            // temporary form; perhaps use $OUTPUT instead

            $out = '<div id="block_mdeditor-block">'.$javascript_required.'</div>';
			$foot = '<div style="font-size:xx-small">';
			$foot .= '<div>'.get_string('block_color_legend', $b).'</div>';
			$foot .= '<div style="background-color:#cdcdcd;"> version 2.2</div>';

			$this->content->text = $out;
			$this->content->footer = $foot;
		} else {
            // when does $this->content->text get initialized? If for example,
            // on the first visit $canmanage equals false?
//			$this->content->oldtext = $this->content->text;
			$this->content->text = null;
//			$this->content->oldfooter = $this->content->footer;
			$this->content->footer = null;
		}
        return $this->content;
    }

    /*
     * @param $data: an associative array:
     *  key: a lom/ sub-directory name (eg resource, page)
     *  value: an array of records (id, name) that represent lom files within
     *      the directory specified with `key'
     *
     * @return array; each item is an array that once parsed with jQuery.dform
     *  will produce an HTML option element
     */
    protected function create_options($data) {
        $result = array();
        $prefix = 'block_status_';
        $titles = array("complete" => get_string("{$prefix}complete", 'block_mdeditor'),
                        'partial' => get_string("{$prefix}partial", 'block_mdeditor'),
                        'not_started' => get_string("{$prefix}not_started", 'block_mdeditor'));

        foreach ($data as $type => $mod) {
            foreach ($mod as $record) {
                /* create a table and use that to determine which resources'
                 * meta of each course are partially or fully completed or not
                 * started at all */
                $id = $record->id;
                $status = $this->get_meta_state($id, $type);
                $result[] = array('html' => $record->name,
                                               'class' => 'status_'.$status,
                                               'title' => $titles[$status],
                                               'value' => "{$type}_{$id}");
            }
        }
        return $result;
    }
    /*
     * Returns (without the quotes):
     * 'status_complete',
     * 'status_partial', or
     * an empty string
     * according to the state of the
     * metadata of the resource corresponding to the supplied id.
     */
    protected function get_meta_state($id, $type = 'resource') {
        $d = DIRECTORY_SEPARATOR;
        $base = dirname(__FILE__).$d.'lom'.$d.$type.$d;

        if (file_exists($base.'complete'.$d.$id.'.json')) {
            return 'complete';
        } else if (file_exists($base.'partial'.$d.$id.'.json')) {
            return 'partial';
        }
        return 'not_started';
    }

    public function instance_allow_multiple() {
        return false;
    }
	
	public function getConfigData() {
		$config = dirname(__FILE__);
	}
}
