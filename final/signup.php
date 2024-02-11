<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

define("INVALID_LOGIN", "Invalid username/password combination");
define("TIME_COOKIE", 2592000);

require_once 'db-login.php'; //sql credentials

    echo <<<_END
        <html>
        <head>
        <link rel="stylesheet" href="css/signup.css">
        <title>CS174 Final</title>
        <script>
        function validate(form) {
            fail = validateName(form.signup_name.value);
            fail += validateEmail(form.signup_email.value);
            fail += validatePassword(form.signup_pass.value);

            var errorDiv = document.getElementById('errorMessages');
            errorDiv.innerHTML = fail;

            if (fail == "") { return true; }
            else {  alert(fail); return false; }
        }
        function validateName(name) { 
            var regex = /^[a-zA-Z]+$/;
            if(regex.test(name)){ return ""; }
            return "Name format is incorrect, must be only letters!<br>";
        }           
        function validateEmail(email) {
            var regex = /^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if(regex.test(email)){ return ""; }
            return "Email format is incorrect!<br>";
        }       
        function validatePassword(password) {  
            if (password.length < 4) {
                return "Password must be at least 4 characters long!<br>";
            }
            return "";
        }
        </script>
        </head>  

        <body> 
        <h2>Random Quote Generator</h2>
        <h4>The fastest way to randomly get a quote!</h4>

        <table border="1" cellpadding="2" cellspacing="5" bgcolor="#4bc0c0">
        <th colspan="2" align="center">Login</th>
            <form method="POST" action="signup.php" enctype="multipart/form-data">
                <tr> <td>Username</td> <td><input type="text" maxlength="16" name="login_user"></td> </tr>
                <tr> <td>Password</td> <td><input type="text" maxlength="12" name="login_pass"></td> </tr>
                <tr> <td colspan="2" align="center"><input type="submit" name="submit_login" value="Submit"></td> </tr>
            </form>
        </table>

        <table border="1" cellspacing="5" bgcolor="#4bc0c0">
        <th colspan="2" align="center">Signup Form</th>
            <form method="post" action="signup.php" onsubmit="return validate(this)">
                <tr> <td>Name</td> <td><input type="text" maxlength="32" name="signup_name"></td> </tr>
                <tr> <td>Username</td> <td><input type="text" maxlength="16" name="signup_user"></td> </tr>
                <tr> <td>Email</td> <td><input type="text" maxlength="64" name="signup_email"></td> </tr>
                <tr> <td>Password</td> <td><input type="text" maxlength="12" name="signup_pass"></td> </tr>
                <tr> <td colspan="2" align="center"><input type="submit" name="submit_signup" value="Submit"> </td></tr>
            </form>
        </table>

        <div id="errorMessages"></div>

        </body>
        </html>
    _END;

    //if user attempted to sign up
    if(isset($_POST['submit_signup']) && !empty($_POST['signup_name']) && !empty($_POST['signup_user']) && !empty($_POST['signup_email'])  && !empty($_POST['signup_pass'])){
        //open db connection
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($hn,$un,$pw,$db);
        if($conn->connect_error) die (mysql_fatal_error());   

        //sanitize 
        $temp_name = mysql_entities_fix_string($conn, $_POST['signup_name']);  
        $temp_user = mysql_entities_fix_string($conn, $_POST['signup_user']);
        $temp_email = mysql_entities_fix_string($conn, $_POST['signup_email']);
        $temp_pass = mysql_entities_fix_string($conn, $_POST['signup_pass']);
             
        //make sure email is unique
        if(unique_user($conn, $temp_user, $temp_email)){
            $token = password_hash($temp_pass, PASSWORD_DEFAULT); //encrypt password
            add_user($conn, $temp_name, $temp_user, $temp_email, $token); //add new user
        } else {
            echo "Email taken; Use another email.<br>";
        }

        //close all connections
        //$result->close();
        $conn->close();   
    }
    
    //if user attempts to login
    if (isset($_POST['submit_login']) && !empty($_POST['login_user']) && !empty($_POST['login_pass'])) {
         //open db connection
         mysqli_report(MYSQLI_REPORT_OFF);
         $conn = @new mysqli($hn,$un,$pw,$db);
         if($conn->connect_error) die (mysql_fatal_error());    

        //sanitize 
        $temp_user = mysql_entities_fix_string($conn, $_POST['login_user']);
        $temp_pass = mysql_entities_fix_string($conn, $_POST['login_pass']);
             
    
        $query = "SELECT * FROM cs174_final_credentials WHERE username='$temp_user'";
        $result = $conn->query($query);
        
        if (!$result) { mysql_fatal_error();} 
        elseif ($result->num_rows){
            $row = $result->fetch_array(MYSQLI_NUM);
            
            //close connection
            $result->close();
            $conn->close();
           
            //verify password 
            if(password_verify($temp_pass, $row[4])){
                //set sessions
                $_SESSION['id'] = $row[0];
                $_SESSION['name'] = $row[1];

                //session regeneration
                if(!isset($_SESSION['initiated'])){
                    session_regenerate_id();
                    $_SESSION['initiated'] = 1;
                }   
                header("Location: main.php");
            } else{
                echo INVALID_LOGIN . "<br>";
            }            
        } else {
            echo INVALID_LOGIN . "<br>";
            //close connection
            $result->close();
            $conn->close();
        }
    }
    
    //check for unique email and studentID
    function unique_user($conn, $user, $email){
        $query = "SELECT * FROM cs174_final_credentials WHERE username='$user' OR email='$email' ";
        $result = $conn->query($query); 
        if (!$result) {mysql_fatal_error();} 
         
        if($result->num_rows == 0) {return true; } //if row has content, email is taken
       
        return false;
    }

    //add user
    function add_user($conn, $name, $user, $em, $pw){
        $query = "INSERT INTO cs174_final_credentials(name, username, email, password) VALUES('$name', '$user', '$em', '$pw')";
        $result = $conn->query($query);
        if (!$result) {mysql_fatal_error();}
        else { echo "Successfully signed up, $name! Proceed to login."; }
        
    }
    
    //sql error func
    function mysql_fatal_error(){
        echo "Sorry, connection fail<br>";
    }

    //sanitize input
    function mysql_entities_fix_string($conn,$string){
        return htmlentities(mysql_fix_string($conn,$string));
    }

    //sanitize input: helper func
    function mysql_fix_string($conn,$string){
       // $string = stripslashes($string);
        return $conn->real_escape_string($string);
    }

    //clear session
    function destroy_session_and_data(){
        $_SESSION = array();
        setcookie(session_name(), '', time() - TIME_COOKIE, '/');
        session_destroy();
    }

?>