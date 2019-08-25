<?php
include_once('parser_configs.php');
include_once('simple_html_dom.php');
include_once('Phantom.php');
require_once('database.php');
$db = new Database;
$phantom = new Phantom;
die('idealstone');
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
$catalogUrl = 'https://www.idealstone.ru/katalog/dekorativnyy-kirpich/?PAGEN_1=5';
$html = file_get_html($catalogUrl);
$productContainer = $html->find('.bx_catalog_list_home')[0];
$productItems = $productContainer->find('.bx_catalog_item_images_double');
$productCount = 412;
foreach ($productItems as $items) {
	$singlePage = $items->href;
	$singlePageUrl = 'https://www.idealstone.ru/'.$singlePage;
	$page = $phantom->getContent($singlePageUrl);
	// echo $singlePageUrl."<br>";
	$fullProductPageUrl = $singlePageUrl;
	$productSinglePage = file_get_html('rendered.html');
	$productName = $productSinglePage->find('h1')[0]->text();
	$productName = str_replace('Искусственный камень ', "", $productName);
	$productPriceBlock = $productSinglePage->find('.item_current_price')[0]->text();
	$prodPriceReplace = str_replace("руб", "", $productPriceBlock);
	$productPrice = str_replace('.', "", $prodPriceReplace);
	$productPrice = str_replace('м2', "", $productPrice);	
	$productPrice = str_replace(' ', "", $productPrice);	

	$productSpecificationTab = $productSinglePage->find('.properties')[0];
	$tbodys = $productSpecificationTab->find('ul');
	$prod_length = 0;  // attr 11
	$prod_width = 0;   // attr 10
	$prod_heigth = 0;  // attr 12
	$upakovka_length = 0; // attr 18
	$upakovka_width = 0; // attr 17
	$upakovka_heigth = 0; // attr 19
	$material = ''; // attr 20
	$strana = ''; // attr 14
	$color = ''; //attr 8
	$ugol = '';
	
		$trs = $tbodys[0]->find('li');
		foreach ($trs as $tr) {
			$tds = $tr->find('span');
			$td = $tr->find('strong');
			if (count($tds) > 0) {
				$name = trim($tds[0]->text());
			}
			if (count($td) > 0) {
				$value = trim($td[0]->text());
			}
			// echo $name.' - '.$value.'<br>';
			// var_dump($value);die;

			if ($name == 'Длина, см') {
				$prod_length = $value;
				$prod_length = str_replace('(', '', $prod_length);
				$prod_length = str_replace(')', '', $prod_length);
				$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_length'");
				if (strpos($prod_length, '±') > 0) {
					$splited_length = explode('±', $prod_length);
					$one = trim($splited_length[0]);
					if (strpos($one,'х') > 0) {
						$one_arr = explode('x', $one);
						$one = trim($one_arr[0]);
					}
					$two = trim($splited_length[1]);
					$two = str_replace('0', $one, $two);
					$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$one'");
					if (!isset($value_for_length[0]["value_id"])) {
						$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$two'");
					}
				}
			}
			if ($name == 'Высота, см') {
				$prod_width = $value;
				$prod_width = str_replace('(', '', $prod_width);
				$prod_width = str_replace(')', '', $prod_width);
				$value_for_width = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_width'");
				if (strpos($prod_width, '±') > 0) {
					$splited_width = explode('±', $prod_width);
					$one = trim($splited_width[0]);
					if (strpos($one,'х') > 0) {
						$one_arr = explode('x', $one);
						$one = trim($one_arr[0]);
					}
					$two = trim($splited_width[1]);
					$two = str_replace('0', $one, $two);
					$value_for_width = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$one'");
					if (!isset($value_for_width[0]["value_id"])) {
						$value_for_width = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$two'");
					}
				}
			}
			if ($name == 'Толщина') {
				$prod_heigth = $value;
				$prod_heigth = str_replace('(', '', $prod_heigth);
				$prod_heigth = str_replace(')', '', $prod_heigth);
				$value_for_heigth = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_heigth'");
				if (strpos($prod_heigth, '±') > 0) {
					$splited_length = explode('±', $prod_length);
					$one = trim($splited_length[0]);
					if (strpos($one,'х') > 0) {
						$one_arr = explode('x', $one);
						$one = trim($one_arr[0]);
					}
					$two = trim($splited_length[1]);
					$two = str_replace('0', $one, $two);
					$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$one'");
					if (!isset($value_for_length[0]["value_id"])) {
						$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$two'");
					}
				}
			}

			if ($name == 'Цвет') {
				$color = $value;
				$prod_color = $value;
				$value_for_color = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_color'");
			}

			if ($name == 'Угловой элемент') {
				$prod_ugol = $value;
				$ugol = $value;
				$value_for_ugol = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_ugol'");
			}
			if ($name == 'Страна') {
				$strana = $value;
				$prod_strana = $value;
				$value_for_strana = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_strana'");
			}
			
			$length_filter = 0;
			$width_filter = 0;
			$heigth_filter = 0;
			$color_filter = 0;
			$ugol_filter = 0;
			$strana_filter = 0;
			if (isset($value_for_length[0]["value_id"])) {
				$length_filter = (int)$value_for_length[0]["value_id"];
			}
			if (isset($value_for_width[0]["value_id"])) {
				$width_filter = (int)$value_for_width[0]["value_id"];
			}
			if (isset($value_for_color[0]["value_id"])) {
				$color_filter = (int)$value_for_color[0]["value_id"];
			}
			if (isset($value_for_ugol[0]["value_id"])) {
				$ugol_filter = (int)$value_for_ugol[0]["value_id"];
			}
			if (isset($value_for_strana[0]["value_id"])) {
				$strana_filter = (int)$value_for_strana[0]["value_id"];
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
			if ($color_filter != 0) {
				$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 5001, `value_id` = '$color_filter'");
			}
			if ($ugol_filter != 0) {
				$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10015, `value_id` = '$ugol_filter'");
			}
			if ($strana_filter != 0) {
				$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10014, `value_id` = '$strana_filter'");
			}
			if ($heigth_filter != 0) {
				$db->execute("INSERT INTO `oc_ocfilter_option_value_to_product` SET `product_id` = '$productCount', `option_id` = 10012, `value_id` = '$heigth_filter'");
			}			
		}
		echo "<hr>";
	$translateName = translit($productName);
	$lowerCaseProductName = strtolower($translateName);
	$directoryName = 'catalog/products/'.$lowerCaseProductName.'_'.$productCount;
	if(!is_dir($directoryName)){
		mkdir($directoryName, 0755);
	}
	$imgCount = 0;
	$imgBlock = $productSinglePage->find('.bx_bigimages_aligner')[0];
	$imgTag = $imgBlock->find('img');
	$mainImage = '';
	foreach ($imgTag as $imgSrc) {
		$imageSrc = $imgSrc->src;
		$imageSrc = 'https://www.idealstone.ru/'.$imageSrc;
		$imageName = $lowerCaseProductName.'_'.$imgCount;
		$output = $directoryName.'/'.$imageName.'.jpg';
		file_put_contents($output, file_get_contents($imageSrc));
		if ($imgCount == 0) {
			$mainImage = $output;
		}
		$db->execute("INSERT INTO `oc_product_image` SET `product_id` = '$productCount', `image` = '$output', `sort_order` = 0");
		$imgCount++;
	}
	$descriptionTab =  $productSinglePage->find('.bx_lt');
	$desc = '';
	if (!empty($descriptionTab[1]->text())) {
			$desc = $descriptionTab[1]->text();
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

	$db->execute("INSERT INTO `oc_product`  (	`product_id`,`model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `price`, `tax_class_id`, `date_available`, `weight_class_id`, `date_added`, `date_modified`, `length`, `width`, `height`, `status`, `subtract`, `length_class_id`,`shipping`) VALUES ('$productCount', '$prodSku', '', '','', '/м²', '', '', '', 1000, 9, '$prodMainImage', 2, '$prodPrice', 0, '2018-06-20', 1, '2019-07-18 00:31:11', '2019-07-18 00:31:14', '$upakovka_length', '$upakovka_width', '$upakovka_heigth', 1, 0, 1, 0);");
	if ($length_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 11, `language_id` = 1, `text` = '$prod_length'");
	}
	if ($width_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 10, `language_id` = 1, `text` = '$prod_width'");
	}
	if ($heigth_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 12, `language_id` = 1, `text` = '$prod_width'");
	}

	if ($color_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 8, `language_id` = 1, `text` = '$color'");
	}
	if ($material != '') {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 20, `language_id` = 1, `text` = '$material'");
	}
	if ($strana != '') {
	$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 14, `language_id` = 1, `text` = '$strana'");
	}
	if ($ugol != '') {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 15, `language_id` = 1, `text` = '$ugol'");
	}
	$db->execute("INSERT INTO `oc_product_description` SET `product_id` = '$productCount', `language_id` = 1, `name` = '$productName', `description` = '$desc', `meta_description` = '', `meta_keyword` = '', `meta_title` = '$productName', `meta_h1` = '', `tag` = '';");
	$db->execute("INSERT INTO `oc_product_to_category` SET `product_id` = '$productCount', `category_id` = 1, `main_category` = 1");
	$db->execute("INSERT INTO `oc_product_to_layout` SET `product_id` = '$productCount', `store_id` = 0, `layout_id` = 0");
	$db->execute("INSERT INTO `oc_product_to_store` SET `product_id` = '$productCount', `store_id` = 0");
	$productCount++;
}