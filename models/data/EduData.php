<?php

namespace app\models\data;

use Yii;
use yii\base\Model;

use app\models\edu\{
	StudentGroup, Teacher,
	Discipline, Schedule, LessonType,
	DisciplineToChair,
	GroupToDiscipline,
	TeacherToChair,
	TeacherToDiscipline,
	TeacherToGroupToDiscipline,
	Faculty, Chair,
	Department, Level,
};
use app\models\helpers\{
	RequestHelper, UserHelper,
	HtmlHelper, TextHelper
};


class EduData extends Model
{

	public function updateGroups()
	{
		$group_list = $this->getGroups();
		foreach ($group_list as $faculty_id => $group_by_faculty) {
			foreach ($group_by_faculty as $department_id => $group_by_department) {
				foreach ($group_by_department as $level_id => $group_by_level) {
					foreach ($group_by_level as $course => $group_by_course) {
						foreach ($group_by_course as $group) {
							$model = new StudentGroup;
							$model->group_name = $group['group_name'];
							$model->has_schedule = $group['has_schedule'];
							$model->faculty_id = $faculty_id;
							$model->department_id = $department_id;
							$model->level_id = $level_id;
							$model->course = $course;
							$model->save();
						}
					}
				}
			}
		}
	}

	public function updateGroupsSchedule(bool $registerd_only = false, bool $schedule_only = false)
	{
		$year = UserHelper::getYear();
		$semestr = UserHelper::getSemestr();
		if ($registerd_only) {
			$group_list = StudentGroup::getListRegistered();
		} else {
			$group_list = StudentGroup::getListWithSchedule();
		}
		foreach ($group_list as $group_id => $group_name) {
			// echo $group_name . "\n";
			list($schedule, $teacher_to_discipline) = $this->getGroupSchedule($group_name);
			if (!$schedule_only) {
				foreach ($teacher_to_discipline as $record) {
					$discipline = new Discipline;
					$discipline->discipline_name = $record['discipline_name'];
					$discipline->save();
					$discipline = Discipline::findByName($record['discipline_name']);

					if ($record['teachers']) {
						foreach ($record['teachers'] as $teacher_data) {
							$teacher = Teacher::findById($teacher_data['teacher_id']);
							if (empty($teacher)) {
								$teacher = new Teacher;
								$teacher->teacher_name = $teacher_data['teacher_name'];
								$teacher->teacher_id = $teacher_data['teacher_id'];
								$teacher->is_checked = true;
								$teacher->save();

								$teacher = Teacher::findById($teacher_data['teacher_id']);
								$this->getTeacherFio($teacher);
							}
							$t_to_d = new TeacherToDiscipline;
							$t_to_d->teacher_id = $teacher->id;
							$t_to_d->discipline_id = $discipline->id;
							$t_to_d->save();

							$t_to_g_to_d = new TeacherToGroupToDiscipline;
							$t_to_g_to_d->teacher_id = $teacher->id;
							$t_to_g_to_d->group_id = $group_id;
							$t_to_g_to_d->discipline_id = $discipline->id;
							$t_to_g_to_d->year = $year;
							$t_to_g_to_d->semestr = $semestr;
							$t_to_g_to_d->save();
						}
					}
					$g_to_d = new GroupToDiscipline;
					$g_to_d->group_id = $group_id;
					$g_to_d->discipline_id = $discipline->id;
					$g_to_d->year = $year;
					$g_to_d->semestr = $semestr;
					$g_to_d->save();
				}
			}
			foreach ($schedule as $discipline_name => $schedule_week) {
				foreach ($schedule_week as $day => $lesson_types) {
					foreach ($lesson_types as $lesson_type => $weeks) {
						$weeks = array_unique($weeks);
						sort($weeks);
						$week_start = $weeks[0];
						$week_end = $weeks[count($weeks) - 1];
						if (count($weeks) > 1) {
							$week_offset = $weeks[1] - $weeks[0];
						} else {
							$week_offset = 0;
						}

						$discipline = Discipline::findByName($discipline_name);
						$schedule_record = new Schedule;
						$schedule_record->group_id = $group_id;
						$schedule_record->discipline_id = $discipline->id;
						$schedule_record->lesson_type = $lesson_type;
						$schedule_record->day = $day;
						$schedule_record->week_start = $week_start;
						$schedule_record->week_end = $week_end;
						$schedule_record->week_offset = $week_offset;
						$schedule_record->year = $year;
						$schedule_record->semestr = $semestr;
						$schedule_record->save();
					}
				}
			}
		}
	}

	public function getGroups()
	{
		$helper = new RequestHelper;
		$helper->baseUrl = Yii::$app->params['mainUrl'];
		$helper->url = "/studies/schedule/schedule_classes";
		$helper->method = "GET";

		$content = $helper->getContent();

		$document = new \DOMDocument;
		$internalErrors = libxml_use_internal_errors(true);
		$document->loadHTML($content);
		libxml_use_internal_errors($internalErrors);
		$xpath = new \DOMXpath($document);

		$faculty_list = Faculty::getList();
		$level_list = Level::getListIndexByName();
		$level_list = Level::getListIndexByName();
		$department_list = Department::getListIndexByName();
		$group_list = [];
		foreach ($faculty_list as $faculty_id => $name) {
			$groups_list_div = $xpath->query("//div[@data-id={$faculty_id}]")->item(0);
			$department_list_div = $xpath->query("./div[@class='schedule__faculty-type']", $groups_list_div);
			foreach ($department_list_div as $department_div) {
				$title = $xpath->query("./div[@class='schedule__faculty-type__title']", $department_div)->item(0)->textContent;
				$department_id = $department_list[$title] ?? null;
				$level_div_list = $xpath->query("./div[@class='schedule__faculty-type__subtitle']", $department_div);
				foreach ($level_div_list as $l => $level_div) {
					$subtitle = $xpath->query("./div[@class='schedule__faculty-type__subtitle']", $department_div)->item($l)->textContent;
					$level_id = $level_list[$subtitle] ?? null;
					$level_courses_div = $xpath->query("./div[@class='schedule__faculty-courses']", $department_div)->item($l);
					$level_courses_list_div = $xpath->query("./div[@class='schedule__faculty-course']", $level_courses_div);
					foreach ($level_courses_list_div as $course_div) {
						$course = $xpath->query("./label[@class='schedule__faculty-course__title']", $course_div)->item(0)->textContent;
						$course = intval($course);
						$groups_elems = $xpath->query("./div[@class='schedule__faculty-groups']/*", $course_div);
						foreach ($groups_elems as $group) {
							$group_list[$faculty_id][$department_id][$level_id][$course][] = [
								'group_name' => $group->textContent,
								'has_schedule' => ($group->tagName == 'a'),
							];
						}
					}
				}
			}
		}
		return $group_list;
	}

	public function getGroupSchedule(string $group_name)
	{
		$lesson_types = LessonType::getListByName();
		$helper = new RequestHelper;
		$helper->baseUrl = Yii::$app->params['mainUrl'];
		$helper->url = "studies/schedule/schedule_classes/schedule";
		$helper->method = "GET";
		$helper->data = [
			'group' => $group_name,
			'print' => 'true',
		];

		$content = $helper->getContent();

		$document = new \DOMDocument;
		$internalErrors = libxml_use_internal_errors(true);
		$document->loadHTML($content);
		libxml_use_internal_errors($internalErrors);
		$xpath = new \DOMXpath($document);

		$week_max = 18;
		$schedule = [];
		$teacher_to_discipline = [];
		$schedule_table = $xpath->query("//div[@class='schedule__table-body']")->item(0);
		$schedule_days = $xpath->query("./div[@class='schedule__table-row']", $schedule_table);
		$i = 0;
		foreach ($schedule_days as $day => $schedule_day) {
			$day++;
			$schedule_items = $xpath->query("./div[@class='schedule__table-cell'][2]/".
				"div[@class='schedule__table-row']/div[@class='schedule__table-cell'][2]/".
				"div[@class='schedule__table-row']/div/div[@class='schedule__table-item']", $schedule_day);
			foreach ($schedule_items as $item) {
				$item_text = $item->textContent;
				if (!HtmlHelper::isEmptyHtml($item_text)) {
					$lesson_type = 0;
					$type_block = $xpath->query("./span[@class='schedule__table-typework']", $item);
					if ($type_block->length) {
						$type_text = $type_block->item(0)->textContent;
						$type_text = trim($type_text);
						if (!empty($type_text)) {
							$lesson_type = $lesson_types[$type_text];
						}
					}
					$i++;
					$weeks = [];
					$span_label = $xpath->query("./span[@class='schedule__table-label']", $item);
					if ($span_label->length) {
						$weeks_item = $item->removeChild($span_label->item(0));
						$weeks_text = $weeks_item->textContent;
						$weeks_text = trim($weeks_text);
						if (str_contains($weeks_text, 'недели')) {
							$weeks_text = str_replace('недели', '', $weeks_text);
							$weeks = explode(' ', $weeks_text);
							foreach ($weeks as $key => $value) {
								$weeks[$key] = (int)$weeks[$key];
								if (empty($weeks[$key])) {
									unset($weeks[$key]);
								}
							}
						} elseif ($weeks_text == 'по чётным') {
							for ($w = 2; $w <= $week_max; $w += 2) {
								$weeks[] = $w;
							}
						} elseif ($weeks_text == 'по нечётным') {
							for ($w = 1; $w <= $week_max; $w += 2) {
								$weeks[] = $w;
							}
						}
					} else {
						for ($w = 1; $w <= $week_max; ++$w) {
							$weeks[] = $w;
						}
					}

					$span_typework = $xpath->query("./span[@class='schedule__table-typework']", $item);
					if ($span_typework->length) {
						$item->removeChild($span_typework->item(0));
					}
					$class = $xpath->query("./span[@class='schedule__table-class']", $item);
					if ($class->length) {
						$item->removeChild($class->item(0));
					}
					$teacher_data = [];
					$teacher_elems = $xpath->query("./a", $item);
					if ($teacher_elems->length) {
						foreach ($teacher_elems as $teacher_elem) {
							$teacher_elem = $item->removeChild($teacher_elem);
							$teacher_name = $teacher_elem->textContent;
							$link = $teacher_elem->getAttribute('href');
							$link_parts = explode('/', $link);
							$teacher_id = array_pop($link_parts);
							$teacher_data[] = [
								'teacher_name' => $teacher_name,
								'teacher_id' => $teacher_id,
							];
						}
					}
					$discipline_name = str_replace(['·', ','], '', $item->textContent);
					$discipline_name = trim($discipline_name);
					$discipline_name = str_replace('&nbsp', '', $discipline_name);
					$discipline_name = explode(';', $discipline_name)[0];
					if ($discipline_name) {
						$teacher_to_discipline[] = [
							'discipline_name' => $discipline_name,
							'teachers' => $teacher_data,
						];
						if (!empty($weeks)) {
							if (empty($schedule[$discipline_name][$day][$lesson_type])) {
								$schedule[$discipline_name][$day][$lesson_type] = [];
							}
							$schedule[$discipline_name][$day][$lesson_type] = array_merge(
								$schedule[$discipline_name][$day][$lesson_type],
								$weeks
							);
						}
					}
				}
			}
		}
		return [ $schedule, $teacher_to_discipline ];
	}

	public function getTeachersFio()
	{
		$teacher_list = Teacher::find()
			->where(['IS', 'teacher_fullname', NULL])
			->all();
		foreach ($teacher_list as $teacher_id => $teacher) {
			$this->getTeacherFio($teacher);
		}
	}

	public function getTeacherFio(Teacher $teacher)
	{
		$helper = new RequestHelper;
		$helper->baseUrl = Yii::$app->params['ciuUrl'];
		$helper->url = "kaf/persons/" . $teacher->teacher_id;
		$helper->method = "GET";
		$content = $helper->getContent();

		$document = new \DOMDocument;
		$internalErrors = libxml_use_internal_errors(true);
		$document->loadHTML($content);
		libxml_use_internal_errors($internalErrors);
		$xpath = new \DOMXpath($document);

		$lastname = $xpath->query("//div[@class='header__personinfo-lastname']")->item(0);
		$name = $xpath->query("//div[@class='header__personinfo-name']")->item(0);
		if (!empty($lastname) and !empty($name)) {
			$full_fio = $lastname->textContent . ' ' . $name->textContent;
			$teacher->teacher_fullname = $full_fio;
			$teacher->save();
			// echo $full_fio . "\n";
		}
	}

	public function getChairs()
	{
		$helper = new RequestHelper;
		$helper->baseUrl = Yii::$app->params['mainUrl'];
		$helper->url = "edu/chairs";
		$helper->method = "GET";
		$content = $helper->getContent();

		$document = new \DOMDocument;
		$internalErrors = libxml_use_internal_errors(true);
		$document->loadHTML($content);
		libxml_use_internal_errors($internalErrors);
		$xpath = new \DOMXpath($document);

		$faculty_list = $xpath->query("//div[@class='faculty-info']");
		foreach ($faculty_list as $faculty) {
			$title = $xpath->query("./h3/a", $faculty)->item(0)->textContent;
			$title = TextHelper::get_string_between($title, '(', ')');
			$faculty_record = Faculty::findOne(['faculty_shortname' => $title]);

			$chairs = $xpath->query("./div/a", $faculty);
			foreach ($chairs as $chair) {
				$chair_string = $chair->textContent;
				$chair_shortname = TextHelper::get_string_between($chair_string, '(', ')');
				$chair_fullname = str_replace(" ({$chair_shortname})", '', $chair_string);

				$chair_model = new Chair;
				$chair_model->faculty_id = $faculty_record->faculty_id;
				$chair_model->chair_fullname = $chair_fullname;
				$chair_model->chair_shortname = $chair_shortname;
				$chair_model->save();
			}
		}
	}

	public function getTeacherToChair()
	{
		$helper = new RequestHelper;
		$helper->baseUrl = Yii::$app->params['mainUrl'];
		$helper->url = "phone/chair";
		$helper->method = "GET";
		$content = $helper->getContent();

		$document = new \DOMDocument;
		$internalErrors = libxml_use_internal_errors(true);
		$document->loadHTML($content);
		libxml_use_internal_errors($internalErrors);
		$xpath = new \DOMXpath($document);

		$chair_list = $xpath->query("//div[contains(@class,'search-result__item')]");
		foreach ($chair_list as $chair) {
			$title = $xpath->query("./div[@class='search-result__head']/div/div/a/span", $chair)->item(0)->textContent;
			$title = TextHelper::get_string_between($title, '(', ')');
			if (empty($title)) continue;
			$chair_record = chair::findOne(['chair_shortname' => $title]);
			if (empty($chair_record)) continue;

			$teacher_list = $xpath->query("./div[@class='search-result__content']/table[2]/tbody/tr/td[1]/a", $chair);
			foreach ($teacher_list as $teacher) {
				$teacher_link = $teacher->getAttribute('href');
				$parts = parse_url($teacher_link);
				parse_str($parts['query'], $query);
				$teacher_id = $query['request'];
				$teacher_record = Teacher::findOne($teacher_id);
				if (empty($teacher_record)) continue;

				$t_to_c = new TeacherToChair;
				$t_to_c->teacher_id = $teacher_id;
				$t_to_c->chair_id = $chair_record->chair_id;
				$t_to_c->save();
			}
		}
	}

	public function getDisciplineToChair()
	{
		$teacher_list = Teacher::find()
			->innerJoinWith('chair')
			->innerJoinWith('disciplines')
			->all();
		foreach ($teacher_list as $teacher) {
			foreach ($teacher->disciplines as $discipline) {
				$d_to_c = new DisciplineToChair;
				$d_to_c->discipline_id = $discipline->discipline_id;
				$d_to_c->chair_id = $teacher->chair->chair_id;
				$d_to_c->save();
			}
		}
	}

}
