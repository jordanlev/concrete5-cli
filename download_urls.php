<?php
//Available C5 versions for installation (note that 5.5.1 was the first version to allow CLI installation).
//
//First one in list becomes default option.
//
//"unzips_to" is the name of the folder inside the downloaded zip file
// (so far, they've always named this the word "concrete" followed by the version number,
//  but theoretically they could change it in the future).

$c5_versions = array(
	array(
		'number' => '5.6.2.1',
		'url' => 'http://www.concrete5.org/download_file/-/view/58379/8497/',
		'unzips_to' => 'concrete5.6.2.1',
	),
	// array(
	// 	'number' => '5.6.2',
	// 	'url' => 'http://www.concrete5.org/download_file/-/view/57877/8497/',
	// 	'unzips_to' => 'concrete5.6.2',
	// ),
	array(
		'number' => '5.6.1.2',
		'url' => 'http://www.concrete5.org/download_file/-/view/51635/8497/',
		'unzips_to' => 'concrete5.6.1.2',
	),
	// array(
	// 	'number' => '5.6.1.1',
	// 	'url' => 'http://www.concrete5.org/download_file/-/view/51531/8497/',
	// 	'unzips_to' => 'concrete5.6.1.1',
	// ),
	// array(
	// 	'number' => '5.6.1',
	// 	'url' => 'http://www.concrete5.org/download_file/-/view/49906/8497/',
	// 	'unzips_to' => 'concrete5.6.1',
	// ),
	array(
		'number' => '5.6.0.2',
		'url' => 'http://www.concrete5.org/download_file/-/view/44326/8497/',
		'unzips_to' => 'concrete5.6.0.2',
	),
	// array(
	// 	'number' => '5.6.0.1',
	// 	'url' => 'http://www.concrete5.org/download_file/-/view/43620/8497/',
	// 	'unzips_to' => 'concrete5.6.0.1',
	// ),
	// array(
	// 	'number' => '5.6.0',
	// 	'url' => 'http://www.concrete5.org/download_file/-/view/43239/8497/',
	// 	'unzips_to' => 'concrete5.6.0',
	// ),
	array(
		'number' => '5.5.2.1',
		'url' => 'http://www.concrete5.org/download_file/-/view/37862/8497/',
		'unzips_to' => 'concrete5.5.2.1',
	),
	// array(
	// 	'number' => '5.5.2',
	// 	'url' => 'http://www.concrete5.org/download_file/-/view/36984/8497/',
	// 	'unzips_to' => 'concrete5.5.2',
	// ),
	array(
		'number' => '5.5.1',
		'url' => 'http://www.concrete5.org/download_file/-/view/33453/8497/',
		'unzips_to' => 'concrete5.5.1',
	),
);
