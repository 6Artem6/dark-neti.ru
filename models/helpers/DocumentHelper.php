<?php

namespace app\models\helpers;

use Yii;
use yii\base\Model;
use yii\helpers\{ArrayHelper, FileHelper};

use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\Element\{
	AbstractElement, Text, TextRun, Table, Image
};
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use PhpOffice\PhpPresentation\Shape\{Group, RichText};


class DocumentHelper extends Model
{

	public static function getFileText(string $path, bool $truncate = true, bool $remove_ws = true)
	{
		$magicFile = FileHelper::$mimeMagicFile;
		$magicFile = Yii::getAlias($magicFile);
		$mimeTypes = require $magicFile;

		$text = '';
		if (file_exists($path)) {
			$mime_type = FileHelper::getMimeType($path);
			if ($mime_type) {
				$doc_type = explode('/', $mime_type)[0];
				try {
					if ($doc_type == 'text') {
						$text = static::getPlainText($path);
					} elseif ($mime_type == $mimeTypes['pdf']) {
						$text = static::getPdfText($path);
					} elseif (($mime_type == $mimeTypes['doc']) or ($mime_type == $mimeTypes['docx'])) {
						$text = static::getWordText($path);
					} elseif (($mime_type == $mimeTypes['xls']) or ($mime_type == $mimeTypes['xlsx'])) {
						$text = static::getExcelText($path);
					} elseif (($mime_type == $mimeTypes['ppt']) or ($mime_type == $mimeTypes['pptx'])) {
						$text = static::getPresentationText($path);
					}
				} catch (\Exception $e) {
				}
			}
		}
		$text = TextHelper::remove_emoji($text);
		if ($remove_ws) {
			$text = TextHelper::remove_multiple_whitespaces($text);
		}
		if ($truncate) {
			$max = pow(2, 16) - 1;
			$text = mb_substr($text, 0, $max);
		}
		return $text;
	}

	protected static function getPlainText(string $path)
	{
		$text = null;
		if (file_exists($path)) {
			$text = file_get_contents($path);
		}
		return $text;
	}

	protected static function getPdfText(string $path)
	{
		$text = null;
		if (file_exists($path)) {
			$parser = new Parser;
			$pdf = $parser->parseFile($path);
			$text = $pdf->getText();
		}
		return $text;
	}

	protected static function getWordText(string $path)
	{
		$text = [];
		if (file_exists($path)) {
			$phpWord = WordIOFactory::load($path);
			$els = $phpWord->getSections()[0]->getElements();
			foreach ($els as $el) {
				$text[] = static::getWordSubElementText($el);
			}
		}
		return implode("\n", $text);
	}

	protected static function getWordSubElementText(AbstractElement $el)
	{
		$text = [];
		if ($el instanceof Text) {
			$text[] = $el->getText();
		} elseif ($el instanceof TextRun) {
			foreach ($el->getElements() as $tr_e) {
				$text[] = static::getWordSubElementText($tr_e);
			}
		} elseif ($el instanceof Table) {
			$rows = $el->getRows();
			foreach ($rows as $row) {
				$cells = $row->getCells();
				foreach ($cells as $cell) {
					foreach ($cell->getElements() as $c_e) {
						$text[] = static::getWordSubElementText($c_e);
					}
				}
			}
		} elseif ($el instanceof Image) {
		}
		return implode("\n", $text);
	}

	protected static function getExcelText(string $path)
	{
		$text = [];
		if (file_exists($path)) {
			$spreadsheet = SpreadsheetIOFactory::load($path);
			foreach ($spreadsheet->getAllSheets() as $sheet) {
				$cells = $sheet->getCellCollection()->getCoordinates();
				foreach ($cells as $cell) {
					$text[] = $sheet->getCell($cell)->getCalculatedValue();
				}
			}
		}
		return implode("\n", $text);
	}

	protected static function getPresentationText(string $path)
	{
		$text = [];
		if (file_exists($path)) {
			$presentation = PresentationIOFactory::load($path);
			$slides = $presentation->getAllSlides();
			foreach ($slides as $slide) {
				$note = $slide->getNote();
				if ($note->getShapeCollection()->count() > 0) {
					foreach ($note->getShapeCollection() as $shape) {
						$text[] = $shape->getPlainText();
					}
				}
				if ($slide->getShapeCollection()->count() > 0) {
					foreach ($slide->getShapeCollection() as $shape) {
						if ($shape instanceof Group) {
							foreach ($shape->getShapeCollection() as $shapeChild) {
								if ($shapeChild instanceof RichText) {
									foreach ($shapeChild->getParagraphs() as $paragraph) {
										$text .= $paragraph->getPlainText();
									}
								}
							}
						} elseif ($shape instanceof RichText) {
							foreach ($shape->getParagraphs() as $paragraph) {
								$text[] = $paragraph->getPlainText();
							}
						} else {
							$text[] = $shape->getPlainText();
						}
					}
				}
			}
		}
		return implode("\n", $text);
	}

}
