<?php
//Author: Talia Syed

//constant for cookie time
define("COOKIE_TIME", 60 * 60 * 24 * 7 );
define("INVALID_LOGIN", "Invalid username/password combination");

require_once 'login.php';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($hn,$un,$pw,$db);

if($conn->connect_error)
    die (mysql_fatal_error());

echo <<<_END
    <html>
    <head>
    <title>CS174 HW4</title>
    </head><body>
    
    <form method="POST" action="upload.php" enctype="multipart/form-data"> 
    <h2>Hello!</h2>

    <h3>Login</h3>

    <label for="login_user"> Username: </label>
    <input type="text" name="login_user"><br><br>

    <label for="login_pass"> Password: </label>
    <input type="text" name="login_pass"><br>

    <p><input type="submit" name="submit_login" value="Submit"></p>

    <h3>Sign Up</h3>

    <label for="signup_name"> Name: </label>
    <input type="text" name="signup_name"><br><br>
    
    <label for="signup_user"> Username: </label>
    <input type="text" name="signup_user"><br><br>
    
    <label for="signup_pass"> Password: </label>
    <input type="text" name="signup_pass"><br>
    
    <p><input type="submit" name="submit_signup" value="Submit"></p>
    
    </form>
_END;


//if user attempted to sign up
if(isset($_POST['submit_signup']) && !empty($_POST['signup_name']) && !empty($_POST['signup_user']) && $_POST['signup_pass']){
    //sanitize 
    $temp_name = mysql_entities_fix_string($conn, $_POST['signup_name']);  
    $temp_user = mysql_entities_fix_string($conn, $_POST['signup_user']);
    $temp_pass = mysql_entities_fix_string($conn, $_POST['signup_pass']);

    //encrypt password
    $token = password_hash($temp_pass, PASSWORD_DEFAULT);

    add_user($conn, $temp_name, $temp_user, $token);

   
}

//if user attempts to login
if (isset($_POST['submit_login']) && !empty($_POST['login_user']) && !empty($_POST['login_pass'])) {

    //sanitize
    $temp_user = mysql_entities_fix_string($conn, $_POST['login_user']);
    $temp_pass = mysql_entities_fix_string($conn, $_POST['login_pass']);

    $query = "SELECT * FROM cs174_hw5_credentials WHERE user='$temp_user'";
    $result = $conn->query($query);
    
    if (!$result) {
        mysql_fatal_error();
    } 
    elseif ($result->num_rows){
        $row = $result->fetch_array(MYSQLI_NUM);
        $result->close();
       
        //verify password 
        if(password_verify($temp_pass, $row[3])){
            //set cookies and sanitize
            setcookie('name', $row[1], time() + COOKIE_TIME, '/');
            setcookie('id',$row[0],time() + COOKIE_TIME, '/' );
            header("Location: " . $_SERVER['REQUEST_URI']);
        } else{
            echo INVALID_LOGIN . "<br>";
        }            
    } else {
        echo "hello";
        echo INVALID_LOGIN . "<br>";
    }


}

//when successfully logged in
if (isset($_COOKIE['name']) && isset($_COOKIE['id'])){
    //sanitize
    $user_id = mysql_entities_fix_string($conn, $_COOKIE['id']);
    $name = mysql_entities_fix_string($conn, $_COOKIE['name']);
    echo "Hello, $name!<br>";

    // Display comment input form
    echo <<<_END
      <form method="POST" action="upload.php">
          <label for="comment"> Comment: </label>
          <input type="text" name="comment"><br>

          <p><input type="submit" name="submit_comment" value="Submit"></p>
      </form>
    _END;

    //sanitize comment and add to table
    if(isset($_POST['submit_comment']) && !empty($_POST['comment'])){    
        $comment = mysql_entities_fix_string($conn, $_POST['comment']);
        
        $query = "INSERT INTO cs174_hw5_comment VALUES ('$user_id','$comment')";
        $result = $conn->query($query);
        if (!$result) {mysql_fatal_error();}
    }

    //print all comments
    $query = "SELECT comment FROM cs174_hw5_comment WHERE user_id = '$user_id'";
    $result = $conn->query($query);
    if (!$result) {mysql_fatal_error();}

    $rows = $result->num_rows;
    echo "<table><tr><th>Comments</th></tr>";
    for ($j = 0 ; $j < $rows ; ++$j){
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_NUM);
        echo "<tr>";
        for ($k = 0 ; $k < 1 ; ++$k) echo "<td><br>$row[$k]</td>";
        echo "</tr>";
    }
    echo "</table>";

    //close all connections
    $result->close();
    $conn->close();
}

echo "</body></html>";

//add user
function add_user($conn, $name, $un, $pw){
    $query = "INSERT INTO cs174_hw5_credentials(name, user, pass) VALUES('$name', '$un', '$pw')";
    $result = $conn->query($query);
    if (!$result) {mysql_fatal_error();}
}

//sql error func
function mysql_fatal_error(){
    echo "Sorry, connection fail";
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