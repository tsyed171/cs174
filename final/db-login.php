<?php
    // db credentials
    $hn = 'localhost';
    $un = 'cs174_hw4_test';
    $pw = '1234';
    $db = 'test';

    // //sql connection
    // mysqli_report(MYSQLI_REPORT_OFF);
    // $db_conn = @new mysqli($hn,$un,$pw,$db);
    // if($db_conn->connect_error) die (mysql_fatal_error_db()); 

    // $db_query = "
    // CREATE DATABASE IF NOT EXISTS $db;
    // USE $db;

    // CREATE TABLE IF NOT EXISTS cs174_final_credentials (
    //     id INT PRIMARY KEY AUTO_INCREMENT,
    //     name VARCHAR(50),
    //     username VARCHAR(50),
    //     email VARCHAR(256),
    //     password VARCHAR(256)
    // );

    // CREATE TABLE IF NOT EXISTS cs174_final_content (
    //     id INT PRIMARY KEY AUTO_INCREMENT,
    //     user_id INT,
    //     question VARCHAR(10000)
    // );";

    // $db_result = $db_conn->multi_query($db_query);
    // if (!$db_result) { mysql_fatal_error_db();} 
    // else {
    //     // Close the result set
    //     while ($db_conn->more_results()) {
    //         $db_conn->next_result();
    //         $db_conn->store_result();
    //     }
    // }

    // //sql error func
    // function mysql_fatal_error_db(){
    //     echo "Sorry, connection fail<br>";
    // }

?>