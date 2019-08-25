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

function transName($textcyr)
{
	$cyrArr = ['&rsquo', '  ', '"', '	', '     ', '   ', '
				', '	', '   ', '	', '  	', '
				'];
	$latArr = ['`', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '];
	$text = str_replace($cyrArr, $latArr, $textcyr);
    $text = str_replace("'", "", $text);
    return $text;
}

function checkQuantity($quantityText)
{
	$quantityText = trim($quantityText);
	$checkString = str_replace(',', '.', $quantityText);

	$numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
	$quantityNumber = '';
	$quantityUnit = '';
	for ($i = 0; $i < strlen($checkString); $i++){
	    if (in_array($checkString[$i], $numbers)) {
	    	$quantityNumber .= $checkString[$i];
	    }else{
	    	$quantityUnit .= $checkString[$i];
	    }
	}

	$qunatity = floatval(1);
	$quantity_unit = 'шт';

	if (is_numeric($quantityNumber)) {
		$qunatity = floatval($quantityNumber);
		$quantity_unit = trim($quantityUnit);
	}

    return [$qunatity, $quantity_unit];
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

/* Base URL */
$GLOBALS['baseURL'] = 'https://novus.ua/sales.html';

/* Products */
$GLOBALS['all_items'] = [];

for($i = 1; $i < 1000; $i++){

	$currentURL = $GLOBALS['baseURL'].'?p='.$i;
	$pageText = getPageContentAsString($currentURL);
	$page = str_get_html($pageText);
	$pageContent = $page->find('#layer-product-list')[0];
	$items = $pageContent->find('.product-item');

	if ($items != null) {
		foreach ($items as $item) {
			$itemObject = [];

			/* Product Image */
			$imgBlock = $item->find('.product-image-photo');
			if ($imgBlock != null) {
				$img = $imgBlock[0]->src;
				$img = trim($img);
			}else{
				$img = 'null';
			}
			$itemObject[':image_url'] = $img;

			/* Product Name */
			$nameBlock = $item->find('.product-item-name');
			if ($nameBlock != null) {
				$nameFullText = trim($nameBlock[0]->plaintext);
				$name = transName($nameFullText);

				$fullNameArray = explode(' ', $name);
				$quantityAndUnitArray = checkQuantity($fullNameArray[count($fullNameArray)-1]);
			}else{
				$name = 'null';
				$quantityAndUnitArray = [floatval(1),'шт'];
			}
			$itemObject[':name'] = $name;
			$itemObject[':quantity'] = $quantityAndUnitArray[0];
			$itemObject[':quantity_unit'] = $quantityAndUnitArray[1];
			

			/* Product Date End */
			$dateBlock = $item->find('.mb-time-countdown-container');
			if ($dateBlock != null) {
				$dateFullText = trim($dateBlock[0]->plaintext);
				$dateArray = explode('по', $dateFullText);
				$date_end = trim($dateArray[count($dateArray)-1]) . '.2019';
				$endDayDateFormat = strtotime($date_end);
				$endDay = date('Y-m-d H:i:s', $endDayDateFormat);
			}else{
				$endDay = 'null';
			}
			$itemObject[':date_end'] = $endDay;

			/* Product Parse URL */
			$itemObject[':parse_url'] = transliterate($name).'&date_end='.$endDay;

			/* Product Price Sell */
			$price_sell_block = $item->find('span[data-price-type="finalPrice"]');
			if ($price_sell_block != null) {
				$price_sell_text = '';
				$price_sell_integer = $price_sell_block[0]->find('.integer');
				if ($price_sell_integer != null) {
					$price_sell_int = $price_sell_integer[0]->plaintext;
				}else{
					$price_sell_int = 'null';
				}

				$price_sell_decimal = $price_sell_block[0]->find('.decimal');
				if ($price_sell_decimal != null) {
					$price_sell_float = $price_sell_decimal[0]->plaintext;
				}else{
					$price_sell_float = 'null';
				}

				if ($price_sell_int != 'null') {
					$price_sell_text .= $price_sell_int;
					if ($price_sell_float != 'null') {
						$price_sell_text .= str_replace(',', '.', $price_sell_float);
					}
					$price_sell = floatval($price_sell_text);
				}else{
					$price_sell = 'null';
				}
			}else{
				$price_sell = 'null';
			}
			$itemObject[':price_sell'] = $price_sell;

			/* Product Price */
			$price_block = $item->find('span[data-price-type="oldPrice"]');
			if ($price_block != null) {
				$price_text = '';
				$price_integer = $price_block[0]->find('.integer');
				if ($price_integer != null) {
					$price_int = $price_integer[0]->plaintext;
				}else{
					$price_int = 'null';
				}

				$price_decimal = $price_block[0]->find('.decimal');
				if ($price_decimal != null) {
					$price_float = $price_decimal[0]->plaintext;
				}else{
					$price_float = 'null';
				}

				if ($price_int != 'null') {
					$price_text .= $price_int;
					if ($price_float != 'null') {
						$price_text .= str_replace(',', '.', $price_float);
					}
					$price = floatval($price_text);
				}else{
					$price = 'null';
				}
			}else{
				$price = 'null';
			}
			$itemObject[':price'] = $price;

			/* Product Percent Diff */
			$percent_diff_block = $item->find('.percent-amount');
			if ($percent_diff_block != null) {
				$percent_diff_text = trim($percent_diff_block[0]->plaintext);
				$percent_diff_text = str_replace('-', '', $percent_diff_text);
				$percent_diff_text = str_replace('%', '', $percent_diff_text);
				$percent_diff = floatval($percent_diff_text);
			}else{
				$percent_diff = 'null';
			}
			$itemObject[':percent_diff'] = $percent_diff;

			array_push($GLOBALS['all_items'], $itemObject);
		}
	}

	if(count($items) < 12){
		$currentCount = 12 - count($items);
		$allItems = (12 * ($i-1)) + $currentCount; 
		// var_dump($GLOBALS['all_items']);
		echo 'You are amazing, you just have parsed '.$allItems.' products <br>'; die;
	}
}

// var_dump($GLOBALS['all_items']);