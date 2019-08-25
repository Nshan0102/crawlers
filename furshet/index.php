<?php
include_once('parser_configs.php');
include_once('simple_html_dom.php');

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
$endDatePageText = getPageContentAsString('https://furshet.ua/scandalous_price');
$endDatePage = str_get_html($endDatePageText);
$endDateTag = $endDatePage->find('.brend-banner__text')[0];
$endDateTextArray = explode('до', $endDateTag->text());
$endDateText = $endDateTextArray[count($endDateTextArray)-1];
$endDayText = trim($endDateText);
$endDayDateFormat = strtotime($endDayText);
$GLOBALS['endDay'] = date('Y-m-d H:i:s', $endDayDateFormat);

/* Parsed Main Page */
$text = getPageContentAsString('https://furshet.ua/actions');
$html = str_get_html($text);
$pagination = $html->find('.site-pager')[0];
$paginationItems = $pagination->find('a');
$pages = ['https://furshet.ua/actions'];
for ($i=1; $i < count($paginationItems) -2; $i++) { 
	$pageUrl = 'https://furshet.ua'.$paginationItems[$i]->href;
	array_push($pages,$pageUrl);
}

/* Products */
$GLOBALS['all_items'] = [];
if (count($pages) > 0) {
	foreach ($pages as $value) {
		$pageText = getPageContentAsString($value);
		$pageDOM = str_get_html($pageText);

		$productSections = $pageDOM->find('.actions-list');
		if ($productSections != null) {
			foreach ($productSections as $section) {
				$items = $section->find('.item');
				if ($items != null) {
					foreach ($items as $item) {
						$itemObject = [];

						/* Product Parse URL */
						$itemObject[':parse_url'] = 'https://furshet.ua/scandalous_price';

						/* Product Image */
						$imgBlock = $item->find('.img');
						if ($imgBlock != null) {
							$imgTag = $imgBlock[0]->find('img');
							if ($imgTag != null) {
								$img = $imgTag[0]->src;
							}else{
								$img = 'null';
							}
						}else{
							$img = 'null';
						}
						$itemObject[':image_url'] = $img;

						/* Product Name */
						$moreNameBlock = $item->find('.more_text');
						if ($moreNameBlock != null) {
							$moreNameTag = $moreNameBlock[0]->find('p');
							if ($moreNameTag != null) {
								$fullMoreName = $moreNameTag[0]->text();
								$fullMoreNameArray = explode(' ', $fullMoreName);
								$newMoreName = '';
								for ($i = 0; $i < count($fullMoreNameArray)-2; $i++) {
									$newMoreName .= $fullMoreNameArray[$i].' ';
								}
								$name = trim($newMoreName);
								/* Product Quantity */
								$quantity = $fullMoreNameArray[count($fullMoreNameArray)-1];
								/* Product Quantity Unit */
								$quantity_unit = $fullMoreNameArray[count($fullMoreNameArray)-2];
							}else{
								$name = 'null';
							}
						}else{
							$nameBlock = $item->find('.desc');
							if ($nameBlock != null) {
								$nameTag = $nameBlock[0]->find('p');
								if ($nameTag != null) {
									$fullName = $nameTag[0]->text();
									$fullNameArray = explode(' ', $fullName);
									$newName = '';
									for ($i = 0; $i < count($fullNameArray)-2; $i++) {
										$newName .= $fullNameArray[$i].' ';
									}
									$name = trim($newName);
									/* Product Quantity */
									$quantity = $fullNameArray[count($fullNameArray)-1];
									/* Product Quantity Unit */
									$quantity_unit = $fullNameArray[count($fullNameArray)-2];
								}else{
									$name = 'null';
								}
							}else{
								$name = 'null';
							}
						}
						
						$itemObject[':name'] = $name;
						$itemObject[':quantity'] = $quantity;
						$itemObject[':quantity_unit'] = $quantity_unit;


						/* Product Date End */
						$date_end = $GLOBALS['endDay'];
						$date_end_block = $item->find('.actions-list__price');
						if ($date_end_block != null) {
							$dateTagParent = $date_end_block[0]->find('.date-cost');
							if ($dateTagParent != null) {
								$dateTagSub = $dateTagParent[0]->find('div');
								if($dateTagSub != null){
									$date_end_span = $dateTagSub[0]->find('span');
									if($date_end_span != null){
										$dateText = $date_end_span[count($date_end_span)-1]->text();
										$endDayDateFormat = strtotime($dateText.'.2019');
										$date_end = date('Y-m-d H:i:s', $endDayDateFormat);
									}
								}
							}
						}
						$itemObject[':date_end'] = $date_end;

						/* Product Price Sell */
						$price_sell_block = $item->find('.cost');
						if ($price_sell_block != null) {
							$price_sell_full_text = $price_sell_block[0]->innertext;
							$price_sell_array = explode('<sup>', $price_sell_full_text);
							$price_sell_int = trim($price_sell_array[0]);
							$price_sell_float = trim(str_replace('</sup>', '', $price_sell_array[1]));
							$price_sell_text = $price_sell_int.'.'.$price_sell_float;
							$price_sell = (float)$price_sell_text;
						}else{
							$price_sell = 'null';
						}
						$itemObject[':price_sell'] = $price_sell;

						/* Product Price */
						$price_block = $item->find('.del-cost');
						if ($price_block != null) {
							$price_full_text = $price_block[0]->innertext;
							$price_array = explode('<sup>', $price_full_text);
							$price_int = trim($price_array[0]);
							$price_float = trim(str_replace('</sup>', '', $price_array[1]));
							$price_text = $price_int.'.'.$price_float;
							$price = (float)$price_text;
						}else{
							$price = 'null';
						}
						$itemObject[':price'] = $price;

						/* Product Percent Diff */
						$percent_diff_block = $item->find('.sale');
						if ($percent_diff_block != null) {
							$percent_diff = $percent_diff_block[0]->plaintext;
						}else{
							$percent_diff = 'null';
						}
						$itemObject[':percent_diff'] = $percent_diff;

						array_push($GLOBALS['all_items'], $itemObject);
					}
				}
			}
		}
	}	
}

var_dump($GLOBALS['all_items']);