<?php
session_start();

define("TIME_COOKIE", 2592000);
    
require_once 'db-login.php'; //sql credentials
//<p><input type="submit" name="question" value="Generate Random Quote!"></p>


mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($hn,$un,$pw,$db);
if($conn->connect_error) die (mysql_fatal_error()); 

//when logged in, show landing page
if (isset($_SESSION['name']) && isset($_SESSION['id'])){
    //sanitize
    $id = mysql_entities_fix_string($conn, $_SESSION['id']);
    $name = mysql_entities_fix_string($conn, $_SESSION['name']);

    // Display input form
    echo <<<_END
    <html>
    <head><title>CS174 Final</title></head><body>
    
    <form method="POST" action="signup.php" enctype="multipart/form-data"> 
    <p><input type="submit" name="logout" value="Log Out"></p>
    </form>

    <form method="POST" action="main.php" enctype="multipart/form-data"> 
    <h3>Hello, $name!</h3>

    <label for="filename"> Select File: </label>
    <input type="file" name="filename"><br>

    <p><input type="submit" name="submit" value="Submit"></p>

    <p><input type="submit" name="question" value="Generate Question!"></p>
    
    </form>
    _END;

//     //if file submitted, sanitize and add to table
//     if ($_FILES && isset($_POST['submit'])) {
//        //open db access
//         // mysqli_report(MYSQLI_REPORT_OFF);
//         // $conn = @new mysqli($hn,$un,$pw,$db);
//         // if($conn->connect_error) die (mysql_fatal_error()); 

//         //file exists
//        $name = mysql_entities_fix_string($_FILES['filename']['tmp_name']);
               
//        if(!is_uploaded_file($name) ){
//            echo "No file uploaded! Please upload.";
//        }
//        else if(empty($name)){
//            echo "File is empty! Please use file with values.";
//        } 
//        else {
//            //restrict filetype to text
//            $file_type = $_FILES['filename']['type'];
//            if($file_type != 'text/plain'){
//                //restrict to only text file using FILE
//                echo "Only text files accepted! Try again.";
//            }
//            else {
//                 //open file to get line
//                 $fh = fopen($name, 'r') or die("File does not exist or you lack permission to open it");
//                 //$line = mysql_entities_fix_string(fread($fh, filesize($name)));
//                 //echo $line;
//                 read_file($conn, $id, $fh); // read every question into database
//                 fclose($fh);   
//            } 
//        }
      
//    }

   if ($_FILES && isset($_POST['submit'])) {

    //file exists
   $name = mysql_entities_fix_string($conn, $_FILES['filename']['tmp_name']);   
    
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
               //open file to get line
               $fh = fopen($name, 'r') or die("File does not exist or you lack permission to open it");

               read_file($conn, $id, $fh);
               fclose($fh); 
            
       } 
   }
  
}
echo"</body></html>";
   
   if(isset($_POST['question'])){ print_content($conn, $id); }
  

    //close all connections
   // $result->close();
    $conn->close();

} 
else { 
    echo "Please <a href='signup.php'>Click Here</a> to log in.";
    exit();
}

//logout pressed
if(isset($_POST['logout'])){
    destroy_session_and_data();
    header("Location: signup.php");
}

function read_file($conn, $id, $fh){
     //get every line until end of file
     while (!feof($fh)){
        $line = fgets($fh); //get line
        $line = mysql_entities_fix_string($conn, $line); //sanitize line
        $line = trim($line); //trim extra spaces before and after line
       
        if(!empty($line)){
            //add data into table
            $query = "INSERT INTO cs174_final_content(user_id, question) VALUES ('$id', '$line')";
            $result = $conn->query($query);
            if (!$result) {mysql_fatal_error();}   
        }  
    }
    echo "Success, questions added!";   

}


function print_content($conn, $id) {
     //make query
     $query = "SELECT * FROM cs174_final_content WHERE user_id = '$id'";
     $result = $conn->query($query);
     if (!$result) {mysql_fatal_error();}
 
     $rows = $result->num_rows - 1; //get number of questions

     //randomly choose a number
     $rand_num = mt_rand(0, $rows);

     //print out question
     echo "<table><tr><th>Mystery Question!</th></tr>";   
         $result->data_seek($rand_num);
         $row = $result->fetch_array(MYSQLI_NUM);  
         echo "<tr><td><br>$row[2]</td></tr>";
     echo "</table>";
     
}

function tester($conn, $id){

}

//clear session
function destroy_session_and_data() {
    $_SESSION = array();
    setcookie(session_name(), '', time() - TIME_COOKIE, '/');
    session_destroy();
}

//sanitize input
function mysql_entities_fix_string($conn,$string){
    return htmlentities(mysql_fix_string($conn,$string));
}

//sanitize input: helper func
function mysql_fix_string($conn,$string){
   //$string = stripslashes($string);
    return $conn->real_escape_string($string);
}

?>