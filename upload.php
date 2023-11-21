<?php
//Author: Talia Syed

require_once 'login.php';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($hn,$un,$pw,$db);

if($conn->connect_error)
    die (mysql_fatal_error("Sorry, connection fail"));

echo <<<_END
    <html>
    <head><title>CS174 HW4</title></head><body>
    <form method="POST" action="upload.php" enctype="multipart/form-data"> 
    <h2>MySQL Insertion and Table Display </h2>

    <label for="filename"> Select File: </label>
    <input type="file" name="filename"><br><br>

    <label for="textname"> Create Title: </label>
    <input type="text" name="textname"><br>

    <p><input type="submit" name="submit" value="Submit"></p>
    
    </form>
_END;

if ($_FILES && isset($_POST['submit'])) {

     //file exists
    $name = htmlentities($_FILES['filename']['tmp_name']);   
     
    if(!is_uploaded_file($name) ){
        echo "No file uploaded! Please upload.";
    }
    else if(empty($name)){
        echo "File is empty! Please use file with values.";
    } 
    else {
        //restrict filetype to text
        $file_type = $_FILES['filename']['type'];
        if($file_type != 'text/plain'){
            //restrict to only text file using FILE
            echo "Only text files accepted! Try again.";
        }
        else {

            $title = mysql_entities_fix_string($conn,$_POST['textname']);
            if(empty($title)){
                echo "Title is necessary! Try again.<br>";
            }
            else {
                //open file to get line
                $fh = fopen($name, 'r') or die("File does not exist or you lack permission to open it");
                $line = htmlentities(fread($fh, filesize($name)));

                //add data into table; title and file line
                $query = "INSERT INTO cs174_hw4(title,text) VALUES ('$title','$line')";
                $result = $conn->query($query);
                if (!$result) {mysql_fatal_error("Sorry, connection fail");}

                fclose($fh); 
            }
             
        } 
    }
   
}

echo "</body></html>";

//print entire table
$query = "SELECT * FROM cs174_hw4";
$result = $conn->query($query);
if (!$result) {mysql_fatal_error("Sorry, connection fail");}

$rows = $result->num_rows;
echo "<table><tr><th>Title</th><th>File Content</th></tr>";
for ($j = 0 ; $j < $rows ; ++$j){
    $result->data_seek($j);
    $row = $result->fetch_array(MYSQLI_NUM);
    echo "<tr>";
    for ($k = 0 ; $k < 2 ; ++$k) echo "<td><br>$row[$k]</td>";
    echo "</tr>";
}
echo "</table>";

//close all connections
$result->close();
$conn->close();

//sql error func
function mysql_fatal_error($msg){
    echo $msg;
}

//sanitize input
function mysql_entities_fix_string($conn,$string){
    return htmlentities(mysql_fix_string($conn,$string));
}

//sanitize input: helper func
function mysql_fix_string($conn,$string){
    $string = stripslashes($string);
    return $conn->real_escape_string($string);
}

?>