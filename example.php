<?php
require_once('src/class.themoviedb.php');

//Themoviedb.org API KEY
$MovieDB = new TheMovieDb('API_KEY'); 
$MovieDB->settings('API_KEY'); //Optional
$MovieDB->search('El Camino:');

//Download Pictures
$MovieDB->fileDownload($MovieDB->Mov_images,'FOLDER_URL','FILE_NAME');

//All Database Listing
print_r($MovieDB->all());