<?php

session_start();

define("COLUMN_SIZE", 3);
define("TIME_COOKIE", 2592000);

require_once 'login.php'; //sql credentials

//when logged in, show landing pagefg
if (isset($_SESSION['id']) && isset($_SESSION['name'])){
    //sanitize
    $id = htmlentities($_SESSION['id']);
    $name = htmlentities($_SESSION['name']);

    // Display input form
    echo <<<_END
    <html>
    <head><title>CS174 Mid2 Homepage</title></head><body>
    
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

    //if file submitted, sanitize and add to table
    if (isset($_POST['submit']) && !empty($_POST['stname']) && !empty($_POST['sid'])) {
        //open db connection
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($hn,$un,$pw,$db);
        if($conn->connect_error) die (mysql_fatal_error());   

        //sanitize
        $stname = mysql_entities_fix_string($conn,$_POST['stname']);
        $sid = mysql_entities_fix_string($conn,$_POST['sid']);

        if(empty($stname) OR empty($sid)){
             echo "Both fields needed! Try again.<br>";
        }
        else {
            //query sql for advisor and print
            print_advisor($conn, $stname, $sid);
         }  
        
        //close all connections
        $result->close();
        $conn->close();
   }

    echo"</body></html>";


} 
else { 
    echo "Please <a href='signin.php'>Click Here</a> to log in.";
    exit();
}

//logout pressed
if(isset($_POST['logout'])){
    destroy_session_and_data();
    header("Location: signup.php");
}

//find 
function print_advisor($conn, $stname, $sid) {
     //find student
     $st_query = "SELECT * FROM cs174_hw6_credentials WHERE name = '$stname' AND studentID = '$sid'";
     $st_result = $conn->query($st_query);
     if (!$st_result) mysql_fatal_error();
     
     //proceed if student exists
     if($st_result->num_rows){
        $st_row = $st_result->fetch_array(MYSQLI_NUM);  

        $lastDigits = substr($st_row[2], -2); // Get last 2 digits
        
        //find correct advisor
        $ad_query = "SELECT * FROM cs174_hw6_advisors WHERE lowerID <= $lastDigits AND upperID >= $lastDigits";
        $ad_result = $conn->query($ad_query);
        if (!$ad_result) mysql_fatal_error();

        //print advisor information
        if($ad_result->num_rows){
            echo "<table><tr><th>Advisor Name</th><th>Email</th><th>Number</th></tr>";  
            $ad_row = $ad_result->fetch_Array(MYSQLI_NUM);
            echo "<tr>";
            //print out row
            for ($k = 0 ; $k < COLUMN_SIZE; ++$k){ echo "<td><br>$ad_row[$k]</td>"; } 
            echo "</tr>";
            echo "</table>";
        } else{
            echo "No advisor assigned for Student Name and ID combination. <br>";
        }
     }
     else {
        echo "Invalid Student Name and ID combination. <br>";
     }   
}

//sql error func
function mysql_fatal_error(){
    echo "Sorry, connection fail<br>";
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
    $string = stripslashes($string);
    return $conn->real_escape_string($string);
}

?>