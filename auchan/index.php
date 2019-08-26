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

function getPageContentAsString($url)
{
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

function selectClearPrice($price)
{
	$numbers = ['0','1','2','3','4','5','6','7','8','9',','];
	$clearText = '';
	for ($i = 0; $i < strlen($price); $i++){
	    if(in_array($price[$i], $numbers)){
	    	$clearText .= $price[$i];
	    }
	}
	$result = null;
	if ($clearText != '') {
		$clearText = str_replace(',', '.', $clearText);
		$result = floatval($clearText);
	}
	return $result;
}

/* Products */
$GLOBALS['all_items'] = [];
function getItems($page_number)
{
	$baseURL = 'https://auchan.ua/superceny/page-'.$page_number.'/';
	$pageText = getPageContentAsString($baseURL);
	$page = str_get_html($pageText);
	$pageContent = $page->find('.category-products')[0];
	$items = $pageContent->find('.item');
	if ($items != null) {
		foreach ($items as $item) {
			$itemObject = [];

			/* Product Image */
			$imgBlock = $item->find('picture');
			if ($imgBlock != null) {
				$imgUrl = $imgBlock[0]->find('img');
				$img = $imgUrl[0]->src;
			}else{
				$img = 'null';
			}
			$itemObject[':image_url'] = $img;

			/* Product Name */
			$nameBlock = $item->find('.product-name');
			if ($nameBlock != null) {
				$nameAnchor = $nameBlock[0]->find('a');
				if ($nameAnchor != null) {
					$nameFullText = trim($nameAnchor[0]->text());
					$name = transName($nameFullText);
					$nameArray = explode(' ', $nameFullText);
					$quantity_unit_array = ['г', 'кг', 'шт', 'см', 'мм', 'л', 'мл'];
					$checkQuantityText = trim($nameArray[count($nameArray)-2]);
					$checkQuantityText = str_replace(',', '.', $checkQuantityText);
					$checkQuantityUnitText = trim($nameArray[count($nameArray)-1]);
					if (is_numeric($checkQuantityText)) {
						if (in_array($checkQuantityUnitText, $quantity_unit_array)) {
							$quantity = floatval($checkQuantityText);
							$quantity_unit = trim($checkQuantityUnitText);
						}else{
							$quantity = floatval(1);
							$quantity_unit = "шт";
						}
					}else{
						$quantity = floatval(1);
						$quantity_unit = "шт";
					}
				}else{
					$name = trim($nameBlock[0]->text());
					$quantity = floatval(1);
					$quantity_unit = "шт";
				}
			}else{
				$name = null;
				$quantity = floatval(1);
				$quantity_unit = "шт";
			}
			$itemObject[':name'] = $name;
			$itemObject[':quantity'] = $quantity;
			$itemObject[':quantity_unit'] = $quantity_unit;

			/* Product Parse URL */
			$parse_url_block = $item->find('.product-image');
			if ($parse_url_block != null) {
				$parse_url = $parse_url_block[0]->href;
			}else{
				$parse_url = transliterate($name);
			}
			$itemObject[':parse_url'] = $parse_url;

			/* Product Date End */
			$itemObject[':date_end'] = null;

			/* Product Price Sell */
			$price_sell_block = $item->find('.special-price');
			if ($price_sell_block != null) {
				$price_sell_tag = $price_sell_block[0]->find('.price');
				if ($price_sell_tag != null) {
					$price_sell_text = $price_sell_tag[0]->text();
					$price_sell_float = trim(str_replace(' ', '', $price_sell_text));
					$price_sell = selectClearPrice($price_sell_float);
				}else{
					$price_sell = null;
				}
			}else{
				$price_sell = null;
			}
			$itemObject[':price_sell'] = $price_sell;

			/* Product Price */
			$price_block = $item->find('.old-price');
			if ($price_block != null) {
				$price_tag = $price_block[0]->find('.price');
				if ($price_tag != null) {
					$price_text = $price_tag[0]->text();
					$price_float = trim(str_replace(' ', '', $price_text));
					$price = selectClearPrice($price_float);
				}else{
					$price = null;
				}
			}else{
				$price = null;
			}
			$itemObject[':price'] = $price;

			/* Product Percent Diff */
			if ($price_sell != null && $price != null) {
				$diff = (float)$price - (float)$price_sell;
				$percent_diff_val = $diff * 100 / (float)$price;
				$percent_diff = intval($percent_diff_val);
			}else{
				$percent_diff = null;
			}
			$itemObject[':percent_diff'] = $percent_diff;

			array_push($GLOBALS['all_items'], $itemObject);
		}
	}

	$paginationBlock = $pageContent->find('.pages');
	if ($paginationBlock != null) {
		$nextPageButton = $paginationBlock[0]->find('.next');
		if ($nextPageButton != null) {
			$new_page_number = $page_number+1;
			getItems($new_page_number);
		}
	}
}

getItems(1);

var_dump($GLOBALS['all_items']);