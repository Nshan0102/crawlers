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

function transName($textcyr){
	$cyrArr = ['&rsquo', '  ', '"', '	', '     ', '   ', '
				', '	', '   ', '	', '  	', '
				'];
	$latArr = ['`', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '];
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

/* Parsed Main Page */
$GLOBALS['baseURL'] = 'https://brusnichka.com.ua/ua/pokupatelyam/aktsii';
$pageText = getPageContentAsString($baseURL);
$page = str_get_html($pageText);
$pageContent = $page->find('.content')[0];

/* Default End Date */
$endDateTag = $pageContent->find('b')[0];
$endDateFullTextArray = explode('-', $endDateTag->text());
$endDateFullText = $endDateFullTextArray[1];
$endDateTextArray = explode(',', $endDateFullText);
$endDateText = $endDateTextArray[0];
$formatting = explode('.', $endDateText);
$formatted = $formatting[0].'.'.$formatting[1].'.20'.$formatting[2];
$endDayText = trim($formatted);
$endDayDateFormat = strtotime($endDayText);
$GLOBALS['endDay'] = date('Y-m-d H:i:s', $endDayDateFormat);

/* Products */
$GLOBALS['all_items'] = [];

$items = $pageContent->find('.weekly-promo-item');
if ($items != null) {
	foreach ($items as $item) {
		$itemObject = [];

		/* Product Image */
		$imgBlock = $item->find('.img');
		if ($imgBlock != null) {
			$imgUrl = $imgBlock[0]->style;
			$imgUrlBigArray = explode("url('", $imgUrl);
			$imgUrlArray = explode("')", $imgUrlBigArray[1]);
			$img = 'https://brusnichka.com.ua'.$imgUrlArray[0];
		}else{
			$img = 'null';
		}
		$itemObject[':image_url'] = $img;

		/* Product Name */
		$nameBlock = $item->find('.desc');
		if ($nameBlock != null) {
			$nameFullText = str_replace('<br/>', ' ', $nameBlock[0]->outertext);
			$nemeObject = str_get_html($nameFullText);
			$nameFullText = str_replace('&nbsp;', ' ', $nemeObject->plaintext);
			$fullNameArray = explode(' ', $nameFullText);
			if (is_numeric(str_replace(',', '.', $fullNameArray[count($fullNameArray)-2]))) {
				$newName = '';
				for ($i = 0; $i < count($fullNameArray) - 2; $i++) {
					$newName .= $fullNameArray[$i].' ';
				}
				/* Product Quantity */
				$quantity = (float)str_replace(',', '.', $fullNameArray[count($fullNameArray)-2]);
				/* Product Quantity Unit */
				$quantity_unit = $fullNameArray[count($fullNameArray)-1];
			}else{
				$newName = '';
				for ($i = 0; $i < count($fullNameArray) - 1; $i++) {
					$newName .= $fullNameArray[$i].' ';
				}

				$lastWordArr = explode('/', str_replace(',', '.', $fullNameArray[count($fullNameArray)-1]));
				$lastWord = str_replace('л', '', $lastWordArr[0]);
				$lastWord = str_replace(' ', '', $lastWord);
				$lastWord = str_replace('ст', '', $lastWord);
				$lastWord = str_replace('гр', '', $lastWord);
				$prevWordArr = explode('/', str_replace(',', '.', $fullNameArray[count($fullNameArray)-2]));
				$prevWord = str_replace('л', '', $prevWordArr[0]);
				$prevWord = str_replace(' ', '', $prevWord);
				$prevWord = str_replace('ст', '', $prevWord);
				$prevWord = str_replace('гр', '', $prevWord);
				if (is_numeric($lastWord)) {
					/* Product Quantity */
					$quantity = (float)$lastWord;
					/* Product Quantity Unit */
					$quantity_unit = str_replace($lastWord, '', $lastWordArr[0]);
				}elseif(is_numeric($prevWord)){
					/* Product Quantity */
					$quantity = (float)$prevWord;
					/* Product Quantity Unit */
					$quantity_unit = str_replace($prevWord, '', $prevWordArr[0]);
				}else{
					/* Product Quantity */
					$quantity = 1;
					/* Product Quantity Unit */
					$quantity_unit = $fullNameArray[count($fullNameArray)-1];
				}
			}
			$name = trim($newName);
			$itemObject[':name'] = transName($name);
			$itemObject[':quantity'] = $quantity;
			$itemObject[':quantity_unit'] = $quantity_unit;
		}

		/* Product Parse URL */
		$itemObject[':parse_url'] = transliterate($name).'&date_end='.$GLOBALS['endDay'];

		/* Product Date End */
		$itemObject[':date_end'] = $GLOBALS['endDay'];

		/* Product Price Sell */
		$price_sell_block = $item->find('.price__new');
		if ($price_sell_block != null) {
			$price_sell_full_text = $price_sell_block[0]->innertext;
			$price_sell_array = explode('<sup', $price_sell_full_text);
			$price_sell_int = trim($price_sell_array[0]);
			$price_sell_float = trim(str_replace('</sup>', '', $price_sell_array[1]));
			$price_sell_float = trim(str_replace('class="coins">', '', $price_sell_float));
			$price_sell_float = trim(str_replace(' ', '', $price_sell_float));
			$price_sell_text = $price_sell_int.'.'.$price_sell_float;
			$price_sell = (float)$price_sell_text;
		}else{
			$price_sell = 'null';
		}
		$itemObject[':price_sell'] = $price_sell;

		/* Product Price */
		$price_block = $item->find('.price__old');
		if ($price_block != null) {
			$price_full_text = $price_block[0]->innertext;
			$price_array = explode('<sup', $price_full_text);
			$price_int = trim($price_array[0]);
			$price_float = trim(str_replace('</sup>', '', $price_array[1]));
			$price_float = trim(str_replace('class="coins">', '', $price_float));
			$price_float = trim(str_replace(' ', '', $price_float));
			$price_text = $price_int.'.'.$price_float;
			$price = (float)$price_text;
		}else{
			$price = 'null';
		}
		$itemObject[':price'] = $price;

		/* Product Percent Diff */
		$percent_diff_block = $item->find('.price__discount');
		if ($percent_diff_block != null) {
			$percent_diff = trim($percent_diff_block[0]->plaintext);
		}else{
			$percent_diff = 'null';
		}
		$itemObject[':percent_diff'] = $percent_diff;

		array_push($GLOBALS['all_items'], $itemObject);
	}
}

var_dump($GLOBALS['all_items']);