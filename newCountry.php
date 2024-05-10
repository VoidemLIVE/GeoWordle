<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

function randomNum($min, $max) {
    return rand($min, $max);
}


$countriesFile = fopen("sorted_countries.json", "r") or die("Unable to open countries file! Error: ");
$countryData = fread($countriesFile, filesize("sorted_countries.json"));
fclose($countriesFile);
$countryDataArray = json_decode($countryData, true);

$randomCountryIndex = randomNum(0, 226);
$currentCountryData = $countryDataArray[$randomCountryIndex];
$currentCountryDataJson = json_encode($currentCountryData);

file_put_contents('currentCountry.json', $currentCountryDataJson);

$env = parse_ini_file('.env');
$sessFolder = $env["SESS_FOLDER"];
$files = scandir($sessFolder);
foreach ($files as $file) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    $filePath = $sessFolder . $file;
    unlink($filePath);
}

?>