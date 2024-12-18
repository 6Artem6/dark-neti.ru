<?php

namespace app\components;

use Yii;
use yii\helpers\VarDumper;
use yii\log\Target;


class DbTarget extends Target
{

	public $logModelClass = \app\models\log\ErrorLogs::class;

	public function init()
	{
		parent::init();
	}

	public function export()
	{
		$logModel = Yii::createObject([
			'class' => $this->logModelClass
		]);
		foreach ($this->messages as $message) {
			list($text, $level, $category, $timestamp) = $message;
			if (!is_string($text)) {
				if ($text instanceof \Exception || $text instanceof \Throwable) {
					$text = (string) $text;
				} else {
					$text = VarDumper::export($text);
				}
			}
			$logModel->level = $level;
			$logModel->category = $category;
			$logModel->prefix = $this->getMessagePrefix($message);
			$logModel->message = $text;
			$logModel->create();
		}
	}
}
