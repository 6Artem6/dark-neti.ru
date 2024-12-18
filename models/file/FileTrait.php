<?php

namespace app\models\file;

use Yii;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use Aws\S3\S3Client;


trait FileTrait
{

	public function getLocalFilePath()
	{
		return Yii::getAlias("@app/repository/user_files/" . $this->file_system_name);
	}

	public function getBucketFolderPath()
	{
		$folder = ($this->isForAnswer ? 'answers' : 'questions');
		$folder .= '/' . $this->file_folder_name;
		$folder .= '/' . $this->bucket_link_version;
		return $folder;
	}

	public function getBucketFiletPath()
	{
		return $this->getBucketFolderPath() . '/' .  $this->file_system_name;
	}

	public static function findById(int $file_id)
	{
		return self::find()->where(['file_id' => $file_id])->one();
	}

	public function filterName($value)
	{
		return str_replace(['<', '>', ':', '\'', '"', '/', '\\', '|', '?', '*'], '', $value);
	}

	public function getIsImg()
	{
		return in_array($this->ext, ['jpg', 'jpeg', 'png']);
	}

	public function getIsForAnswer()
	{
		return !empty($this->answer_id);
	}

	public function getIsFileExists()
	{
		return file_exists($this->getLocalFilePath());
	}

	public function canSee(): ?string
	{
		$message = null;
		if (!Yii::$app->user->isGuest and !Yii::$app->user->identity->isModerator()) {
			if ($this->isForAnswer) {
				if ($this->answer->is_hidden and !$this->answer->isAuthor) {
					$message = Yii::t('app', 'Ответ временно скрыт!');
				}
			} else {
				if ($this->question->is_hidden and !$this->question->isAuthor) {
					$message = Yii::t('app', 'Вопрос временно скрыт!');
				}
			}
		}
		return $message;
	}

	public function getDocUrl()
	{
		if ($this->isImg) {
			$url = Url::to(["/file/document/" . $this->file_id]);
		} else {
			$url = Url::to(["/file/document/" . $this->file_id]);
		}
		return $url;
	}

	public function getDocIcon()
	{
		return match ($this->ext) {
			'pdf' => 'bi-file-earmark-pdf',
			'doc' => 'bi-file-earmark-word',
			'docx' => 'bi-file-earmark-word',
			'xls' => 'bi-file-earmark-excel',
			'xlsx' => 'bi-file-earmark-excel',
			'ppt' => 'bi-file-earmark-ppt',
			'pptx' => 'bi-file-earmark-ppt',
			'txt' => 'bi-filetype-txt',
			'zip' => 'bi-file-earmark-zip',
			default => 'bi-files'
		};
	}

	public function getFullSize()
	{
		$size = "";
		if ($this->size < 1024) {
			$size = $this->size . " B";
		} elseif ($this->size < 1024 * 1024) {
			$size = floor($this->size / 1024) . " KB";
		} else {
			$size = floor($this->size / (1024 * 1024)) . " MB";
		}
		return $size;
	}

	public function getDocHtml()
	{
		$html = "";
		if (is_null($this->canSee())) {
			// $url = $this->getDocUrl();
			$url = $this->getLinkFromBucket();
			if ($this->isImg) {
				$html = Html::img($url, [
					'class' => ['img-thumbnail', 'img-file'],
				]);
			} else {
				$icon = $this->getDocIcon();
				$filename = $this->file_user_name . " (" . $this->fullSize . ")";
				$html = Html::a($filename, $url, [
					'target' => '_blank',
					'class' => ['text-secondary', 'link-primary', 'text-break', 'bi', 'bi_icon', 'text_doc', $icon]
				]);
			}
		}
		return $html;
	}

	public function removeFromLocal()
	{
		if ($this->isFileExists) {
			unlink($this->getLocalFilePath());
		}
	}

	public function getClient()
	{
		$params = Yii::$app->params['s3'];
		$key = $params['key'];
		$secret = $params['secret'];
		$endpoint = $params['endpoint'];
		$version = $params['version'];
		$region = $params['region'];
		return new S3Client([
			'endpoint'	=> $endpoint,
			'version'	=> $version,
			'region'	=> $region,
			'credentials' => [
				'key'	=> $key,
				'secret' => $secret,
			]
		]);
	}

	public function putToBucket()
	{
		$bucket = Yii::$app->params['s3']['bucket'];
		$s3 = $this->getClient();
		$result = $s3->putObject([
			'Bucket' => $bucket,
			'Key' => $this->getBucketFiletPath(),
			'SourceFile' => $this->getLocalFilePath(),
			'ACL' => 'private'
		]);
	}

	public function deleteFromBucket()
	{
		$bucket = Yii::$app->params['s3']['bucket'];
		$s3 = $this->getClient();
		$result = $s3->deleteObject([
			'Bucket' => $bucket,
			'Key'	=> $this->getBucketFiletPath(),
		]);
	}

	public function isExpired()
	{
		return (!$this->expires or (date('YmdHis') > date('YmdHis', strtotime($this->expires))));
	}

	public function replaceBucketLink()
	{
		$bucket = Yii::$app->params['s3']['bucket'];
		$s3 = $this->getClient();
		$get_result = $s3->getObject([
			'Bucket' => $bucket,
			'Key'	=> $this->getBucketFiletPath(),
		]);

		$this->deleteFromBucket();

		$this->bucket_link_version++;

		$result = $s3->putObject([
			'Bucket' => $bucket,
			'Key'	=> $this->getBucketFiletPath(),
			'Body'	=> $get_result['Body']->getContents(),
			'ACL'	=> 'private'
		]);

		$this->updateBucketLink();
	}

	public function updateBucketLink()
	{
		$bucket = Yii::$app->params['s3']['bucket'];
		$s3 = $this->getClient();
		$cmd = $s3->getCommand('GetObject', [
			'Bucket' => $bucket,
			'Key' => $this->getBucketFiletPath(),
    		'ResponseContentDisposition' => 'attachment; filename="' . $this->file_user_name . '"'
		]);

		$time = '+7 days';
		$this->expires = date('Y-m-d H:i:s', strtotime($time));

		$request = $s3->createPresignedRequest($cmd, $time);
		$bucket_link = (string)$request->getUri();

		$this->bucket_link = $bucket_link;
		$this->save();
	}

	public function getLinkFromBucket()
	{
		if ($this->isExpired()) {
			$this->updateBucketLink();
		}
		return $this->bucket_link;
	}
}
