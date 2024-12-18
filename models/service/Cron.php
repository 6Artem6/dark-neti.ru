<?php

namespace app\models\service;

use Yii;
use yii\base\Model;

use app\models\register\CronRegisterMail;


class Cron extends Model
{

	protected $params;

	public function init()
	{
		parent::init();
		$this->params = Yii::$app->params['cron'];
	}

	public function checkSecret($secret = null)
	{
		return ($secret == $this->getSecret());
	}

	protected function getSecret()
	{
		return sha1(sha1($this->params['SECRET_KEY']));
	}

	public function checkRegisterMail()
	{
		$list = CronRegisterMail::getCurrentList();
		foreach ($list as $record) {
			$record->register->sendMailStudentMail();
			$record->delete();
		}
		return true;
	}

}
