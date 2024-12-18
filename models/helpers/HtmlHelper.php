<?php

namespace app\models\helpers;

use Yii;
use yii\base\Model;
use yii\helpers\{ArrayHelper, HtmlPurifier, Url};
use yii\bootstrap5\Html;

use \Parsedown;


class HtmlHelper extends Model
{

	public static function getFooterText()
	{
		$begin_year = 2022;
		$year = ((date('Y') > $begin_year) ? ($begin_year . '-' . date('Y')) : $begin_year);
		$text = '&copy; ' . Html::a(Yii::$app->name, ['/'], ['class' => 'text-secondary']);
		$text .= self::circle();
		$text .= Html::tag('span', $year);
		return $text;
	}

	public static function getMonths()
	{
		return [
			1 => Yii::t('app', 'янв.'),
			2 => Yii::t('app', 'фев.'),
			3 => Yii::t('app', 'мар.'),
			4 => Yii::t('app', 'апр.'),
			5 => Yii::t('app', 'мая'),
			6 => Yii::t('app', 'июн.'),
			7 => Yii::t('app', 'июл.'),
			8 => Yii::t('app', 'авг.'),
			9 => Yii::t('app', 'сен.'),
			10 => Yii::t('app', 'окт.'),
			11 => Yii::t('app', 'ноя.'),
			12 => Yii::t('app', 'дек.'),
		];
	}

	public static function getTimeElapsed(string $datetime, bool $full = false)
	{
		$now = new \DateTime(null);
		$ago = new \DateTime($datetime);

		$diff = $now->diff($ago);
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		if ($full or $diff->d or $diff->w or $diff->m or $diff->y) {
			$months = self::getMonths();
			$date = $ago->format('d') . ' ';
			$m = (int)$ago->format('m');
			$date .= $months[$m];
			if ($ago->format('Y') != date('Y') or $full) {
				$date .= ' ' . $ago->format('Y');
			}
			if ($full) {
				$date .= ', в ' . $ago->format('H:i');
			}
			return $date;
		}

		$result = [];
		$words = array(
			'h' => 'ч',
			'i' => 'м',
		);
		foreach ($words as $k => $v) {
			if ($diff->{$k}) {
				$result[$k] = '';
				if (!$full) {
					$result[$k] = $diff->{$k} . ' ';
				}
				$result[$k] .= $v;
			}
		}

		$result = array_slice($result, 0, 1);
		if ($result) {
		   $ago_text = Html::tag('span', Yii::t('app', 'назад'), ['class' => ['ago-text']]);
			$result = implode(', ', $result) . $ago_text;
		} else {
			$result = Yii::t('app', 'только что');
		}
		return $result;
	}

	public static function isEmptyHtml(?string $html)
	{
		$html = html_entity_decode($html);
		$nbsp = html_entity_decode('&nbsp;');
		$html = str_replace($nbsp, '', $html);
		$nbsp = html_entity_decode('&nbsp');
		$html = str_replace($nbsp, '', $html);
		$html = HtmlPurifier::process($html);
		$html = strip_tags($html);
		$html = preg_replace('/\s+/', '', $html);
		return empty($html);
	}

	public static function isEmptyMarkdown(?string $markdown)
	{
		$parse = new Parsedown;
		$markdown = $parse->line($markdown);
		return static::isEmptyHtml($markdown);
	}

	public static function errorSummary($models, $options = [])
	{
		$options = (array)$options;
		$output = '';
		$has_errors = false;
		if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
        	if ($model->hasErrors()) {
        		$has_errors = true;
        	}
        }
        if ($has_errors) {
			$div_options = [
				'header' => Yii::t('app','При сохранении данных возникли ошибки.') . '<br>' .
						Yii::t('app','Попробуйте исправить их или обратитесь за помощью в поддержку.') . '<br>' .
						Yii::t('app','Список ошибок:')
			];
			if ($div_options) {
				$div_options = array_merge($div_options, $options);
			}
			$output = Html::errorSummary($models, $div_options);
        }
        return $output;
	}

	public static function getShareDiv(string $div_id, string $link, string $title): string
	{
		$output = '';

		$output .= Html::beginTag('div', [
			'class' => ['toast', 'w-100', 'fade', 'hide', 'position-absolute'],
			'style' => ['z-index' => 100, 'max-width' => '300px'],
			'data-share-id' => $div_id,
			'data-autohide' => 'true',
			'aria-live' => 'polite',
			'aria-atomic' => 'true',
			'data-bs-delay' => 500,
		]);
		$output .= Html::beginTag('div', ['class' => 'toast-header']);
		$output .= Html::tag('strong', $title, ['class' => 'me-auto']);
		$output .= Html::button(null, ['class' => 'btn-close', 'data-bs-dismiss' => 'toast', 'aria-label' => 'Close']);
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => 'toast-body']);
		$output .= Html::input('text', null, $link, ['class' => 'form-control']);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

	public static function circle(?string $class = null)
	{
		return Html::tag('span', null, ['class' => ['bi', 'bi-dot', 'bi_dot', $class]]);
	}

	public static function divider()
	{
		return Html::tag('li',
			Html::tag('hr', null, ['class' => ['dropdown-divider', 'my-1']])
		);
	}

	public static function icons()
	{
		$icon_url = '/favicon.ico?v=1.001';
		$icon_sizes = [16, 32, 48, 96, 144, 192, 256, 384, 512];
		$apple_icon_sizes = [57, 60, 72, 76, 114, 120, 144, 152, 167, 180];
		$output = '';
		$output .= Html::tag('link', null, ['href' => $icon_url, 'rel' => 'shortcut icon']);
		$output .= Html::tag('link', null, ['href' => $icon_url, 'type' => 'image/x-icon', 'rel' => 'icon']);
		foreach ($icon_sizes as $size) {
			$sizes = $size . 'x' . $size;
			$output .= Html::tag('link', null, ['href' => $icon_url, 'sizes' => $sizes, 'rel' => 'icon']);
		}
		foreach ($apple_icon_sizes as $size) {
			$sizes = $size . 'x' . $size;
			$output .= Html::tag('link', null, ['href' => $icon_url, 'sizes' => $sizes, 'rel' => 'apple-touch-icon']);
		}
		return $output;
	}

	public static function getCountHtml(?int $value = 0)
	{
		return Html::tag('span', $value, ['class' => ['badge', 'badge-pill', 'badge-light', 'ms-1']]);
	}

	public static function getIconText(?string $text = '', bool $show = false)
	{
		$class = ($show ? 'icon-text-show' : 'icon-text');
		return Html::tag('span', $text, ['class' => $class]);
	}

	public static function getCountText(?int $value = 0, bool $brackets = false)
	{
		$text = Html::tag('span', $value, ['class' => 'count']);
		if ($brackets) {
			$text = '(' . $text . ')';
		}
		return $text;
	}

	protected static function actionButtonBase(?string $title, string $action, string $data_button, ?int $id = null, ?array $params = null)
	{
		$params = (array)$params;
		$tag = ArrayHelper::remove($params, 'tag');
		if (!$tag) {
			$tag = 'button';
		}
		$params['data-button'] = $data_button;
		$params['data-action'] = $action;
		$params['data-id'] = $id;
		return Html::tag($tag, $title, $params);
	}

	public static function actionButton(?string $title, string $action, ?int $id = null, ?array $params = null)
	{
		$data_button = 'action';
		return static::actionButtonBase($title, $action, $data_button, $id, $params);
	}

	public static function actionModeratorButton(?string $title, string $action, ?int $id = null, ?array $params = null)
	{
		$data_button = 'action-moderator';
		return static::actionButtonBase($title, $action, $data_button, $id, $params);
	}

}
