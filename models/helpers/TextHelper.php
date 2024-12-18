<?php

namespace app\models\helpers;

use Yii;
use yii\base\Model;
use yii\helpers\{ArrayHelper, HtmlPurifier, Url};
use yii\bootstrap5\Html;

use \Parsedown;


class TextHelper extends Model
{

	public static function hasAt(string $text)
	{
		return (mb_substr($text, 0, 1) == '@');
	}

	public static function remove_multiple_whitespaces(?string $string, bool $trim = true)
	{
		$string = preg_replace('/\s+/', ' ', $string);
		if ($trim) {
			$string = trim($string);
		}
		return $string;
	}

	public static function remove_non_alphanumeric(?string $string)
	{
		return preg_replace('/[^\Dа-яa-z\s]/i', '', $string);
	}

	public static function remove_word_end(?string $string)
	{
		if (mb_strlen($string) < 4) {
			return $string;
		}
		$string = mb_strtolower($string);
		$list = [
			'а', 'я', 'и', 'ы', 'е', 'у', 'ю', 'о', 'ь',
			'ой', 'ей', 'ам', 'ям', 'ами', 'ями', 'ах', 'ях', 'ом', 'ем', 'ов',
			'ий', 'ый', 'ая', 'яя', 'ия', 'ий', 'ое', 'ее', 'ие', 'ые', 'их', 'ых',
			'им', 'ым', 'ую', 'юю', 'ого', 'его', 'ому', 'ему', 'ими', 'ыми',
			'ет', 'ут', 'ют', 'ит', 'ат', 'ят', 'ешь', 'ете', 'ишь', 'ите',
			'ить', 'ать', 'ять',
		];
		$match = implode('|', $list);
		return preg_replace("/({$match})$/i", '', $string);
	}

	public static function remove_emoji(?string $string)
	{
		$regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
		$clear_string = preg_replace($regex_alphanumeric, '', $string);

		$regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
		$clear_string = preg_replace($regex_symbols, '', $clear_string);

		$regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
		$clear_string = preg_replace($regex_emoticons, '', $clear_string);

		$regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
		$clear_string = preg_replace($regex_transport, '', $clear_string);

		$regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
		$clear_string = preg_replace($regex_supplemental, '', $clear_string);

		$regex_misc = '/[\x{2600}-\x{26FF}]/u';
		$clear_string = preg_replace($regex_misc, '', $clear_string);

		$regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
		$clear_string = preg_replace($regex_dingbats, '', $clear_string);

		return $clear_string;
	}

	public static function checkName(?string $name)
	{
		$name = preg_replace('/[^А-ЯЁа-яё\.\s]/', '', $name);
		$name = preg_replace('/\s+/', ' ', $name);
		$name = trim($name);
		return $name;
	}

	public static function get_string_between(?string $string, string $start, string $end)
	{
		$result = '';
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini) {
			$ini += strlen($start);
			$len = strpos($string, $end, $ini) - $ini;
			$result = substr($string, $ini, $len);
		}
		return $result;
	}

	public static function getIdFromUrl(string $url, ?array $pass_parts = null)
	{
		$id = null;
		$url_parts = parse_url($url);
		if (!empty($url_parts['host']) and ($url_parts['host'] != Yii::$app->request->serverName)) {
			return null;
		}
		if (empty($url_parts['path'])) {
			return null;
		}
		$path = trim($url_parts['path'], '/');
		$path_parts = explode('/', $path);
		$i = null;
		if ($pass_parts and (count($path_parts) >= count($pass_parts))) {
			$i = 0;
			foreach ($pass_parts as $k => $part) {
				if ($part != $path_parts[$k]) {
					return null;
				}
				$i++;
			}
		}
		if (!empty($path_parts[$i])) {
			$id = (int)$path_parts[$i];
		}
		return $id;
	}

}
