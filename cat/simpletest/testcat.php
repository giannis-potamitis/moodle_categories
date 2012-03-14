<?php

require_once($CFG->dirroot . '/mod/assignment/locallib.php');
require_once($CFG->libdir.'/gradelib.php');

/*
require_once($CFG->libdir.'/eventslib.php');

require_once($CFG->libdir.'/formslib.php');

require_once($CFG->dirroot.'/calendar/lib.php'); */


class update_multiple_categories_no_marked_submission_yet_test extends UnitTestCase {
	
	protected $assignment;

	public function setUp() {
		global $DB;
	
		$this->assignment = $DB->get_record('assignment', array('id' => 49), '*', MUST_EXIST);
	}

	function test_add_many_categories_no_categories_before() {
		global $DB;
		
		// remove all previous records
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		if ($cat != null) {
			$categories = $DB->get_records('cat_category', array('catid' => $cat->id));
			foreach ($categories as $c) {
				$DB->delete_records('cat_submission', array('categoryid' => $c->id));
			}
			$DB->delete_records('cat_category', array('catid' => $cat->id));
			$DB->delete_records('cat', array('id' => $cat->id));
		}
		
		// Set data
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;
		
		$data['catdescription1'] = 'Category2';
		$data['catweight1'] = 3;
		$data['catmaxgrade1'] = 8;
		$data['remove1'] = 0;
		$data['catid1'] = -1;
		$data['priority1'] = 1;
		
		$data['catdescription2'] = 'Category3';
		$data['catweight2'] = 30;
		$data['catmaxgrade2'] = 10;
		$data['remove2'] = 0;
		$data['catid2'] = -1;
		$data['priority2'] = 2;
		
		$data['catdescription3'] = 'Category4';
		$data['catweight3'] = 15;
		$data['catmaxgrade3'] = 20;
		$data['remove3'] = 0;
		$data['catid3'] = -1;
		$data['priority3'] = 3;
		
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);
		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == 48); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == 48);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		$cur = reset($categories);
		$this->assertTrue(strcmp($cur->description, $data['catdescription1']) == 0
											&& $cur->weight == $data['catweight1'] && $cur->maxgrade == $data['catmaxgrade1']);
											
		$cur = next($categories);
		$this->assertTrue(strcmp($cur->description, $data['catdescription2']) == 0
											&& $cur->weight == $data['catweight2'] && $cur->maxgrade == $data['catmaxgrade2']);
											

		$cur = next($categories);
		$this->assertTrue(strcmp($cur->description, $data['catdescription3']) == 0
											&& $cur->weight == $data['catweight3'] && $cur->maxgrade == $data['catmaxgrade3']);											
																			
	}
	

	function test_add_many_categories_with_categories_before() {
		global $DB;
		
		
		// Set data
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;
		
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id), '*', MUST_EXIST);		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');		
		
		$i = 1;
		
		foreach ($categories as $c) {
		
			
			$data['catdescription' . $i] = $c->description;
			$data['catweight' . $i] = $c->weight;
			$data['catmaxgrade' . $i] = $c->maxgrade;
			$data['remove' . $i] = 0;
			$data['catid' . $i] = $c->id;
			$data['priority' . $i] = $i;
			
			$i++;
		}

		
		$data['catdescription4'] = 'CategoryA';
		$data['catweight4'] = 3;
		$data['catmaxgrade4'] = 15;
		$data['remove4'] = 0;
		$data['catid4'] = -1;
		$data['priority4'] = 4;
		
		$data['catdescription5'] = 'CategoryB';
		$data['catweight5'] = 7;
		$data['catmaxgrade5'] = 10;
		$data['remove5'] = 0;
		$data['catid5'] = -1;
		$data['priority5'] = 5;		
		
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);
		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == 58); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == 58);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		$i = 1;
		foreach ($categories as $c) {
			$this->assertTrue(strcmp($c->description, $data['catdescription' . $i]) == 0
											&& $c->weight == $data['catweight' . $i] && $c->maxgrade == $data['catmaxgrade' . $i]);
			$i++;
		}
												
																			
	}	
	

	function test_edit_an_arbitrary_combination_of_many_categories() {	
		global $DB;
		
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id), '*', MUST_EXIST);		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');	
		$sum = 0;
		$i = 1;
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;		
		foreach ($categories as $c) {
			
			$data['catdescription' . $i] = $c->description . ' EDITED';
			$data['catweight' . $i] = $c->weight * 2;
			$data['catmaxgrade' . $i] = $c->maxgrade * 3;
			$data['remove' . $i] = 0;
			$data['catid' . $i] = $c->id;
			$data['priority' . $i] = count($categories) - $i + 1; // inverse priorities
			
			$sum += $data['catweight' . $i];
			$i++;			
		}
		
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == $sum); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == $sum);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		
		$i = count($categories);
		foreach ($categories as $c) {	
		
			$this->assertTrue(strcmp($c->description, $data['catdescription' . $i]) == 0
											&& $c->weight == $data['catweight' . $i] && $c->maxgrade == $data['catmaxgrade' . $i]);
			$i--;
		}				
	}
	
	function test_delete_many_categories() {
		global $DB;
		
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id), '*', MUST_EXIST);		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');	
		$sum = 0;
		$i = 1;
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;
		
		// delete the first two categories (categoryA and categoryB)		
		foreach ($categories as $c) {
			
			$data['catdescription' . $i] = $c->description;
			$data['catweight' . $i] = $c->weight;
			$data['catmaxgrade' . $i] = $c->maxgrade;
			$data['remove' . $i] = ($i <= 2) ? 1 : 0;
			$data['catid' . $i] = $c->id;
			$data['priority' . $i] = $i - 2;
			
			$sum += ($i <= 2) ? 0 : $data['catweight' . $i];
			$i++;			
		}
		
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == $sum); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == $sum);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		
		$i = 3;
		foreach ($categories as $c) {	
		
			$this->assertTrue(strcmp($c->description, $data['catdescription' . $i]) == 0
											&& $c->weight == $data['catweight' . $i] && $c->maxgrade == $data['catmaxgrade' . $i]);
			$i++;
		}								
		
	}
	
}



class update_multiple_categories_with_marked_submission extends UnitTestCase {
	
	protected $assignment;
	protected $submissions; // array of submissions

	public function setUp() {
		global $DB;
	
		$this->assignment = $DB->get_record('assignment', array('id' => 51), '*', MUST_EXIST);
		$this->submissions = $DB->get_records('assignment_submissions', array('assignment' => $this->assignment->id)); 
	}

	function test_add_many_categories_no_categories_before() {
		global $DB;
		
		// remove all previous records
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		if ($cat != null) {
			$categories = $DB->get_records('cat_category', array('catid' => $cat->id));
			foreach ($categories as $c) {
				$DB->delete_records('cat_submission', array('categoryid' => $c->id));
			}
			$DB->delete_records('cat_category', array('catid' => $cat->id));
			$DB->delete_records('cat', array('id' => $cat->id));
		}
		
		// Set data
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;
		
		$data['catdescription1'] = 'Category2';
		$data['catweight1'] = 3;
		$data['catmaxgrade1'] = 8;
		$data['remove1'] = 0;
		$data['catid1'] = -1;
		$data['priority1'] = 1;
		
		$data['catdescription2'] = 'Category3';
		$data['catweight2'] = 30;
		$data['catmaxgrade2'] = 10;
		$data['remove2'] = 0;
		$data['catid2'] = -1;
		$data['priority2'] = 2;
		
		$data['catdescription3'] = 'Category4';
		$data['catweight3'] = 15;
		$data['catmaxgrade3'] = 20;
		$data['remove3'] = 0;
		$data['catid3'] = -1;
		$data['priority3'] = 3;
		
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);
		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == 48); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == 48);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		$cur = reset($categories);
		$this->assertTrue(strcmp($cur->description, $data['catdescription1']) == 0
											&& $cur->weight == $data['catweight1'] && $cur->maxgrade == $data['catmaxgrade1']);
											
		$cur = next($categories);
		$this->assertTrue(strcmp($cur->description, $data['catdescription2']) == 0
											&& $cur->weight == $data['catweight2'] && $cur->maxgrade == $data['catmaxgrade2']);
											

		$cur = next($categories);
		$this->assertTrue(strcmp($cur->description, $data['catdescription3']) == 0
											&& $cur->weight == $data['catweight3'] && $cur->maxgrade == $data['catmaxgrade3']);	
											
		$sum = $assignment->grade;
											
		// Test submissions
		

		//$newassignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);

		foreach ($this->submissions as $oldsub) {

			$new = $DB->get_record('assignment_submissions', array('id' => $oldsub->id), '*', MUST_EXIST);
			
			$oldmax = $this->assignment->grade;
			$newmax = $sum;
			
			$oldgrade = $oldsub->grade;
			$newgrade = ($newmax * $oldgrade) / $oldmax;
			
			$this->assertTrue(round($new->grade,2) == round($newgrade,2));
			
			// check that there no any cat_submissions
			$cat_sub = $DB->get_records('cat_submission', array('ass_subid' => $new->id));
			$this->assertTrue($cat_sub == null);
			
		}										
																			
	}
	

	function test_add_many_categories_with_categories_before() {
		global $DB;
		
		
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id), '*', MUST_EXIST);		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');	
		
		// this test makes more sense if the submissions were marked using the multiple categories
		// so lets fill DB with those data first		
		$ratio = 0.5;
		foreach ($this->submissions as $submission) {
			
			$sumweight = 0;
			foreach ($categories as $c) {
			
				$grade = $ratio * $c->maxgrade;
				$thatweight = $ratio * $c->weight;
				$sumweight += $thatweight;
				$DB->insert_record('cat_submission', array('categoryid' => $c->id, 'ass_subid' => $submission->id, 'grade' => $grade, 'feedback' => 'unitest'));
		
				$ratio += 0.1;
			}
			
			$submission->grade = $sumweight;
			$DB->update_record('assignment_submissions', $submission);
		}		
		
		// Set data
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;
		
		//$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id), '*', MUST_EXIST);		
		//$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');		
		
		$i = 1;
		
		foreach ($categories as $c) {
			
			$data['catdescription' . $i] = $c->description;
			$data['catweight' . $i] = $c->weight;
			$data['catmaxgrade' . $i] = $c->maxgrade;
			$data['remove' . $i] = 0;
			$data['catid' . $i] = $c->id;
			$data['priority' . $i] = $i;
			
			$i++;
		}

		
		$data['catdescription4'] = 'CategoryA';
		$data['catweight4'] = 3;
		$data['catmaxgrade4'] = 15;
		$data['remove4'] = 0;
		$data['catid4'] = -1;
		$data['priority4'] = 4;
		
		$data['catdescription5'] = 'CategoryB';
		$data['catweight5'] = 7;
		$data['catmaxgrade5'] = 10;
		$data['remove5'] = 0;
		$data['catid5'] = -1;
		$data['priority5'] = 5;		
		
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);
		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == 58); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == 58);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		$i = 1;
		foreach ($categories as $c) {
			$this->assertTrue(strcmp($c->description, $data['catdescription' . $i]) == 0
											&& $c->weight == $data['catweight' . $i] && $c->maxgrade == $data['catmaxgrade' . $i]);
			$i++;
		}
		

		//$sum = $assignment->grade;
											
		// Test submissions
		

		//$newassignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);

		foreach ($this->submissions as $oldsub) {

			$new = $DB->get_record('assignment_submissions', array('id' => $oldsub->id), '*', MUST_EXIST);
			
			$newgrade = $oldsub->grade + (0.4 * $data['catweight4']) + (0.4 * $data['catweight5']); 
			
			$this->assertTrue(round($new->grade,2) == round($newgrade, 2));
			
			// check correctness of cat_submissions
			$i = 1;
			foreach ($categories as $c) {
				if ($i <= 3) {
					$i++;
					continue;
				}
				$cat_sub = $DB->get_record('cat_submission', array('ass_subid' => $new->id, 'categoryid' => $c->id));
				$this->assertTrue($cat_sub != null);
				
				$this->assertTrue(round($cat_sub->grade,2) == round((0.4 * $data['catmaxgrade' . $i]),2));				
				
				$i++;
			}
		
		}	
																													
	}	
	

	function test_edit_an_arbitrary_combination_of_many_categories() {	
		global $DB;
		
		
		
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id), '*', MUST_EXIST);		
		$oldcategories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');	
		$sum = 0;
		$i = 1;
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;		
		foreach ($oldcategories as $c) {
			
			$data['catdescription' . $i] = $c->description . ' EDITED';
			$data['catweight' . $i] = $c->weight * 2;
			$data['catmaxgrade' . $i] = $c->maxgrade * 3;
			$data['remove' . $i] = 0;
			$data['catid' . $i] = $c->id;
			$data['priority' . $i] = count($oldcategories) - $i + 1; // inverse priorities
			
			$sum += $data['catweight' . $i];
			$i++;
			

						
		}
		
		$old_catsubs = array();
		foreach ($this->submissions as $sub) {
			$old_catsubs[$sub->id] = array();
			$thosecats = array();
			foreach ($oldcategories as $c) {
				$thosecats[$c->id] = $DB->get_record('cat_submission', array('categoryid' => $c->id, 'ass_subid' => $sub->id), '*', MUST_EXIST);
			}
			$old_catsubs[$sub->id] = $thosecats;
		}
		
			
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == $sum); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == $sum);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		
		$i = count($categories);
		foreach ($categories as $c) {	
		
			$this->assertTrue(strcmp($c->description, $data['catdescription' . $i]) == 0
											&& $c->weight == $data['catweight' . $i] && $c->maxgrade == $data['catmaxgrade' . $i]);
			$i--;
		}
		
		
		
		// Test submissions
		

		foreach ($this->submissions as $oldsub) {
		
			$finalgrade = $oldsub->grade;

			$new = $DB->get_record('assignment_submissions', array('id' => $oldsub->id), '*', MUST_EXIST);
			
			//$sumweight = 0;
			
			$old_catsub = $old_catsubs[$oldsub->id];
			
			foreach ($oldcategories as $oc) {
				$newc = $DB->get_record('cat_category', array('id' => $oc->id), '*', MUST_EXIST);
				$newsubcat = $DB->get_record('cat_submission', array('categoryid' => $oc->id, 'ass_subid' => $oldsub->id), '*', MUST_EXIST);
				$oldsubcat = $old_catsub[$oc->id];
				
				$newgrade = ($oldsubcat->grade * $newc->maxgrade) / $oc->maxgrade;
				$this->assertTrue(round($newgrade, 2) == round($newsubcat->grade, 2));
				
				$finalgrade -= ($oldsubcat->grade * $oc->weight) / $oc->maxgrade; 
				$finalgrade += ($newsubcat->grade * $newc->weight) / $newc->maxgrade;
			
			}
			
			
			$this->assertTrue(round($finalgrade, 2) == round($new->grade, 2));

		
		}							
	}
	
	function test_delete_many_categories() {
		global $DB;
		
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id), '*', MUST_EXIST);		
		$oldcategories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');	
		$sum = 0;
		$i = 1;
		$data = array();
		$data['rank'] = 0;
		$data['multiplecat'] = 1;
		
		$deleteids = array();
		// delete the first two categories (categoryA and categoryB)		
		foreach ($oldcategories as $c) {
			
			$data['catdescription' . $i] = $c->description;
			$data['catweight' . $i] = $c->weight;
			$data['catmaxgrade' . $i] = $c->maxgrade;
			$data['remove' . $i] = ($i <= 2) ? 1 : 0;
			$data['catid' . $i] = $c->id;
			$data['priority' . $i] = $i - 2;
			
			if ($i<= 2) {
			 $deleteids[] = $c->id;
			}
			
			$sum += ($i <= 2) ? 0 : $data['catweight' . $i];
			$i++;			
		}
		
		
		$old_catsubs = array();
		foreach ($this->submissions as $sub) {
			$old_catsubs[$sub->id] = array();
			$thosecats = array();
			foreach ($oldcategories as $c) {
				$thosecats[$c->id] = $DB->get_record('cat_submission', array('categoryid' => $c->id, 'ass_subid' => $sub->id), '*', MUST_EXIST);
			}
			$old_catsubs[$sub->id] = $thosecats;
		}		
		
		// Apply changes
		update_multiple_categories($this->assignment->id, $data);		
		
		// Test changes
		$cat = $DB->get_record('cat', array('assignmentid' => $this->assignment->id));
		$this->assertTrue($cat != null && $cat->total == $sum); // $cat->nextpriority not needed so remains always 1
		
		$assignment = $DB->get_record('assignment', array('id' => $this->assignment->id), '*', MUST_EXIST);
		$this->assertTrue($assignment->grade == $sum);
		
		$categories = $DB->get_records('cat_category', array('catid' => $cat->id), 'priority ASC');
		
		
		$i = 3;
		foreach ($categories as $c) {	
		
			$this->assertTrue(strcmp($c->description, $data['catdescription' . $i]) == 0
											&& $c->weight == $data['catweight' . $i] && $c->maxgrade == $data['catmaxgrade' . $i]);
			$i++;
		}
		
		// Test submissions
		foreach ($this->submissions as $oldsub) {
		
			$finalgrade = $oldsub->grade;

			$new = $DB->get_record('assignment_submissions', array('id' => $oldsub->id), '*', MUST_EXIST);
			

			
			$old_catsub = $old_catsubs[$oldsub->id];
			
			foreach ($oldcategories as $oc) {
				
				$remove = false;
				/*
				if (strcmp($oc->description, 'CategoryA EDITED') == 0 || strcmp($oc->description, 'CategoryB EDITED') == 0) {
					$remove = true;
				}*/
				
				if (in_array($oc->id, $deleteids, true)) {
					$remove = true;
				}
				
				$oldsubcat = $old_catsub[$oc->id];
				if (!$remove) {
					$newc = $DB->get_record('cat_category', array('id' => $oc->id), '*', MUST_EXIST);
					$newsubcat = $DB->get_record('cat_submission', array('categoryid' => $oc->id, 'ass_subid' => $oldsub->id), '*', MUST_EXIST);
				
					$newgrade = ($oldsubcat->grade * $newc->maxgrade) / $oc->maxgrade;
					$this->assertTrue(round($newgrade, 2) == round($newsubcat->grade, 2));
				}
				else {
					$this->assertTrue($DB->get_record('cat_submission', array('categoryid' => $oc->id, 'ass_subid' => $oldsub->id)) == null);
				}
				
				
				$finalgrade -= ($oldsubcat->grade * $oc->weight) / $oc->maxgrade; 
				if (!$remove) {
					$finalgrade += ($newsubcat->grade * $newc->weight) / $newc->maxgrade;
				}
			
			}
			
			
			$this->assertTrue(round($finalgrade, 2) == round($new->grade, 2));

		
		}												
		
	}
	
}
