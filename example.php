<?php
require_once('src/class.themoviedb.php');

//Themoviedb.org API KEY
$MovieDB = new TheMovieDb('API_KEY');

//It is used to change the API Key later.
$MovieDB->settings('API_KEY'); //Optional

//Movie / Series Search
$Search = $MovieDB->search('El Camino:','en'); //When this field is left blank, it brings Turkish data by default.

//Error Status
if (isset(($Search->status))) {
//Print error messages
	echo json_encode($Search);
}

//Download Pictures
$MovieDB->fileDownload($MovieDB->Mov_images, 'FOLDER_URL', 'FILE_NAME');

//All Database Listing
print_r($MovieDB->all());

?>