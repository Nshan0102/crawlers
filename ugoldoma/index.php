<?php
include_once('parser_configs.php');
include_once('simple_html_dom.php');
include_once('Phantom.php');
require_once('database.php');
$db = new Database;

// $prodsFilt = $db->query("SELECT `product_id` FROM `oc_ocfilter_option_value_to_product` WHERE `value_id` = 3999331269");
$prodsFilt = $db->query("SELECT * FROM `oc_product` WHERE `product_id` = 1 || `product_id` = 2 || `product_id` = 3 || `product_id` = 4 || `product_id` = 5 || `product_id` = 6 || `product_id` = 7 || `product_id` = 8 || `product_id` = 9 || `product_id` = 10 || `product_id` = 11 || `product_id` = 12 || `product_id` = 13 || `product_id` = 14 || `product_id` = 15 || `product_id` = 16 || `product_id` = 18 || `product_id` = 19 || `product_id` = 20 || `product_id` = 21 || `product_id` = 22 || `product_id` = 23 || `product_id` = 24 || `product_id` = 25 || `product_id` = 26 || `product_id` = 27 || `product_id` = 28 || `product_id` = 29 || `product_id` = 30 || `product_id` = 31 || `product_id` = 32 || `product_id` = 33 || `product_id` = 34 || `product_id` = 35 || `product_id` = 36 || `product_id` = 37 || `product_id` = 38 || `product_id` = 39 || `product_id` = 40 || `product_id` = 41 || `product_id` = 42 || `product_id` = 43 || `product_id` = 44 || `product_id` = 45 || `product_id` = 46 || `product_id` = 47 || `product_id` = 48 || `product_id` = 49 || `product_id` = 50 || `product_id` = 51 || `product_id` = 52 || `product_id` = 53 || `product_id` = 54 || `product_id` = 55 || `product_id` = 57 || `product_id` = 58 || `product_id` = 63 || `product_id` = 65 || `product_id` = 66 || `product_id` = 67 || `product_id` = 68 || `product_id` = 333 || `product_id` = 334 || `product_id` = 335 || `product_id` = 336 || `product_id` = 337 || `product_id` = 338 || `product_id` = 339 || `product_id` = 340 || `product_id` = 341 || `product_id` = 343 || `product_id` = 344 || `product_id` = 345 || `product_id` = 346 || `product_id` = 347 || `product_id` = 348 || `product_id` = 350 || `product_id` = 351 || `product_id` = 352 || `product_id` = 353 || `product_id` = 356 || `product_id` = 357 || `product_id` = 358 || `product_id` = 359 || `product_id` = 360 || `product_id` = 361 || `product_id` = 362 || `product_id` = 363 || `product_id` = 364 || `product_id` = 365 || `product_id` = 366 || `product_id` = 367 || `product_id` = 368 || `product_id` = 369 || `product_id` = 370 || `product_id` = 373 || `product_id` = 374 || `product_id` = 375 || `product_id` = 376 || `product_id` = 377 || `product_id` = 378 || `product_id` = 379 || `product_id` = 380 || `product_id` = 381 || `product_id` = 382 || `product_id` = 383 || `product_id` = 384 || `product_id` = 385 || `product_id` = 386 || `product_id` = 387 || `product_id` = 388 || `product_id` = 389 || `product_id` = 390 || `product_id` = 391 || `product_id` = 392 || `product_id` = 393 || `product_id` = 399 || `product_id` = 400 || `product_id` = 401 || `product_id` = 402 || `product_id` = 403 || `product_id` = 404 || `product_id` = 405 || `product_id` = 406 || `product_id` = 407 || `product_id` = 408 || `product_id` = 409 || `product_id` = 410 || `product_id` = 411 || `product_id` = 414 || `product_id` = 415 || `product_id` = 450");
// var_dump($prodsFilt);
$productCount = 468;
foreach ($prodsFilt as $value) {
	$productCount++;
	$prodSku = '00'.$productCount;
	$prodId = (int)$value["product_id"];
	$manufac = (int)$value["manufacturer_id"];
	$db->execute("INSERT INTO `oc_product`  (	`product_id`,`model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `price`, `tax_class_id`, `date_available`, `weight_class_id`, `date_added`, `date_modified`, `length`, `width`, `height`, `status`, `subtract`, `length_class_id`,`shipping`) VALUES ('$productCount', '$prodSku', '', '','', '/м²', '', '', '', 1000, 9, '', $manufac, 100, 0, '2018-06-20', 1, '2019-07-18 00:31:11', '2019-07-18 00:31:14', 0, 0, 0, 1, 0, 1, 0);");
	$productName = $db->query("SELECT `name` FROM `oc_product_description` WHERE `product_id` = '$prodId'");
	$name = $productName[0]["name"];
	$prodName = 'Угловой элемент '.$name;
	$db->execute("INSERT INTO `oc_product_description` SET `product_id` = '$productCount', `language_id` = 1, `name` = '$prodName', `description` = '$desc', `meta_description` = '', `meta_keyword` = '', `meta_title` = '$prodName', `meta_h1` = '', `tag` = '';");
	$categId = $db->query("SELECT `category_id` FROM `oc_product_to_category` WHERE `product_id` = '$prodId'");
	$categ = (int)$categId[0]["category_id"];
	$db->execute("INSERT INTO `oc_product_to_category` SET `product_id` = '$productCount', `category_id` = '$categ', `main_category` = 1");
	$db->execute("INSERT INTO `oc_product_to_layout` SET `product_id` = '$productCount', `store_id` = 0, `layout_id` = 0");
	$db->execute("INSERT INTO `oc_product_to_store` SET `product_id` = '$productCount', `store_id` = 0");
}


die('Loft Style');
function translit($str) {
	$rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я','№',' ');
	$lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'No','_');
	return str_replace($rus, $lat, $str);
}
function lengthWidthHeight($str){
	$splited = explode('х', $str);
	$l = trim(str_replace('см', '', $splited[0]));
	$l = trim(str_replace(',', '.', $l));
	$w = trim(str_replace('см', '', $splited[1]));
	$w = trim(str_replace(',', '.', $w));
	$h = trim(str_replace('см', '', $splited[2]));
	$h = trim(str_replace(',', '.', $h));
	$array= [$l,$w,$h];
	return $array;
}
$catalogUrl = 'https://xn--80asecmsgbp.xn--p1ai/dekorativnyj-kirpich/';
$html = file_get_html($catalogUrl);
$productContainer = $html->find('.row')[6];
$productItems = $productContainer->find('.product-layout');
$productCount = 18;
foreach ($productItems as $items) {
	$singlePage = $items->find('a')[0];
	$singlePageUrl = $singlePage->href;
	$fullProductPageUrl = $singlePageUrl;
	$productSinglePage = file_get_html($fullProductPageUrl);
	$productName = $productSinglePage->find('h1')[0]->text();
	$productPriceBlock = $productSinglePage->find('#formated_price')[0]->text();
	$prodPriceReplace = str_replace("руб", "", $productPriceBlock);
	$productPrice = str_replace('.', "", $prodPriceReplace);
	$productPrice = str_replace(' ', "", $productPrice);
	$productSpecificationTab = $productSinglePage->find('#tab-specification');
	$tbodys = $productSpecificationTab[0]->find('tbody');
	$prod_length = 0;  // attr 11
	$prod_width = 0;   // attr 10
	$prod_heigth = 0;  // attr 12
	$upakovka_length = 0; // attr 18
	$upakovka_width = 0; // attr 17
	$upakovka_heigth = 0; // attr 19
	$material = ''; // attr 20
	$strana = ''; // attr 14
	foreach ($tbodys as $tbody) {
		$trs = $tbody->find('tr');
		foreach ($trs as $tr) {
			$tds = $tr->find('td');
			$name = trim($tds[0]->text());
			$value = trim($tds[1]->text());
			if ($name == 'Размер ложок') {
				$prod_lwh = lengthWidthHeight($value);
				$prod_length = $prod_lwh[0];
				$prod_width = $prod_lwh[1];
				$prod_heigth = $prod_lwh[2];
				$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_length'");
				if (strpos($prod_length, '/') > 0) {
					$splited_length = explode('/', $prod_length);
					$one = $splited_length[0];
					$two = $splited_length[1];
					$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$one'");
					if (!isset($value_for_length[0]["value_id"])) {
						$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$two'");
					}
				}
			
				$value_for_width = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_width'");
				$value_for_heigth = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_heigth'");
				$length_filter = 0;
				$width_filter = 0;
				$heigth_filter = 0;
				if (isset($value_for_length[0]["value_id"])) {
					$length_filter = (int)$value_for_length[0]["value_id"];
				}
				if (isset($value_for_width[0]["value_id"])) {
					$width_filter = (int)$value_for_width[0]["value_id"];
				}
				if (isset($value_for_heigth[0]["value_id"])) {
					$heigth_filter = (int)$value_for_heigth[0]["value_id"];
				}
				if ($length_filter != 0) {
					$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10011, `value_id` = '$length_filter'");
				}
				if ($width_filter != 0) {
					$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10010, `value_id` = '$width_filter'");
				}
				if ($heigth_filter != 0) {
					$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10012, `value_id` = '$heigth_filter'");
				}
				
			}
			if ($name == 'Размер упаковки (ДхШхВ)') {
				$upakovka_lwh = lengthWidthHeight($value);
				$upakovka_length = $upakovka_lwh[0];
				$upakovka_width = $upakovka_lwh[1];
				$upakovka_heigth = $upakovka_lwh[2];
			}
			if ($name == 'Страна производства') {
				$strana = trim($value);
				$value_for_strana = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$strana'");
				$strana_filter = 0;
				if (isset($value_for_strana[0]["value_id"])) {
					$strana_filter = (int)$value_for_strana[0]["value_id"];
				}
				if ($strana_filter != 0) {
					$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10014, `value_id` = '$strana_filter'");
				}
			}
			if ($name == 'Уголок') {
				$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10015, `value_id` = 3999331269");
			}
			if ($name == 'Материал') {
				$material = trim($value);
				$value_for_material = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$material'");
				$material_filter = 0;
				if (isset($value_for_material[0]["value_id"])) {
					$material_filter = (int)$value_for_material[0]["value_id"];
				}
				if ($material_filter != 0) {
					$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10020, `value_id` = '$material_filter'");
				}
			}
			
		}
	}
	$translateName = translit($productName);
	$lowerCaseProductName = strtolower($translateName);
	$directoryName = 'catalog/products/'.$lowerCaseProductName.'_'.$productCount;
	if(!is_dir($directoryName)){
		mkdir($directoryName, 0755);
	}
	$imgCount = 0;
	$imgBlock = $productSinglePage->find('#one-image');
	$imgTag = $imgBlock[0]->find('img');
	$mainImage = '';
	foreach ($imgTag as $imgSrc) {
		$imageSrc = $imgSrc->src;
		$imageName = $lowerCaseProductName.'_'.$imgCount;
		$output = $directoryName.'/'.$imageName.'.jpg';
		file_put_contents($output, file_get_contents($imageSrc));
		if ($imgCount == 0) {
			$mainImage = $output;
		}
		$db->execute("INSERT INTO `oc_product_image` SET `product_id` = '$productCount', `image` = '$output', `sort_order` = 0");
		$imgCount++;
	}
	$descriptionTab =  $productSinglePage->find('#tab-description');
	$desc = '';
	if (!empty($descriptionTab[0]->text())) {
		$desc = $descriptionTab[0]->text();
	}
	if($productCount < 10){
		$prodSku = '0000'.$productCount;
	}
	if ($productCount > 9 && $productCount < 100) {
		$prodSku = '000'.$productCount;
	}
	if ($productCount > 99 && $productCount < 1000) {
		$prodSku = '00'.$productCount;
	}
	if ($productCount > 999 && $productCount < 10000) {
		$prodSku = '0'.$productCount;
	}
	if ($productCount > 9999 && $productCount < 100000) {
		$prodSku = $productCount;
	}
	$prodPrice = $productPrice;
	$prodMainImage = $mainImage;



	$db->execute("INSERT INTO `oc_product`  (	`product_id`,`model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `price`, `tax_class_id`, `date_available`, `weight_class_id`, `date_added`, `date_modified`, `length`, `width`, `height`, `status`, `subtract`, `length_class_id`,`shipping`) VALUES ('$productCount', '$prodSku', '', '','', '/м²', '', '', '', 1000, 9, '$prodMainImage', 4, '$prodPrice', 0, '2018-06-20', 1, '2019-07-18 00:31:11', '2019-07-18 00:31:14', '$upakovka_length', '$upakovka_width', '$upakovka_heigth', 1, 0, 1, 0);");


	



	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 11, `language_id` = 1, `text` = '$prod_length'");
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 10, `language_id` = 1, `text` = '$prod_width'");
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 12, `language_id` = 1, `text` = '$prod_heigth'");
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 18, `language_id` = 1, `text` = '$upakovka_length'");
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 17, `language_id` = 1, `text` = '$upakovka_width'");
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 19, `language_id` = 1, `text` = '$upakovka_heigth'");
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 20, `language_id` = 1, `text` = '$material'");
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 14, `language_id` = 1, `text` = '$strana'");
	$db->execute("INSERT INTO `oc_product_description` SET `product_id` = '$productCount', `language_id` = 1, `name` = '$productName', `description` = '$desc', `meta_description` = '', `meta_keyword` = '', `meta_title` = '$productName', `meta_h1` = '', `tag` = '';");
	$db->execute("INSERT INTO `oc_product_to_category` SET `product_id` = '$productCount', `category_id` = 1, `main_category` = 1");
	$db->execute("INSERT INTO `oc_product_to_layout` SET `product_id` = '$productCount', `store_id` = 0, `layout_id` = 0");
	$db->execute("INSERT INTO `oc_product_to_store` SET `product_id` = '$productCount', `store_id` = 0");
	$productCount++;
}