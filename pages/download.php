<?php

//The directory where the download files are kept - keep outside of the web document root
$strDownloadFolder = "downloads/";

//If you can download a file more than once
$boolAllowMultipleDownload = 0;

include('../src/db.php');
global $con;

if(!empty($_GET['key'])){
    //check the DB for the key
    $key = $_GET['key'];
    $resCheck = mysqli_query($con, "SELECT * FROM downloads WHERE downloadkey = '".mysqli_real_escape_string($con, $key)."' LIMIT 1");
    $arrCheck = mysqli_fetch_assoc($resCheck);
    if(!empty($arrCheck['file'])){
        //check that the download time hasnt expired
        if($arrCheck['expires']>=time()){
            if(!$arrCheck['downloads'] OR $boolAllowMultipleDownload){
                //everything is hunky dory - check the file exists and then let the user download it
                $strDownload = $strDownloadFolder.$arrCheck['file'];
                echo $strDownload;
                if(file_exists($strDownload)){

                    header("Content-Type: application/octet-stream");

                    $file = $strDownload;
                    header("Content-Disposition: attachment; filename=" . urlencode($file));
                    header("Content-Type: application/octet-stream");
                    header("Content-Type: application/download");
                    header("Content-Description: File Transfer");
                    header("Content-Length: " . filesize($file));
                    flush(); // this doesn't really matter.
                    $fp = fopen($file, "r");
                    while (!feof($fp))
                    {
                        echo fread($fp, 65536);
                        flush(); // this is essential for large downloads
                    }
                    fclose($fp);

                    //update the DB to say this file has been downloaded
                    mysqli_query($con, "UPDATE downloads SET downloads = downloads + 1 WHERE downloadkey = '".mysqli_real_escape_string($con, $_GET['key'])."' LIMIT 1");

                    exit;

                }else{
                    echo "We couldn't find the file to download.";
                }
            }else{
                //this file has already been downloaded and multiple downloads are not allowed
                echo "This file has already been downloaded.";
            }
        }else{
            //this download has passed its expiry date
            echo "This download has expired.";
        }
    }else{
        //the download key given didnt match anything in the DB
        echo "No file was found to download.";
    }
}else{
    //No download key wa provided to this script
    echo "No download key was provided. Please return to the previous page and try again.";
}

?>