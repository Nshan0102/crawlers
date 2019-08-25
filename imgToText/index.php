<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
<?php 

// include('pdfToText.php');
// $a = new PDF2Text();
// $a->setFilename('13.pdf'); 
// $a->decodePDF();
// echo $a->output(); 


// $text = file_get_contents('13.txt');
// $x = explode(' ', $text);
// echo($text);


//Initialise the cURL var
// $ch = curl_init();

// //Get the response from cURL
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// //Set the Url
// curl_setopt($ch, CURLOPT_URL, 'https://pdftotext.com/upload/iteiap313mz1l1fy');

// //Create a POST array with the file in it
// $postData = array(
// 	'name' => '003_0000000000.pdf',
// 	'id' => 'o_3dir2ndea1i0if41oid1cm5570s',
//     'file' => 'C:/OSPanel/domains/imgToText/003_0000000000.pdf',
// );
// curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

// // Execute the request
// $response = curl_exec($ch);

// print_r($response);
// PDF_show(fopen('C:/OSPanel/domains/imgToText/PDF32000_2008.pdf', 'rb'), 'text');
$file = 'PDF32000_2008.pdf';
$filename = 'PDF32000_2008.pdf';
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($file));
header('Accept-Ranges: bytes');
@readfile($file);

die;
$x = pdftotext('C:/OSPanel/domains/imgToText/003_0000000000.pdf');
var_dump($x);die();
 ?>

</body>
</html>