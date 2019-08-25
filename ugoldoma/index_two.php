<?php
include_once('parser_configs.php');
include_once('simple_html_dom.php');
include_once('Phantom.php');
require_once('database.php');
$db = new Database;

die('leonardo-stone');
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
$catalogUrl = 'leonardo-stone.html';
$html = file_get_html($catalogUrl);
$productContainer = $html->find('#mse2_filter-results')[0];
$productItems = $productContainer->find('.thumb--border');
$productCount = 69;
foreach ($productItems as $items) {
	$singlePage = $items->href;
	$singlePageUrl = 'https://leonardo-stone.ru/'.$singlePage;
	echo $singlePageUrl."<br>";
	$fullProductPageUrl = $singlePageUrl;
	$productSinglePage = file_get_html($fullProductPageUrl);
	$productName = $productSinglePage->find('h2')[0]->text();
	$productPriceBlock = $productSinglePage->find('.collection-preview-product__price')[0]->text();
	$prodPriceReplace = str_replace("₽", "", $productPriceBlock);
	$productPrice = str_replace('/', "", $prodPriceReplace);
	$productPrice = str_replace('м2', "", $productPrice);	
	$productPrice = str_replace(' ', "", $productPrice);	

	$productSpecificationTab = $productSinglePage->find('.tab__body')[0];
	$tbodys = $productSpecificationTab->find('tbody');
	$prod_length = 0;  // attr 11
	$prod_width = 0;   // attr 10
	$prod_heigth = 0;  // attr 12
	$upakovka_length = 0; // attr 18
	$upakovka_width = 0; // attr 17
	$upakovka_heigth = 0; // attr 19
	$material = ''; // attr 20
	$strana = ''; // attr 14
	
		$trs = $tbodys[0]->find('tr');
		foreach ($trs as $tr) {
			$tds = $tr->find('td');
			if (count($tds) > 0) {
				$name = trim($tds[0]->text());
			}
			if (count($tds) > 1) {
				$value = trim($tds[1]->text());
			}
			
			// var_dump($value);die;
			if ($name == 'Длина плоского элемента (L) коллекции, см:') {
				$prod_length = $value;
				$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_length'");
				if (strpos($prod_length, ';') > 0) {
					$splited_length = explode(';', $prod_length);
					$one = trim($splited_length[0]);
					$two = trim($splited_length[1]);
					$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$one'");
					if (!isset($value_for_length[0]["value_id"])) {
						$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$two'");
					}
				}
			}
			if ($name == 'Высота плоского элемента коллекции (H), см:') {
				$prod_width = $value;
				$value_for_width = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_width'");
			}
			if ($name == 'Толщина плоского элемента коллекции (T), см:') {
				$prod_heigth = $value;
				$value_for_heigth = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_heigth'");
				if (strpos($prod_heigth, '-') > 0) {
					$splited_length = explode(';', $prod_length);
					$one = trim($splited_length[0]);
					$two = trim($splited_length[1]);
					$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$one'");
					if (!isset($value_for_length[0]["value_id"])) {
						$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$two'");
					}
				}
			}
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
			if ($name == 'Вес упаковки') {
				$upakovka_lwh = lengthWidthHeight($value);
				$upakovka_length = $upakovka_lwh[0];
				$upakovka_width = $upakovka_lwh[1];
				$upakovka_heigth = $upakovka_lwh[2];
			}			
		}
	if ($productSinglePage->find('#tab-angular')[0] != null) {
		// $db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10015, `value_id` = 3999331269");
	}
	$translateName = translit($productName);
	$lowerCaseProductName = strtolower($translateName);
	$directoryName = 'catalog/products/'.$lowerCaseProductName.'_'.$productCount;
	if(!is_dir($directoryName)){
		mkdir($directoryName, 0755);
	}
	$imgCount = 0;
	$imgBlock = $productSinglePage->find('.slider-vertical')[0];
	$imgTag = $imgBlock->find('img');
	$mainImage = '';
	foreach ($imgTag as $imgSrc) {
		$imageSrc = $imgSrc->src;
		$imageSrc = 'https://leonardo-stone.ru/'.$imageSrc;
		$imageName = $lowerCaseProductName.'_'.$imgCount;
		$output = $directoryName.'/'.$imageName.'.jpg';
		file_put_contents($output, file_get_contents($imageSrc));
		if ($imgCount == 0) {
			$mainImage = $output;
		}
		// $db->execute("INSERT INTO `oc_product_image` SET `product_id` = '$productCount', `image` = '$output', `sort_order` = 0");
		$imgCount++;
	}
	$descriptionTab =  $productSinglePage->find('.tab__id--body');
	$desc = '';
	if (!empty($descriptionTab[0]->text())) {
		foreach ($descriptionTab[0]->find('p') as $value) {
			$desc .= $value->text();
		}
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

	echo $productName.'<br>';
	echo $productCount.'<br>';
	echo $prodSku.'<br>';
	echo $prodMainImage.'<br>';
	echo $upakovka_length.'<br>';
	echo $upakovka_width.'<br>';
	echo $upakovka_heigth.'<br>';
	echo $prodPrice.'<br>';
	echo $prod_length.'<br>';
	echo $prod_width.'<br>';
	echo $prod_heigth.'<br>';
	echo $material.'<br>';
	echo $strana.'<br>';
	echo $desc.'<br>';
	echo '<hr>';
	// die;

	$db->execute("INSERT INTO `oc_product`  (	`product_id`,`model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `price`, `tax_class_id`, `date_available`, `weight_class_id`, `date_added`, `date_modified`, `length`, `width`, `height`, `status`, `subtract`, `length_class_id`,`shipping`) VALUES ('$productCount', '$prodSku', '', '','', '/м²', '', '', '', 1000, 9, '$prodMainImage', 4, '$prodPrice', 0, '2018-06-20', 1, '2019-07-18 00:31:11', '2019-07-18 00:31:14', '$upakovka_length', '$upakovka_width', '$upakovka_heigth', 1, 0, 1, 0);");
	if ($length_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 11, `language_id` = 1, `text` = '$prod_length'");
	}
	if ($width_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 10, `language_id` = 1, `text` = '$prod_width'");
	}
	if ($heigth_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 12, `language_id` = 1, `text` = '$prod_heigth'");
	}
	if ($upakovka_length != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 18, `language_id` = 1, `text` = '$upakovka_length'");
	}
	if ($upakovka_width != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 17, `language_id` = 1, `text` = '$upakovka_width'");
	}
	if ($upakovka_heigth != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 19, `language_id` = 1, `text` = '$upakovka_heigth'");
	}
	if ($material != '') {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 20, `language_id` = 1, `text` = '$material'");
	}
	if ($strana != '') {
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 14, `language_id` = 1, `text` = '$strana'");
	}
	$db->execute("INSERT INTO `oc_product_description` SET `product_id` = '$productCount', `language_id` = 1, `name` = '$productName', `description` = '$desc', `meta_description` = '', `meta_keyword` = '', `meta_title` = '$productName', `meta_h1` = '', `tag` = '';");
	$db->execute("INSERT INTO `oc_product_to_category` SET `product_id` = '$productCount', `category_id` = 1, `main_category` = 1");
	$db->execute("INSERT INTO `oc_product_to_layout` SET `product_id` = '$productCount', `store_id` = 0, `layout_id` = 0");
	$db->execute("INSERT INTO `oc_product_to_store` SET `product_id` = '$productCount', `store_id` = 0");
	$productCount++;
}