<?php

/*
	*************************	ATTENTION	*************************

	Just because of PHP isn't client side Programming Language
	You should do a little bit hardcoding with JavaScript to scrape 
	the web page that we need

	1. So first of all open this URL page in your Browser -> https://silpo.ua/offers
	2. Scroll down untill you see the end of page
	3. Then right click on page content ( or just press F12 )
	4. Go to console tab of opened prompt
	5. Copy this code and paste there
		var dom = document.getElementsByTagName('html')[0];copy(dom);
	6. Open content.html and press ctrl+a then ctrl+v and then ctrl+s

	That's all!
	Now you can run this php file

	(^_^) 		Thats a joke man  !!!!  LOL  !!!! 		(^_^)
	(^_^) 		But if you don't have programming 		(^_^)
	(^_^)	skills this tutorial is a gift from me NsH  (^_^)

*/

function getPageContentAsString($url){
	$data = array(
		'operationName' => 'offers', 
		'query' => 'query offers($categoryId: ID, $storeIds: [ID], $pagingInfo: InputBatch!, $pageSlug: String!, $random: Boolean!) {   offersSplited(categoryId: $categoryId, storeIds: $storeIds, pagingInfo: $pagingInfo, pageSlug: $pageSlug, random: $random) {     promos {       count       items {         ... on Promo {           ...PromoFragment           __typename         }         __typename       }       __typename     }     products {       count       items {         ... on Product {           ...OptimizedProductsFragment           __typename         }         __typename       }       __typename     }     coupons {       count       items {         ... on Coupon {           ...OptimizedCouponsFragment           addresses {             store {               id               __typename             }             __typename           }           __typename         }         __typename       }       __typename     }     __typename   } }  fragment PromoFragment on Promo {   id   title   description   detailsButton   position   image {     url     __typename   }   appearance {     ...AppearanceBaseFragment     __typename   }   type   promotion {     slug     title     __typename   }   campaign {     slug     title     __typename   }   link   video   displayType   imageSmall {     url     __typename   }   imageMiddle {     url     __typename   }   imageFull {     url     __typename   }   __typename }  fragment AppearanceBaseFragment on Appearance {   size   color   __typename }  fragment OptimizedProductsFragment on Product {   id   slug   type   title   weight   image {     url     __typename   }   price   oldPrice   discount   points   pointsText   appearance {     size     color     __typename   }   promotion {     id     slug     labelIcon {       url       __typename     }     labelIconReversed {       url       __typename     }     __typename   }   activePeriod {     start     end     __typename   }   __typename }  fragment OptimizedCouponsFragment on Coupon {   id   useType   startedAt   endAt   isChangeable   isCouponControl   listImages   listBrands   signText   rewardValue   unitText   couponDescription   promoDescription   limitDescription   isOff   __typename }',
		'variables' => '{"categoryId":null,"storeIds":null,"pagingInfo":{"offset":0,"limit":999999},"pageSlug":"actions","random":true}',
		'debugName' => ''
	);
	$data_string = json_encode($data);
	$ch = curl_init('https://silpo.ua/graphql');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_REFERER, "https://www.google.com/");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
	    'Content-Length: ' . strlen($data_string))
	);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

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

/* Parsed Main Page */
$GLOBALS['baseURL'] = 'https://silpo.ua/offers';
$pageText = getPageContentAsString('https://silpo.ua/graphql');
$data = json_decode($pageText);

$GLOBALS['all_items'] = [];

$items = $data->data->offersSplited->products->items;
/* Products */
foreach ($items as $item) {

	$itemObject = [];

	/* Product Image */
	if (isset($item->image)) {
		$image = $item->image->url;
	}else{
		$image = 'null';
	}
	$itemObject[':image_url'] = $image;

	if (isset($item->title)) {
		$name = $item->title;
	}else{
		$name = 'null';
	}
	$itemObject[':name'] = $name;

	/* Product Date End */
	$itemObject[':date_end'] = 'null';
	if (isset($item->activePeriod)) {
		if (isset($item->activePeriod->end)) {
			$date_end = explode('T', $item->activePeriod->end);
			$endDay = $date_end[0].' 00:00:00';
			$itemObject[':date_end'] = $endDay;
		}
	}

	/* Product Parse URL */
	if (isset($item->slug)) {
		$itemObject[':parse_url'] = $item->slug.'&date_end='.$endDay;
	}else{
		$itemObject[':parse_url'] = transliterate($name).'&date_end='.$endDay;
	}

	/* Product Price Sell */
	if (isset($item->price)) {
		$price_sell = $item->price;
	}else{
		$price_sell = 'null';
	}
	$itemObject[':price_sell'] = (float)$price_sell;

	/* Product Price */
	if (isset($item->oldPrice)) {
		$price = $item->oldPrice;
	}else{
		$price = 'null';
	}
	$itemObject[':price'] = (float)$price;

	/* Product Percent Diff */
	if (isset($item->oldPrice) && isset($item->price)) {
		$diff = (float)$item->oldPrice - (float)$item->price;
		$percent_diff = $diff * 100 / (float)$item->oldPrice;
		$itemObject[':percent_diff'] = intval($percent_diff);
	}else{
		$itemObject[':percent_diff'] = 'null';
	}
	
	/* Product Quantity and Product Quantity Unit */
	if (isset($item->weight)) {
		$array = str_split($item->weight);
		$quantityText = '';
		$quantityUnit = '';
		foreach ($array as $char) {
			$quantityAllow = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '.', ','];
			if (in_array($char, $quantityAllow)) {
				$quantityText .= $char;
			}else{
				$quantityUnit .= $char;
			}
		}

		if ($quantityText != '') {
			if (strpos('*', $quantityText)) {
				$newQuantityText = explode('*', $quantityText);
				$quantity = (float)$newQuantityText[0] * (float)$newQuantityText[1];
			}else{
				$quantity = (float)$quantityText;
			}
			$itemObject[':quantity'] = $quantity;
		}else{
			$itemObject[':quantity'] = floatval(1);
		}

		if ($quantityUnit != '') {
			$itemObject[':quantity_unit'] = $quantityUnit;
		}else{
			$itemObject[':quantity_unit'] = 'шт';
		}
	}

	array_push($GLOBALS['all_items'], $itemObject);
}

var_dump($GLOBALS['all_items']);