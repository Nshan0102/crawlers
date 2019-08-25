<?php
include_once('parser_configs.php');
include_once('simple_html_dom.php');
include_once('Phantom.php');
require_once('database.php');
$db = new Database;
$phantom = new Phantom;

function translit($str) {
	$rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я','№',' ');
	$lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'No','_');
	return str_replace($rus, $lat, $str);
}
function lengthWidthHeight($str){
	$splited = explode('х', $str);
	$l = trim(str_replace('см', '', $splited[0]));
	$l = (int)$l/10;
	$w = trim(str_replace('см', '', $splited[1]));
	$w = (int)$w/10;
	$h = trim(str_replace('см', '', $splited[2]));
	$h = (int)$h/10;
	$array= [$l,$w,$h];
	return $array;
}
$catalogUrl = 'https://www.rockp.ru/catalog/ravenna/';
$phantom->getContent($catalogUrl);
$html = file_get_html('rendered.html');
$productContainer = $html->find('.central-content')[0];
$productItems = $productContainer->find('.catalogue-image');
$productCount = 430;
foreach ($productItems as $items) {
	$anchor = $items->find('a');
	$singlePage = $anchor[0]->href;
	$singlePageUrl = 'https://www.rockp.ru'.$singlePage;
	$phantom->getContent($singlePageUrl);
	$fullProductPageUrl = $singlePageUrl;
	$productSinglePage = file_get_html('rendered.html');
	$productName = $productSinglePage->find('h1')[0]->text();
	$productName = trim($productName);
	$productPriceBlock = $productSinglePage->find('.catalogue-price')[0]->text();
	$prodPriceReplace = str_replace("руб", "", $productPriceBlock);
	$productPrice = str_replace('.', "", $prodPriceReplace);
	$productPrice = str_replace('м2', "", $productPrice);	
	$productPrice = str_replace(' ', "", $productPrice);	

	$productSpecificationTab = $productSinglePage->find('.passport-main-table')[0];
	$tbodys = $productSpecificationTab->find('tbody');
	$prod_length = 0;  // attr 11
	$prod_width = 0;   // attr 10
	$prod_heigth = 0;  // attr 12
	$upakovka_length = 0; // attr 18
	$upakovka_width = 0; // attr 17
	$upakovka_heigth = 0; // attr 19
	
	$trs = $tbodys[0]->find('tr');
	$tr = $trs[2];
	$tds = $tr->find('td');
	if (count($tds) > 0) {
		$name = trim($tds[0]->text());
	}
	// echo $name.' - '.$value.'<br>';
	// var_dump($value);die;

	
	$prod_lengths = (int)$tds[1]->text();
	$prod_length = $prod_lengths/10;
	$value_for_length = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_length'");

	
	$prod_widths = (int)$tds[2]->text();
	$prod_width = $prod_widths/10;
	$value_for_width = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_width'");
	
	
	
	$prod_heigths = (int)$tds[3]->text();
	$prod_heigth = $prod_heigths/10;
	$prod_heigth = str_replace(')', '', $prod_heigth);
	$value_for_heigth = $db->query(" SELECT `value_id` FROM `oc_ocfilter_option_value_description` WHERE `name` = '$prod_heigth'");
	
	$length_filter = 0;
	$width_filter = 0;
	$heigth_filter = 0;
	$upakovka_length = 0;
	$upakovka_width = 0;
	$upakovka_heigth = 0;

	if ($tds[7]->text() != null) {
		$upakovka_lwh = lengthWidthHeight($tds[7]->text());
		$upakovka_length = $upakovka_lwh[0];
		$upakovka_width = $upakovka_lwh[1];
		$upakovka_heigth = $upakovka_lwh[2];
	}

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

	$translateName = translit($productName);
	$lowerCaseProductName = strtolower($translateName);
	$directoryName = 'catalog/products/'.$lowerCaseProductName.'_'.$productCount;
	if(!is_dir($directoryName)){
		mkdir($directoryName, 0755);
	}
	$imgCount = 0;
	$imgBlock = $productSinglePage->find('.passport-main-image')[0];
	$imgTag = $imgBlock->find('img');
	$mainImage = '';
	foreach ($imgTag as $imgSrc) {
		$imageSrc = $imgSrc->src;
		$imageSrc = 'https://www.rockp.ru'.$imageSrc;
		$imageSrc = str_replace('&amp;', '&', $imageSrc);
		echo $imageSrc.'<br>';
		$imageName = $lowerCaseProductName.'_'.$imgCount;
		$output = $directoryName.'/'.$imageName.'.jpg';
		file_put_contents($output, file_get_contents($imageSrc));
		if ($imgCount == 0) {
			$mainImage = $output;
		}
		$db->execute("INSERT INTO `oc_product_image` SET `product_id` = '$productCount', `image` = '$output', `sort_order` = 0");
		$imgCount++;
	}
	$descriptionTab =  $productSinglePage->find('.passport-text');
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

	$db->execute("INSERT INTO `oc_product`  (	`product_id`,`model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `price`, `tax_class_id`, `date_available`, `weight_class_id`, `date_added`, `date_modified`, `length`, `width`, `height`, `status`, `subtract`, `length_class_id`,`shipping`) VALUES ('$productCount', '$prodSku', '', '','', '/м²', '', '', '', 1000, 9, '$prodMainImage', 5, '$prodPrice', 0, '2018-06-20', 1, '2019-07-18 00:31:11', '2019-07-18 00:31:14', '$upakovka_length', '$upakovka_width', '$upakovka_heigth', 1, 0, 1, 0);");
	if ($length_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 11, `language_id` = 1, `text` = '$prod_length'");
	}
	if ($width_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 10, `language_id` = 1, `text` = '$prod_width'");
	}
	if ($heigth_filter != 0) {
		$db->execute("INSERT INTO `oc_product_attribute` SET `product_id` = '$productCount', `attribute_id` = 12, `language_id` = 1, `text` = '$prod_width'");
	}
	$db->execute("INSERT INTO `oc_product_description` SET `product_id` = '$productCount', `language_id` = 1, `name` = '$productName', `description` = '$desc', `meta_description` = '', `meta_keyword` = '', `meta_title` = '$productName', `meta_h1` = '', `tag` = '';");
	$db->execute("INSERT INTO `oc_product_to_category` SET `product_id` = '$productCount', `category_id` = 1, `main_category` = 1");
	$db->execute("INSERT INTO `oc_product_to_layout` SET `product_id` = '$productCount', `store_id` = 0, `layout_id` = 0");
	$db->execute("INSERT INTO `oc_product_to_store` SET `product_id` = '$productCount', `store_id` = 0");
	$name = 'rendered.html';
    $w = fopen($name, "wb");
    fwrite($w,'');
    fclose($w);
	$productCount++;
}