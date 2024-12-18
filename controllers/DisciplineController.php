<?php

namespace app\controllers;

use Yii;

use app\models\edu\Discipline;
use app\models\search\DisciplineSearch;


class DisciplineController extends BaseController
{

	public function actionIndex()
	{
		$searchModel = new DisciplineSearch;
		$provider = $searchModel->search(Yii::$app->request->get());
		return $this->render('index', [
			'searchModel' => $searchModel,
			'pages' => $searchModel->pages,
			'provider' => $provider,
		]);
	}

	public function actionView($discipline = '', $tab = 'about')
	{
		$discipline = (string)$discipline;
		$tab = (string)$tab;
		$model = Discipline::findByName($discipline);
		if (empty($model)) {
			return $this->redirect(['index']);
		}
		return $this->render('view', [
			'model' => $model
		]);
	}
}
