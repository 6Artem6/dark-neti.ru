<?php

namespace app\controllers\api;

use Yii;
use yii\filters\{AjaxFilter, VerbFilter};

use app\controllers\BaseController;

use app\models\data\RecordInfo;

use app\widgets\user\InfoRecord;


class InfoController extends BaseController
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = [
			'class' => VerbFilter::class,
			'actions' => [
				'*'  => ['POST'],
				'index'  => ['GET'],
			],
		];
		$behaviors[] = [
			'class' => AjaxFilter::class
		];
		return $behaviors;
	}

	public $layout = false;


	public function actionIndex()
	{
		return $this->asJson([
			'status' => true,
			'message' => Yii::t('app', 'Welcome to DARK-NETi Info API!')
		]);
	}

	public function actionUser()
	{
		$username = (string)Yii::$app->request->post('username');
		$question_id = (int)Yii::$app->request->post('question_id');
		$discipline_name = (string)Yii::$app->request->post('discipline_name');
		$data = RecordInfo::getUserInfo($username, $question_id, $discipline_name);
		if (!$data) {
			return $this->asJson(false);
		}
		return $this->renderContent(
			InfoRecord::widget([
				'user_data' => $data,
			])
		);
	}
}
