<?php

namespace app\controllers;

use Yii;
use yii\helpers\FileHelper;

use app\models\file\{File, FileHistory};
use app\models\badge\Badge;
use app\models\user\UserData;


class FileController extends BaseController
{

	public function actionIndex()
	{
		return $this->redirect(['/']);
	}

	public function actionDocument($id = 0)
	{
		$id = (int)$id;
		$model = File::findById($id);
		if (empty($model)) {
			$model = FileHistory::findById($id);
			if (empty($model)) {
				return null;
			}
		}
		if ($message = $model->canSee()) {
			return $message;
		}
		// if (!$model->isFileExists) {
		// 	return Yii::t('app', 'Файл не найден');
		// }
		$file_name = $model->file_user_name;
		$file_path = $model->getLinkFromBucket();
		Yii::$app->response->headers->set('Content-Disposition', 'filename="' . $file_name . '"');
		if (!$model->isImg) {
			return Yii::$app->response->sendFile(file_get_contents($file_path), $file_name);
		}
		Yii::$app->response->headers->set('Content-Type', FileHelper::getMimeTypeByExtension($file_name));
		return Yii::$app->response->sendContentAsFile(file_get_contents($file_path), $file_name, ['inline' => true]);
	}

	public function actionAvatar($id = 0)
	{
		$id = (int)$id;
		$model = UserData::findOne($id);
		if (empty($model)) {
			return null;
		}
		$file_name = $model->avatar;
		$file_path = $model->getAvatarPath();
		Yii::$app->response->headers->set('Content-Type', FileHelper::getMimeType($file_path));
		Yii::$app->response->headers->set('Content-Disposition', 'filename="' . $file_name . '"');
		return Yii::$app->response->sendFile($file_path, $file_name, ['inline' => true]);
	}

	public function actionBadge($id = 0)
	{
		$id = (int)$id;
		$model = Badge::findOne($id);
		if (empty($model)) {
			return null;
		}
		$file_name = $model->latin_name . '.svg';
		$file_path = $model->getBadgePath();
		Yii::$app->response->headers->set('Content-Type', FileHelper::getMimeType($file_path));
		Yii::$app->response->headers->set('Content-Disposition', 'filename="' . $file_name . '"');
		return Yii::$app->response->sendFile($file_path, $file_name, ['inline' => true]);
	}

}
