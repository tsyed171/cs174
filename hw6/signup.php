<?php
session_start();

define("INVALID_LOGIN", "Invalid username/password combination");
define("TIME_COOKIE", 2592000);

require_once 'login.php'; //sql credentials

    echo <<<_END
        <html>
        <head>
        <title>CS174 Hw6</title>
        <script>
        function validate(form) {
            fail = validateName(form.signup_name.value);
            fail += validateID(form.signup_studentid.value);
            fail += validateEmail(form.signup_email.value);
            fail += validatePassword(form.signup_pass.value);

            var errorDiv = document.getElementById('errorMessages');
            errorDiv.innerHTML = fail;

            if (fail == "") { return true; }
            else {  alert(fail); return false; }
        }
        function validateName(name) { return ""; }       
        function validateID(id) {
            var regex = /^\d{8}$/;  // regular expression for 8 digits
            if (regex.test(id)) {  return ""; } 
            return "Student ID has to be 8 digits!<br>";
        }       
        function validateEmail(email) {
            var regex = /^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if(regex.test(email)){ return ""; }
            return "Email format is incorrect!<br>";
        }       
        function validatePassword(password) {  return ""; }
        </script>
        </head>  

        <body> 
        <h2>Hello!</h2>

        <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
        <th colspan="2" align="center">Login</th>
            <form method="POST" action="signup.php" enctype="multipart/form-data">
                <tr> <td>Student ID</td> <td><input type="text" maxlength="16" name="login_sid"></td> </tr>
                <tr> <td>Password</td> <td><input type="text" maxlength="12" name="login_pass"></td> </tr>
                <tr> <td colspan="2" align="center"><input type="submit" name="submit_login" value="Submit"></td> </tr>
            </form>
        </table>

        <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
        <th colspan="2" align="center">Signup Form</th>
            <form method="post" action="signup.php" onsubmit="return validate(this)">
                <tr> <td>Name</td> <td><input type="text" maxlength="32" name="signup_name"></td> </tr>
                <tr> <td>Student ID</td> <td><input type="text" maxlength="16" name="signup_studentid"></td> </tr>
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
    if(isset($_POST['submit_signup']) && !empty($_POST['signup_name']) && !empty($_POST['signup_studentid']) && !empty($_POST['signup_email'])  && !empty($_POST['signup_pass'])){
        //open db connection
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($hn,$un,$pw,$db);
        if($conn->connect_error) die (mysql_fatal_error(); );   

        //sanitize 
        $temp_name = mysql_entities_fix_string($conn, $_POST['signup_name']);  
        $temp_id = mysql_entities_fix_string($conn, $_POST['signup_studentid']);
        $temp_email = mysql_entities_fix_string($conn, $_POST['signup_email']);
        $temp_pass = mysql_entities_fix_string($conn, $_POST['signup_pass']);
             
        //make sure email is unique
        if(unique_user($conn, $temp_id, $temp_email)){
            $token = password_hash($temp_pass, PASSWORD_DEFAULT); //encrypt password
            add_user($conn, $temp_name, $temp_id, $temp_email, $token); //add new user
            echo "Successfully signed up, $temp_name! Proceed to login.";
        } else {
            echo "Email taken; Use another email.<br>";
        }

        //close all connections
        $result->close();
        $conn->close();   
    }
    
    //if user attempts to login
    if (isset($_POST['submit_login']) && !empty($_POST['login_sid']) && !empty($_POST['login_pass'])) {
         //open db connection
         mysqli_report(MYSQLI_REPORT_OFF);
         $conn = @new mysqli($hn,$un,$pw,$db);
         if($conn->connect_error) die (mysql_fatal_error());    

        //sanitize 
        $temp_sid = mysql_entities_fix_string($conn, $_POST['login_sid']);
        $temp_pass = mysql_entities_fix_string($conn, $_POST['login_pass']);
             
    
        $query = "SELECT * FROM cs152_project_credentials WHERE studentID='$temp_sid'";
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
    function unique_user($conn, $sid, $email){
        $query = "SELECT * FROM cs174_hw6_credentials WHERE email='$email' OR studentID='$sid'";
        $result = $conn->query($query); 
        if (!$result) {mysql_fatal_error();} 
         
        if($result->num_rows == 0) {return true;} //if row has content, email is taken
        
        return false;
    }

    //add user
    function add_user($conn, $name, $sid, $em, $pw){
        $query = "INSERT INTO cs174_hw6_credentials(name, studentID, email, password) VALUES('$name', $sid, '$em', '$pw')";
        $result = $conn->query($query);
        if (!$result) {mysql_fatal_error();}
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