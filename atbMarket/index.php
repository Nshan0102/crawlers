<?php
include_once('parser_configs.php');
include_once('simple_html_dom.php');

function transliterate($textcyr)
{
    $cyr = array(
        'ж', 'ч', 'щ', 'ы', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
        'Ж', 'Ч', 'Щ', 'Ы', 'Ш', 'Ю', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я', '&rsquo', '  ', '"', '	', '     ', '   ');
    $lat = array(
        'zh', 'ch', 'sht', 'y', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
        'Zh', 'Ch', 'Sht', 'Y', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q', '`', ' ', '', ' ', ' ', ' ');
    $text = str_replace($cyr, $lat, $textcyr);
    $text = str_replace("'", "", $text);
    return $text;
}

function getPageContentAsString($url){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_REFERER, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}

/* Default End Date */
$endDatePageText = getPageContentAsString('https://www.atbmarket.com/hot/akcii/');
$endDatePage = str_get_html($endDatePageText);
$endDateTag = $endDatePage->find('.date')[1];
$endDateTextArray = explode('по', $endDateTag->text());
$endDateText = $endDateTextArray[count($endDateTextArray)-1];
$endDayText = trim($endDateText);
$endDayDateFormat = strtotime($endDayText);
$GLOBALS['endDay'] = date('Y-m-d H:i:s', $endDayDateFormat);

/* Parsed Main Page */
$pageText = getPageContentAsString('https://www.atbmarket.com/hot/akcii/7day/');
$pageDOM = str_get_html($pageText);


/* Products */
$GLOBALS['all_items'] = [];


$productSectionBlock = $pageDOM->find('.list_wrapper');
$productSections = $productSectionBlock[0]->find('ul');

foreach ($productSections as $productSection) {
	if ($productSection != null) {
		$items = $productSection->find('li');
		if ($items != null) {
			foreach ($items as $item) {
				$itemObject = [];

				/* Product Image */
				$imgBlock = $item->find('.promo_image_wrap');
				if ($imgBlock != null) {
					$imgTag = $imgBlock[0]->find('img');
					if ($imgTag != null) {
						$img = 'https://www.atbmarket.com/'.$imgTag[0]->src;
					}else{
						$img = 'null';
					}
				}else{
					$img = 'null';
				}
				$itemObject[':image_url'] = $img;

				/* Product Name */
				$moreNameBlock = $item->find('.promo_info_text');
				if ($moreNameBlock != null) {
					$fullMoreName = $moreNameBlock[0]->text();
					$name = str_replace('
	                            ', ' ', $fullMoreName);
					$name = str_replace('                             ', ' ', $name);
					$name = trim($name);

					/* Product Quantity */

					$quantityAndUnitText = str_replace(',', '.', $fullMoreNameArray[count($fullMoreNameArray)-1]);
					$quantityAndUnitArray = explode(' ', $quantityAndUnitText);
					if (strpos( $quantityAndUnitArray[0], '/') != false) {

						$quantArray = explode('/', $quantityAndUnitArray[0]);
						$quantity = floatval(trim($quantArray[0]));
					}elseif (strpos($quantityAndUnitArray[0], '×') != false) {

						$quantArray = explode('×', $quantityAndUnitArray[0]);
						$quantityOne = floatval(trim($quantArray[0]));
						$quantityTwo = floatval(trim($quantArray[1]));
						$quantity = floatval($quantityOne * $quantityTwo);
					}else{
						if (is_numeric($quantityAndUnitArray[0])) {
							$quantity = floatval($quantityAndUnitArray[0]);
						}else{
							$quantity = floatval(1);
							$quantity_unit = 'шт';
						}
					}
					
					/* Product Quantity Unit */
					if (!isset($quantity_unit)) {
						$quantity_unit = trim($quantityAndUnitArray[1]);
					}
					
				}else{
					$name = 'null';
					$quantity = 'null';
					$quantity_unit = 'null';
				}

				$itemObject[':name'] = $name;
				$itemObject[':quantity'] = $quantity;
				$itemObject[':quantity_unit'] = $quantity_unit;

				/* Product Parse URL */
				$parse_url = transliterate($name).'&date='.$GLOBALS['endDay'];
				$parse_url = str_replace(',', '_', $parse_url);
				$itemObject[':parse_url'] = str_replace(' ', '_', $parse_url);

				/* Product Date End */
				
				$itemObject[':date_end'] = $GLOBALS['endDay'];

				/* Product Price Sell */
				$price_sell_block = $item->find('.promo_price');
				if ($price_sell_block != null) {
					$price_sell_full_text = $price_sell_block[0]->text();
					$price_sell_full_text = str_replace('                                  ', '', $price_sell_full_text);

					$floatPart = $price_sell_block[0]->find('span');
					if ($floatPart != null) {
						$floatPartValueText = $floatPart[0]->text();
						$floatPartValue = trim($floatPartValueText);
						$price_sell_array = explode($floatPartValue, $price_sell_full_text);
						$price_sell_text = trim($price_sell_array[0]).'.'.$floatPartValue;
						$price_sell = floatval(trim($price_sell_text));
					}else{
						$priceUnitBlock = $price_sell_block[0]->find('.currency');
						if ($priceUnitBlock != null) {
							$priceUnitValueText = $priceUnitBlock[0]->text();
							$priceUnitValue = trim($priceUnitValueText);
							$price_sell_array = explode($priceUnitValue, $price_sell_full_text);
							$price_sell_text = trim($price_sell_array[0]);
							$price_sell = floatval(trim($price_sell_text));
						}else{
							$price_sell = floatval(trim($price_sell_full_text));
						}
					}
				}else{
					$price_sell = 'null';
				}

				$itemObject[':price_sell'] = $price_sell;

				/* Product Price */
				$price_block = $item->find('.promo_old_price');
				if ($price_block != null) {
					$price_full_text = $price_block[0]->text();
					$price_float = trim(str_replace(',', '.', $price_full_text));
					$price = floatval(trim($price_float));
				}else{
					$price = 'null';
				}
				$itemObject[':price'] = $price;

				/* Product Percent Diff */
				$percent_diff_block = $item->find('.economy_price');
				if ($percent_diff_block != null) {
					$percent_diff_text_block = $percent_diff_block[0]->find('span');
					if ($percent_diff_text_block != null) {
						$percent_diff_text = $percent_diff_text_block[0]->text();
						$percent_diff_text = str_replace('-', '', $percent_diff_text);
						$percent_diff_text = str_replace('%', '', $percent_diff_text);
						$percent_diff_text = str_replace(' ', '', $percent_diff_text);
						$percent_diff = floatval(trim($percent_diff_text));
					}else{
						$percent_diff = 'null';
					}
				}else{
					$percent_diff = 'null';
				}
				$itemObject[':percent_diff'] = $percent_diff;

				array_push($GLOBALS['all_items'], $itemObject);
			}
		}
	}
}

var_dump($GLOBALS['all_items']);