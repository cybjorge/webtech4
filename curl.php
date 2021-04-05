<?php
/*errors*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*errors*/

    require "configs/configdb.php";

    $headers=get_headers("https://github.com/cybjorge/webtech4/tree/master/data");
    $conn=connect();
    $newcommit=last_commit_in_db($headers[5],$conn);

if($newcommit==1){
        $query="INSERT INTO lastcommit (etag) VALUES ('$headers[5]')";
        $result = mysqli_query($conn, $query);
        echo "bol novy commit \n";
        //nova tabulka atd
    }
else{
    echo "nebol novy \n";
}


//funkcie
$lessons=[];

function string_between_two_string($str, $starting_word, $ending_word)
{
    $subtring_start = strpos($str, $starting_word);
    //Adding the strating index of the strating word to
    //its length would give its ending index
    $subtring_start += strlen($starting_word);
    //Length of our required sub string
    $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
    // Return the substring from the index substring_start of length size
    return substr($str, $subtring_start, $size);
}
function save_to_sql($url,$database,$loop,$tablename){
    $lessons=[];
    echo"------";
    echo  $tablename;
    echo"--echo adresy kde hladam--";

    //get raw data from github
    $crl=curl_init();
    curl_setopt($crl,CURLOPT_URL,$url);
    curl_setopt($crl,CURLOPT_HEADER,0);
    curl_setopt($crl,CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($crl);
    curl_close($crl);

    //encode as UTF-16LE;
    $output=mb_convert_encoding($output,'UTF-8','UTF-16LE');



    //extract rows from raw data
    $rows=explode(PHP_EOL,$output);

    //generate table in db
    $lessons[]="prednaska.'$tablename'";
    $command="CREATE TABLE prednaska".$tablename."(
                                id INT(6) PRIMARY KEY,
                                name VARCHAR(100) NOT NULL,
                                useraction VARCHAR(16) NOT NULL,
                                timestp VARCHAR(100) NOT NULL)";
    if(mysqli_query($database,$command)){
        echo "Table ".$tablename." was created";
    }

    foreach ($rows as $index=>$row){
        //extract values from rows
        $inputline=str_getcsv($row,"\t");
        //initialize table
        if($index>0 && sizeof($inputline)>1){
            $name=$inputline[0];
            $action=$inputline[1];
            $timestamp=date('d-m-Y, H:i:s',date_create_from_format('d/m/Y, H:i:s',$inputline[2])->getTimestamp()) ;

            $pushquery="INSERT INTO prednaska".$tablename." (id,name,useraction,timestp) VALUES ('$loop','$name','$action','$timestamp')";
            if(mysqli_query($database,$pushquery)){
                echo "Table ".$tablename." was initialized succesfully";
            }
        }
        echo $index;

    }
}

function findall($database){
    $names=[];
    //echo "zacinam find all \n";

    $curl=curl_init();
    $home="https://github.com/";
    $raw="https://raw.githubusercontent.com";
    curl_setopt($curl, CURLOPT_URL,$home."/cybjorge/webtech4/tree/master/data");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    $html=curl_exec($curl);
    curl_close($curl);

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_use_internal_errors(false);


    $selector = new DOMXPath($doc);
    $result=$doc->getElementsByTagName('a');


    $loop=1;
    echo "zacinam find loop \n";
    foreach($result as $node) {
        $url = $node->getAttribute('href');
        $url = str_replace("/blob", "", $url);

        if (str_ends_with($url, ".csv")) {
            try {
                $nameoflesson=string_between_two_string($url, "data/", "_AttendanceList");
                $names[]="prednaska".$nameoflesson;
                save_to_sql($raw . $url, $database, $loop,$nameoflesson);
                $loop = $loop + 1;
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";;
            }

        }

    };
    return $names;
}


