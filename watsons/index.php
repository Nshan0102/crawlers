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
	$text = str_replace($cyr, $lat, $textcyr);
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

/* Parsed Main Page */
$GLOBALS['baseURL'] = 'https://www.watsons.ua/uk/aktsiyi/lc/TBG';
$pageText = getPageContentAsString($baseURL);
$page = str_get_html($pageText);
$pageContent = $page->find('.plp-left-menu')[1];

/* Products */
$GLOBALS['all_items'] = [];

if ($pageContent != null) {
	$leftMenuAnchorBlocks = $pageContent->find('dt');
	if ($leftMenuAnchorBlocks != null) {
		$leftMenuAnchorBlocksCount = 0;
		foreach ($leftMenuAnchorBlocks as $anchorBlock) {

			// This statement is written to parse only one category from left menu items
			// To see the last count of items just uncomment the line below
			// var_dump(count($leftMenuAnchorBlocks));die;
			// You can ++ the number 0 of $leftMenuAnchorBlocksCount
			// In the if statement to parse other categories
			// All this was done to avoid crashes
			// Because of the number of products is huge and the max execution time which was set
			// To 30 minutes was unable to parse all the data

			if ($leftMenuAnchorBlocksCount === 0) {
				$anchorTag = $anchorBlock->find('a');
				if ($anchorTag != null) {
					$anchor = 'https://www.watsons.ua/'.$anchorTag[0]->href;
					//echo '-> '.$anchor.'<br>';
					$singlePageText = getPageContentAsString($anchor);
					$singlePage = str_get_html($singlePageText);
					if ($singlePage != null) {
						$paginationBlock = $singlePage->find('.pagination');
						if ($paginationBlock != null) {
							$paginationAnchors = $paginationBlock[0]->find('a');
							if ($paginationAnchors != null) {
								$lastPageAnchorTag = $paginationAnchors[count($paginationAnchors)-1];
								$lastPage = "https://www.watsons.ua".$lastPageAnchorTag->href;
								$lastPageArray = explode('amp;', $lastPage);
								$pageCount = 0;
								foreach ($lastPageArray as $pageNumber) {
									if (strpos($pageNumber, 'page=') !== false) {
									    $pageText = str_replace('page=', '', $pageNumber);
									    $pageText = str_replace('&', '', $pageText);
									    $pageCount = intval($pageText);
									}
								}
								if (is_numeric($pageCount)) {
									for ($i = 0; $i < $pageCount; $i++) {
										$productsPageHref = "https://www.watsons.ua/uk/aktsiyi/zhovtiy-tsinnik/c/Sub1?q=:topRated&page=".$i."&resultsForPage=20&text=&sort=topRated";
										//echo $productsPageHref.'<br>';
										$productsSinglePageText = getPageContentAsString($productsPageHref);
										$productsSinglePage = str_get_html($productsSinglePageText);
										if ($productsSinglePage != null) {
											$items = $productsSinglePage->find('.product-tile__content');
											if ($items != null) {
												foreach ($items as $item) {
													$isactiveProduct = $item->find('div[data-in-stock="true"]');
													if ($isactiveProduct != null) {
														$itemObject = [];

														/* Product Image */
														$imgBlock = $item->find('.product-tile__product-link');
														if ($imgBlock != null) {
															$imgUrl = $imgBlock[0]->find('img');
															$img = 'https://www.watsons.ua'.$imgUrl[0]->src;
														}else{
															$img = 'null';
														}
														//echo $img.'<br>';
														$itemObject[':image_url'] = $img;

														/* Product Name */
														$nameBlock = $item->find('.product-tile__product-name-link__description-overflow');
														if ($nameBlock != null) {
															$name = trim($nameBlock[0]->text());
														}else{
															$name = null;
														}
														//echo $name.'<br>';
														$itemObject[':name'] = transName($name);

														/* Product Parse URL */
														$parse_url_Block = $item->find('.product-tile__product-link');
														if ($parse_url_Block != null) {
															$parse_url = 'https://www.watsons.ua'.$parse_url_Block[0]->href;
														}else {
															$parse_url = null;
														}
														//echo 'ParseURL'.$parse_url.'<br>';
														$itemObject[':parse_url'] = $parse_url;

														/* Product Quantity */
														if ($parse_url != null) {
															$quantityContent = getPageContentAsString($parse_url.'/variantOptions');
															$quantityPage = str_get_html($quantityContent);
															$quantityTextBlock = $quantityPage->find('.variants__size-item');
															if ($quantityTextBlock != null) {
																$quantityAndUnitText = trim($quantityTextBlock[0]->text());
																$quantityAndUnitArray = explode(' ', $quantityAndUnitText);
																$quantity = floatval($quantityAndUnitArray[0]);
																$quantity_unit = $quantityAndUnitArray[1];
															}else{
																$quantity = 1;
																$quantity_unit = 'шт';
															}
														}
														//echo $quantity.' '.$quantity_unit.'<br>';
														$itemObject[':quantity'] = $quantity;
														$itemObject[':quantity_unit'] = $quantity_unit;
														

														/* Product Date End */
														$itemObject[':date_end'] = null;
														
														/* Product Price Sell */
														$price_sell_block = $item->find('.product-tile__price--discounted');
														if ($price_sell_block != null) {
															$price_sell_float = trim(str_replace(' ', '', $price_sell_block[0]->text()));
															$price_sell = selectClearPrice($price_sell_float);
														}else{
															$price_sell = 'null';
														}
														//echo 'Price Sell ---> '. $price_sell.'<br>';
														$itemObject[':price_sell'] = $price_sell;

														/* Product Price */
														$price_block = $item->find('.product-tile__price--old');
														$price_original_block = $item->find('.product-tile__price--original');
														$price = 0;
														if ($price_block != null) {
															$priceText = trim($price_block[0]->text());
															if ($priceText != '') {
																$price_float = trim(str_replace(' ', '', $priceText));
																$price = selectClearPrice($price_float);
															}elseif($price_original_block != null){
																$priceOriginalText = trim($price_original_block[0]->text());
																$price_float = trim(str_replace(' ', '', $priceOriginalText));
																$price = selectClearPrice($price_float);
															}else{
																$price = 'null';
															}
														}else{
															$price = 'null';
														}
														$itemObject[':price'] = $price;
														//echo 'Price ---> '. $price.'<br>';

														/* Product Percent Diff */
														if ($price_sell != "null" && $price != "null") {
															$diff = (float)$price - (float)$price_sell;
															$percent_diff_val = $diff * 100 / (float)$price;
															$percent_diff = intval($percent_diff_val);
														}else{
															$percent_diff = null;
														}
														$itemObject[':percent_diff'] = $percent_diff;

														array_push($GLOBALS['all_items'], $itemObject);	
													}else{
														echo 'This product is not active';
													}
												}
											}
										}
									}
								}else{
									$items = $singlePage->find('.product-tile__content');
									if ($items != null) {
										foreach ($items as $item) {
											$isactiveProduct = $item->find('div[data-in-stock="true"]');
											if ($isactiveProduct != null) {
												$itemObject = [];

												/* Product Image */
												$imgBlock = $item->find('.product-tile__product-link');
												if ($imgBlock != null) {
													$imgUrl = $imgBlock[0]->find('img');
													$img = 'https://www.watsons.ua'.$imgUrl[0]->src;
												}else{
													$img = 'null';
												}
												//echo $img.'<br>';
												$itemObject[':image_url'] = $img;

												/* Product Name */
												$nameBlock = $item->find('.product-tile__product-name-link__description-overflow');
												if ($nameBlock != null) {
													$name = trim($nameBlock[0]->text());
												}else{
													$name = null;
												}
												//echo $name.'<br>';
												$itemObject[':name'] = transName($name);

												/* Product Parse URL */
												$parse_url_Block = $item->find('.product-tile__product-link');
												if ($parse_url_Block != null) {
													$parse_url = 'https://www.watsons.ua'.$parse_url_Block[0]->href;
												}else {
													$parse_url = null;
												}
												//echo 'ParseURL'.$parse_url.'<br>';
												$itemObject[':parse_url'] = $parse_url;

												/* Product Quantity */
												if ($parse_url != null) {
													$quantityContent = getPageContentAsString($parse_url.'/variantOptions');
													$quantityPage = str_get_html($quantityContent);
													$quantityTextBlock = $quantityPage->find('.variants__size-item');
													if ($quantityTextBlock != null) {
														$quantityAndUnitText = trim($quantityTextBlock[0]->text());
														$quantityAndUnitArray = explode(' ', $quantityAndUnitText);
														$quantity = floatval($quantityAndUnitArray[0]);
														$quantity_unit = $quantityAndUnitArray[1];
													}else{
														$quantity = 1;
														$quantity_unit = 'шт';
													}
												}
												//echo $quantity.' '.$quantity_unit.'<br>';
												$itemObject[':quantity'] = $quantity;
												$itemObject[':quantity_unit'] = $quantity_unit;
												

												/* Product Date End */
												$itemObject[':date_end'] = null;
												
												/* Product Price Sell */
												$price_sell_block = $item->find('.product-tile__price--discounted');
												if ($price_sell_block != null) {
													$price_sell_float = trim(str_replace(' ', '', $price_sell_block[0]->text()));
													$price_sell = selectClearPrice($price_sell_float);
												}else{
													$price_sell = 'null';
												}
												//echo 'Price Sell ---> '. $price_sell.'<br>';
												$itemObject[':price_sell'] = $price_sell;

												/* Product Price */
												$price_block = $item->find('.product-tile__price--old');
												$price_original_block = $item->find('.product-tile__price--original');
												$price = 0;
												if ($price_block != null) {
													$priceText = trim($price_block[0]->text());
													if ($priceText != '') {
														$price_float = trim(str_replace(' ', '', $priceText));
														$price = selectClearPrice($price_float);
													}elseif($price_original_block != null){
														$priceOriginalText = trim($price_original_block[0]->text());
														$price_float = trim(str_replace(' ', '', $priceOriginalText));
														$price = selectClearPrice($price_float);
													}else{
														$price = 'null';
													}
												}else{
													$price = 'null';
												}
												$itemObject[':price'] = $price;
												//echo 'Price ---> '. $price.'<br>';
												/* Product Percent Diff */
												if ($price_sell != "null" && $price != "null") {
													$diff = (float)$price - (float)$price_sell;
													$percent_diff_val = $diff * 100 / (float)$price;
													$percent_diff = intval($percent_diff_val);
												}else{
													$percent_diff = null;
												}
												$itemObject[':percent_diff'] = $percent_diff;

												array_push($GLOBALS['all_items'], $itemObject);	
											}else{
												echo 'This product is not active';
											}
										}
									}
								}
							}
						}else{
							$items = $singlePage->find('.product-tile__content');
							if ($items != null) {
								foreach ($items as $item) {
									$isactiveProduct = $item->find('div[data-in-stock="true"]');
									if ($isactiveProduct != null) {
										$itemObject = [];

										/* Product Image */
										$imgBlock = $item->find('.product-tile__product-link');
										if ($imgBlock != null) {
											$imgUrl = $imgBlock[0]->find('img');
											$img = 'https://www.watsons.ua'.$imgUrl[0]->src;
										}else{
											$img = 'null';
										}
										//echo $img.'<br>';
										$itemObject[':image_url'] = $img;

										/* Product Name */
										$nameBlock = $item->find('.product-tile__product-name-link__description-overflow');
										if ($nameBlock != null) {
											$name = trim($nameBlock[0]->text());
										}else{
											$name = null;
										}
										//echo $name.'<br>';
										$itemObject[':name'] = transName($name);

										/* Product Parse URL */
										$parse_url_Block = $item->find('.product-tile__product-link');
										if ($parse_url_Block != null) {
											$parse_url = 'https://www.watsons.ua'.$parse_url_Block[0]->href;
										}else {
											$parse_url = null;
										}
										//echo 'ParseURL'.$parse_url.'<br>';
										$itemObject[':parse_url'] = $parse_url;

										/* Product Quantity */
										if ($parse_url != null) {
											$quantityContent = getPageContentAsString($parse_url.'/variantOptions');
											$quantityPage = str_get_html($quantityContent);
											$quantityTextBlock = $quantityPage->find('.variants__size-item');
											if ($quantityTextBlock != null) {
												$quantityAndUnitText = trim($quantityTextBlock[0]->text());
												$quantityAndUnitArray = explode(' ', $quantityAndUnitText);
												$quantity = floatval($quantityAndUnitArray[0]);
												$quantity_unit = $quantityAndUnitArray[1];
											}else{
												$quantity = 1;
												$quantity_unit = 'шт';
											}
										}
										//echo $quantity.' '.$quantity_unit.'<br>';
										$itemObject[':quantity'] = $quantity;
										$itemObject[':quantity_unit'] = $quantity_unit;
										

										/* Product Date End */
										$itemObject[':date_end'] = null;
										
										/* Product Price Sell */
										$price_sell_block = $item->find('.product-tile__price--discounted');
										if ($price_sell_block != null) {
											$price_sell_float = trim(str_replace(' ', '', $price_sell_block[0]->text()));
											$price_sell = selectClearPrice($price_sell_float);
										}else{
											$price_sell = 'null';
										}
										//echo 'Price Sell ---> '. $price_sell.'<br>';
										$itemObject[':price_sell'] = $price_sell;

										/* Product Price */
										$price_block = $item->find('.product-tile__price--old');
										$price_original_block = $item->find('.product-tile__price--original');
										$price = 0;
										if ($price_block != null) {
											$priceText = trim($price_block[0]->text());
											if ($priceText != '') {
												$price_float = trim(str_replace(' ', '', $priceText));
												$price = selectClearPrice($price_float);
											}elseif($price_original_block != null){
												$priceOriginalText = trim($price_original_block[0]->text());
												$price_float = trim(str_replace(' ', '', $priceOriginalText));
												$price = selectClearPrice($price_float);
											}else{
												$price = 'null';
											}
										}else{
											$price = 'null';
										}
										$itemObject[':price'] = $price;
										//echo 'Price ---> '. $price.'<br>';

										/* Product Percent Diff */
										if ($price_sell != "null" && $price != "null") {
											$diff = (float)$price - (float)$price_sell;
											$percent_diff_val = $diff * 100 / (float)$price;
											$percent_diff = intval($percent_diff_val);
										}else{
											$percent_diff = null;
										}
										$itemObject[':percent_diff'] = $percent_diff;

										array_push($GLOBALS['all_items'], $itemObject);	
									}else{
										echo 'This product is not active';
									}
								}
							}
						}
					}else{
						echo "<br><hr><br>Sorry we could not find any Product at this page <br>";
						echo $anchor."<br><hr><br>";
					}
				}
			}

			$leftMenuAnchorBlocksCount++;
		}
	}
}

var_dump($GLOBALS['all_items']);