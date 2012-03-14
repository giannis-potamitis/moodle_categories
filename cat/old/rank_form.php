<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}


class local_cat_rank_form extends moodleform {

    function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;
				
				$mform->addElement('header', 'groupname',get_string('group_name', 'local_cat'));
				$group = array();
				$group[] = &$mform->createElement('text', 'rankname', get_string('group_name', 'local_cat'));
				$group[] = &$mform->createElement('submit', 'change', get_string('updatename', 'local_cat'));
				$mform->addGroup($group, 'grouparray', '', array(' '), false);
    }
    
    function validation($data, $files) {
    	global $DB;
    
    	$errors = parent::validation($data, $files);
    	if (empty($data['rankname'])) {
    		$errors['grouparray'] = get_string('rank_namenotempty', 'local_cat');
    	}
    	return $errors;
    }    
}

class local_cat_ranks_form extends moodleform {
		
    function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;
        $data = &$this->_customdata;
				$mform->addElement('header', 'groupname');
				$ranks = $data->ranks;
				$i = 0;
				
				// the description label
				$dis = array();
				//$dis[] = &$mform->createElement('static', 'spaces', '', '   ');
				//$html = '&nbsp;&nbsp;&nbsp;';
				//$dis[] = &$mform->createElement('html', $html);
				$dis[] = &$mform->createElement('static', 'descriptionlabel', '', '<b>' . get_string('description', 'local_cat') . '</b> ');
				$mform->addGroup($dis, 'dislabel', '',array(' '), false);
				
				$total = count($ranks);
										
				
				foreach ($ranks as $r) {
					$i++;
					$mform->addElement('hidden', 'id'. $r->id);
					$group = array();
					$group[] = &$mform->createElement('static', 'num' . $r->id, '', '<b>' . $i . ')</b> ');
					$group[] = &$mform->createElement('text', 'description' . $r->id, get_string('description', 'local_cat'));
					$group[] = &$mform->createElement('submit', 'update' . $r->id, get_string('update', 'local_cat'));					
					
					$attr['onclick'] = 'return confirm("' . get_string('ranks_confirmdelete', 'local_cat') . '")';
					if ($total <= 2) {
						$attr['disabled'] =  'disabled';
					}
					$group[] = &$mform->createElement('submit', 'delete' . $r->id, get_string('delete', 'local_cat'), $attr);
					
					$attr = array();
					if ($r->priority == 1) {
						$attr = array ('disabled' => 'disabled');
					}
					$group[] = &$mform->createElement('submit', 'up' . $r->id, get_string('subup', 'local_cat'), $attr);
					
					$attr = array();
					if ($r->priority == $total) {
						$attr = array ('disabled' => 'disabled');
					}
					$group[] = &$mform->createElement('submit', 'down' . $r->id, get_string('subdown', 'local_cat'), $attr);												
					
					$mform->addGroup($group, 'groupranks' . $r->id, /*'(' . $i . ') ' . get_string('description', 'local_cat')*/'', array(' '), false);
					
				}
    }
    
    function validation($data, $files) {
    	global $DB;
    	$errors = parent::validation($data, $files);
    	
			$custom = &$this->_customdata;
			$total = count($custom->ranks);
			foreach ($custom->ranks as $r) {
				$update = 'update' . $r->id;
				$des = 'description' . $r->id;
				$group = 'groupranks' . $r->id;
    		$delete = 'delete' . $r->id;
    		$up = 'up' . $r->id;
    		$down = 'down' . $r->id;				     	
    		if (empty($data[$des]) && isset($data[$update])) {
    			$errors[$group] = get_string('ranks_descriptionnotempty', 'local_cat');
    		
    		} else if(isset($data[$delete]) && count($custom->ranks) <= 2) {
    			$errors[$group] = get_string('nodeletelimit', 'local_cat');
    		
    		} else if(isset($data[$up]) && $r->priority == 1) {
    			$errors[$group] = get_string('nofurtherup', 'local_cat');
    		
    		} else if(isset($data[$down]) && $r->priority == $total) {
    			$errors[$group] = get_string('nofurtherdown', 'local_cat');
    		}
    	}
    	return $errors;
    } 
}

class local_cat_add_form extends moodleform {

    function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;
				$newdis = array();
				$newdis[] = &$mform->createElement('static', 'newdescriptionlabel', '', '<b>' . get_string('new_description', 'local_cat') . '</b> ');
				$mform->addGroup($newdis, 'newdisarray', '', array(' '), false);		
				$group = array();
				$group[] = &$mform->createElement('text', 'newrank', '');
				$group[] = &$mform->createElement('submit', 'addnew', get_string('rankadd', 'local_cat'));
				$mform->addGroup($group, 'addarray', '', array(' '), false);
    }
    
    function validation($data, $files) {
    	global $DB;
    
    	$errors = parent::validation($data, $files);
    	if (empty($data['newrank'])) {
    		$errors['addarray'] = get_string('rank_givedescription', 'local_cat');
    	}
    	return $errors;
    }    
}


class local_cat_list_form extends moodleform {
	function definition() {
		global $DB, $CFG;
	
		$frm = &$this->_form;
		$frm->addElement('header', 'items');
		$i = 0;
		$rank_groups = $this->_customdata->rank_groups;
		$cid = $this->_customdata->cid;
		$privilege = $this->_customdata->privilege;
		/*
		$delurl = $CFG->wwwroot . '/pix/giannis/delete.gif';
		$title = get_string('delete', 'local_cat');
		$delhtml = '  <img src="' . $url . '" alt="' . $title . '" onclick=' . $attr['onclick'] . ' style="cursor: pointer" title="' . $title . '"/>';	
		$group[] = &$frm->createElement('static', 'linkitems' . $g->id, '', $html);*/
		
		foreach ($rank_groups as $g) {
			$group = array();
			$i++;
			$str = $g->name . ' (';
			$ranks = $DB->get_records('cat_ranks', array('rankid' => $g->id));
			$first = true;
			foreach ($ranks as $r) {
				if ($first) {
					$first = false;
				}
				else {
					$str .= ', ';
				}
				$str .= $r->description;
			}
			$str .= ')';
	
			$r_url = $CFG->wwwroot . '/local/cat/editranks.php?rankid=' . $g->id . '&cid=' . $cid . '&privilege=' . $privilege;
			$html = '<b>' . $i . ')</b> <a href="' . $r_url . '">' . $str . '</a>';
			//$group[] = &$frm->createElement('html', $html);
			$group[] = &$frm->createElement('static', 'linkitems' . $g->id, '', $html);
			$attr['onclick'] = 'return confirm("' . get_string('ranks_confirmdeletegroup', 'local_cat') . '")';
			$group[] = &$frm->createElement('submit', 'delete' . $g->id, get_string('delete', 'local_cat'), $attr);
			
			$frm->addGroup($group, 'listarray', '', array(' '), false);
		}
		
		if (count($rank_groups) == 0) {
			$frm->addElement('static', 'nothingexist', '', get_string('nothingtodisplay', 'local_cat'));
		}	
	}
}

class local_cat_add_group_form extends moodleform {

    function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;
				$newdis = array();
				$newdis[] = &$mform->createElement('static', 'newgrouplabel', '', '<b>' . get_string('new_group', 'local_cat') . '</b> ');
				$mform->addGroup($newdis, 'labelarray', '', array(' '), false);		
				$group = array();
				$group[] = &$mform->createElement('text', 'newgroup', '');
				$group[] = &$mform->createElement('submit', 'addnewgroup', get_string('rankadd', 'local_cat'));
				$mform->addGroup($group, 'newgrouparray', '', array(' '), false);
    }
    
    function validation($data, $files) {
    	global $DB;
    
    	$errors = parent::validation($data, $files);
    	if (empty($data['newgroup'])) {
    		$errors['newgrouparray'] = get_string('rank_givename', 'local_cat');
    	}
    	return $errors;
    }    
}
