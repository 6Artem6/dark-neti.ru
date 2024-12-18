<?php

namespace app\controllers;

use Yii;
use yii\data\Pagination;

use app\models\data\RecordList;
use app\models\request\ReportType;


class FeedController extends BaseController
{

	public function actionIndex($discipline = '')
	{
		$has_badge = Yii::$app->session->getFlash('badge');
		if (Yii::$app->request->get('tour') == 1) {
			Yii::$app->user->identity->data->checkTour();
		}
		$discipline = (string)$discipline;
		list($list, $pages) = RecordList::getQuestionFeedList($discipline);
		$discipline_followed_list = RecordList::getDisciplineFollowedUserList();

		$report_types = ReportType::getListsByType();
		$discipline_preferred_list = RecordList::getDisciplinesPreferredList();
		$user_list = RecordList::getDisciplinesUserBestList(array_column($discipline_preferred_list, 'discipline_id'), 10);

		return $this->render('index', [
			'list' => $list,
			'pages' => $pages,
			'discipline_followed_list' => $discipline_followed_list,
			'discipline_preferred_list' => $discipline_preferred_list,
			'user_list' => $user_list,
			'report_types' => $report_types,
			'discipline' => $discipline,
			'has_badge' => $has_badge
		]);
	}

	public function actionTest()
	{
		return $this->render('test');
	}

}
