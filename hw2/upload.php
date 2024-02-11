<?php

echo <<<_END
    <html>
    <head><title></title></head><body>
    <form method="POST" action="upload.php" enctype="multipart/form-data"> 
    <h2>Cs174 Hw2: Upload File and determine  if  given numbers are primes</h2>
    <label> Select File </label>
    <p><input type="file" name="filename" ></p>
    <p><input type="submit" value="Upload"></p>
    
    </form>
_END;

if ($_FILES) {
    $name = htmlentities($_FILES['filename']['tmp_name']);
   
    if( empty($name) || !is_uploaded_file($name)){
        die("File is empty! Please use file with values.");
    }

   // echo "Uploaded file:  <br>";

    $fh = fopen($name, 'r') or die("File does not exist or you lack permission to open it");
    
    $str4 = Prime::tester_function($fh);
    echo print_r($str4);
    // "<br>haha";
   
}

echo "</body></html>";


class Prime{   

    //check if the two inputs are valid
    public static function is_valid_input($var1, $var2){
        $proof = "";
        $result = "Not valid input. Proof: One or more values is a ";

        //check invalid input types: string/character, boolean
        if (is_numeric($var1) !== true OR is_numeric($var2) !== true ) {
        	// proof 1: non-numerical string or char value
        	if(is_string($var1) == true OR is_string($var2) == true ) { 
            	$proof = "non-numerical string/character"; 
            }
            // proof 2: boolean value
            else if(is_bool($var1) == true OR is_bool($var2) == true ) {  
            	$proof = "boolean value"; 
            }
    	}
        //checks invalid number types: 0, negative, decimal
        else {
            // proof 3: 0 value
     		if($var1 == 0 OR $var2 == 0){  
            	$proof = "0 value"; 
            }
            // proof 4: negative value
            else if($var1 < 0 OR $var2 < 0) { 
            	$proof = "negative value";
            }
            // proof 5: decimal value
        	else if (fmod($var1, 1) != 0.0 OR fmod($var2, 1) != 0.0 ) { 	
            	$proof = "decimal value";
            }
            // proof 0: base case; all valid numbers
        	else{   
            	$result = 0;  
                return $result;
            }
    	}

        return $result . $proof;
     }

    //find divisors of var1 and var2
    public static function find_divisor_count($var){
    	$arr; // array
    	$ctr = 0; //counter
        
        //find divisors of var
    	for ($i = 1; $i <= (int)sqrt($var); $i++)
    	{
        	//check for mod of two divided values to be 0
        	if ($var % $i == 0){
                //add first divisor
                $arr[$ctr++] = (int)$i;
                
            	// only add second divisor if different
            	if ((int)$var / $i != $i){
                    $arr[$ctr++] = (int)$var / $i;
                }
        	}
    	}
        
        return $ctr; // number of divisors
    }

    //check if a number is prime
    public static function is_prime($input){
        $result = "false";
        $count = "";
        
        define("constant", 2); // only 2 values to be prime (1 and itself)
        $count = self::find_divisor_count($input); //find divisor count
   
         //if one divisor, then prime 
         if ($count == constant){
             $result = "true"; 
         }   
           
         return $result;
    }

    //change name to primes_in_range
    public static function find_prime($fh){
        
        $result = array(); //final result of all lines
        $line_num = 1; //line number

         //get every line until end of file
         while (!feof($fh)){
             $line = fgets($fh); //get line
             $line = htmlentities($line); //sanitize line
            
             $line = trim($line); //trim extra spaces before and after line

             $ctr = 0;//line counter
             $space_reached = "false"; //indicator for variable
             $var_one = "";
             $var_two = "";
        
             //extract two inputs from line
             while($line[$ctr] != NULL){
                 //check if space reached
                 if($line[$ctr] == " "){
                     $space_reached = "true";
                 }
                 //add values to respective variables
                 if($space_reached == "false"){
                     $var_one = $var_one . $line[$ctr];
                 }
                 else {
                     $var_two = $var_two . $line[$ctr];
                 }
                $ctr++;
             }
         
             //check if inputs are valid integer inputs
             $valid_input = self::is_valid_input($var_one, $var_two);
             
             //print if invalid input
             if($valid_input !== 0 ){
                $temp = "Line " . $line_num . ": " . $valid_input;
                array_push($result, $temp) ;
             }
              //continue with valid input
             if($valid_input == 0){
                 $prime_arr = "Line " . $line_num . ":";
                 $curr = ""; //placeholder
                 
                 //start prime code here
                 for($i = $var_one; $i < $var_two; $i++){
                     
                     $curr = self::is_prime($i);
                     
                     if($curr == "true"){
                         //add $i to prime array
                         $prime_arr = $prime_arr . " "  . $i . ",";  
                     }
                 }
                 array_push($result, $prime_arr);
             }

            $line_num++;
             
        }
    
        //echo "<br> hello <br>";
        //close file at end
        fclose($fh);

        return $result;
    }

    //tester function
    //hard coded to my own file
    //Line 1: 3 10
    //Line 2: 1 20
    //Line 3: -7 10
    //Line 4: a b
    public static function tester_function($fh){
        $line_one = "Line 1: 3, 5, 7,";
        $line_two = "Line 2: 2, 3, 5, 7, 11, 13, 17, 19,";
        $line_three = "Line 3: Not valid input. Proof: One or more values is a negative value";
        $line_four = "Line 4: Not valid input. Proof: One or more values is a non-numerical string/character";
            
        $arr = self::find_prime($fh); //array to get result from main function
        $ctr = 1; //ctr for line

        //check output from function and hardcoded answer are same
        for($i = 0; $i < 4; $i++){
            
            $string_res = "Test " . $ctr++ . " passed! <br>";
            echo $arr[$i] . "<br>";

            if($i == 0 AND $line_one == $arr[$i]){
                echo $string_res;
            }
            else if ($i == 1 AND $line_two == $arr[$i]){
                echo $string_res;
            }
            else if ($i == 2 AND $line_three == $arr[$i]){
                echo $string_res;
            }
            else if ($i == 3 AND $line_four == $arr[$i]){
                echo $string_res;
            } 
        }
    }
       
}

?>