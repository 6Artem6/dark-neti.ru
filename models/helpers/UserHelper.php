<?php

namespace app\models\helpers;

use Yii;
use yii\base\Model;
use yii\helpers\IpHelper;
use yii\bootstrap5\Html;
use Jdenticon\Identicon;


class UserHelper extends Model
{

	public const SIZE_SM = 'sm';
	public const SIZE_MD = 'md';
	public const SIZE_LG = 'lg';


	public static function isDarkTheme()
	{
		$user = Yii::$app->user;
		$dark = true;
		if (!$user->isGuest and isset($user->identity->data)) {
			$dark = !$user->identity->data->theme;
		}
		return $dark;
	}

	public static function getTheme()
	{
		return (static::isDarkTheme() ? 'dark' : 'light');
	}

	public static function getYear()
	{
		if (date('m') >= 8) {
			$year = date('Y');
		} elseif (date('m') == 1) {
			$year = date('Y') - 1;
		} elseif ((date('m') >= 2) and (date('m') < 8)) {
			$year = date('Y') - 1;
		}
		return $year;
	}

	public static function getSemestr()
	{
		if ((date('m') >= 8) or date('m') == 1) {
			$semestr = 1;
		} elseif ((date('m') >= 2) and (date('m') < 8)) {
			$semestr = 2;
		}
		return $semestr;
	}

	public static function getWeek()
	{
		$today = date('Y-m-d');
		$year = static::getYear();
		if ((date('m') >= 7) and date('m') < 9) {
			$week = 0;
		} else {
			if ((date('m') >= 9) or date('m') == 1) {
				$date = $year."-09-01";
			} elseif ((date('m') >= 2) and (date('m') < 7)) {
				$year++;
				$date = $year."-02-01";
			}
			$now = new \DateTime($today);
			$begin = new \DateTime($date);
			if ($begin->format('w') == 7) {
				$d = 1;
				$interval = new \DateInterval("P".$d."D");
				$begin->add($interval);
			} else {
				$d = $begin->format('w') - 1;
				$interval = new \DateInterval("P".$d."D");
				$begin->sub($interval);
			}
			$diff = $now->diff($begin);
			$week = floor($diff->days / 7) + 1;
		}

		return $week;
	}

	public static function getIsOnlineText(bool $full = true)
	{
		$text = [];
		if ($full) {
			$text[] = Yii::t('app', 'Сейчас');
		}
		$text[] = Yii::t('app', 'Online');
		$text = implode(' ', $text);
		if ($full) {
			$text = Html::tag('span', null, ['class' => ['bi-circle-fill', 'text-success', 'me-1']]) . $text;
		}
		return $text;
	}

	public static function getWasOnlineText(string $time, bool $full = true)
	{
		$text = [];
		if ($full) {
			$text[] = Yii::t('app', 'Был в сети:');
		}
		$text[] = HtmlHelper::getTimeElapsed($time);
		$text = implode(' ', $text);
		if ($full) {
			$text = Html::tag('span', null, ['class' => ['bi', 'bi-clock', 'bi_icon']]) . $text;
		}
		return $text;
	}

	public static function getAvatar(string $type = self::SIZE_MD, bool $is_online = false, bool $show_info = true,
									 string $img_link = '', string $page_link = '', string $username = '')
	{
		$output = match ($type) {
			static::SIZE_SM => self::getAvatarSm($is_online, $img_link, $page_link),
			static::SIZE_MD => self::getAvatarMd($is_online, $img_link, $page_link),
			static::SIZE_LG => self::getAvatarLg($is_online, $img_link, $page_link),
			default => self::getAvatarMd($is_online, $img_link, $page_link),
		};
		if ($show_info) {
			$output .= Html::tag('div', null, [
				'class' => ['position-relative', 'info-div'],
				'style' => ['display' => 'none'],
			]);
		}
		return Html::tag('div', $output, [
			'class' => ['avatar-div'],
			'data-username' => ($show_info ? $username : null)
		]);
	}

	public static function getAvatarSm(bool $is_online = false, string $img_link = '', string $page_link = '')
	{
		$text = Html::img($img_link, ['class' => ['avatar-img']]);
		if ($is_online) {
			$text .= self::getOnlineCircle(static::SIZE_SM);
		}
		$avatar = Html::a($text, $page_link);
		return Html::tag('div', $avatar, ['class' => 'position-relative']);
	}

	public static function getAvatarMd(bool $is_online = false, string $img_link = '', string $page_link = '')
	{
		$text = Html::img($img_link, ['class' => ['avatar-img']]);
		if ($is_online) {
			$text .= self::getOnlineCircle(static::SIZE_MD);
		}
		$avatar = Html::a($text, $page_link);
		return Html::tag('div', $avatar, ['class' => 'position-relative']);
	}

	public static function getAvatarLg(bool $is_online = false, string $img_link = '', string $page_link = '')
	{
		$text = Html::img($img_link, ['class' => ['avatar-img-lg']]);
		if ($is_online) {
			$text .= self::getOnlineCircle(static::SIZE_LG);
		}
		$avatar = Html::a($text, $page_link);
		return Html::tag('div', $avatar, ['class' => ['avatar-img-lg-a']]);
	}

	public static function getOnlineCircle(string $type = self::SIZE_MD)
	{
		$class = match ($type) {
			static::SIZE_SM => 'online-circle-sm',
			static::SIZE_MD => 'online-circle-md',
			static::SIZE_LG => 'online-circle-lg',
			default => 'online-circle-md',
		};
		return Html::tag('span', null, [
			'class' => $class,
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Пользователь Online')
		]);
	}

	public static function getAvatarUrl(string $name, string $format, int $size)
	{
		$helper = new RequestHelper;
		$helper->baseUrl = Yii::$app->params['identiconUrl'];
		$helper->url = '/' . $name . '/' . $size;
		$helper->method = 'GET';
		$helper->data = [
			'format' => $format
		];
		return $helper->getContent();
	}

	public static function getAvatarImagick(string $name, string $format, int $size)
	{
		$icon = new Identicon;
		$icon->setValue($name);
		$icon->setSize($size);
		return $icon->getImageData();
	}

	public static function isLocal()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$range = Yii::$app->params['allowedIPs'];
		return in_array($ip, $range);
	}

	public static function isBot()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$range = Yii::$app->params['telegramIPs']['range'];
		return IpHelper::inRange($ip, $range);
	}

	public static function canSeeAll()
	{
		$user = Yii::$app->user;
		$can = false;
		if ($user->isGuest) {
			$can = static::isBot();
		} else {
			$can = $user->identity->isModerator();
		}
		return $can;
	}

}
