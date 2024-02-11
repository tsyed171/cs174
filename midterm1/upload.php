<?php
//Author:Talia Syed

$line_size = 20;
$total_size = 400;
$greatest_product = 0;
$location1 = 1; 
$location2 = 2;
$location3 = 3;


echo <<<_END
    <html>
    <head><title></title></head><body>
    <form method="POST" action="upload.php" enctype="multipart/form-data"> 
    <h2>Cs174 Midterm 1: Upload File and Find Greatest 4 Integer Product</h2>
    <label> Select File </label>
    <p><input type="file" accept=".txt" name="filename" ></p>
    <p><input type="submit" value="Upload"></p>
    
    </form>
_END;

tester();

function tester(){
     //TEST 1 : GOOD
    $file1 = htmlentities(__DIR__ .'/cs174_m1_test1.txt');
    $fh1 = fopen($file1, 'r') or die("File does not exist or you lack permission to open it");
    $output1 = greatestProduct::find_greatest_product($fh1); 
    $expected1 = 64;            
    echo $expected1 == $output1 ? "Test 1 passed<br>" : "Test 1 Failed<br>";
    
    //TEST 2 : BAD
    $file2 = htmlentities(__DIR__ .'/cs174_m1_test2.txt');
    $fh2 = fopen($file2, 'r') or die("File does not exist or you lack permission to open it");
    $output2 = greatestProduct::find_greatest_product($fh2); 
    $expected2 = 64;            
    echo $expected2 == $output2 ? "Test 2 passed<br>" : "Test 2 Failed<br>";

    //TEST 3 : UGLY
    $file3 = htmlentities(__DIR__ .'/cs174_m1_test3.txt');
    $fh3 = fopen($file3, 'r') or die("File does not exist or you lack permission to open it");
    $output3 = greatestProduct::find_greatest_product($fh3); 
    $expected3 = 64;            
    echo $expected3 == $output3 ? "Test 3 passed<br>" : "Test 3 Failed<br>";
    
}

if ($_FILES) {
    $name = htmlentities($_FILES['filename']['tmp_name']);
   
    //check if file empty
    if( empty($name) || !is_uploaded_file($name)){
        die("File is empty! Please use file with values.");
    }
    //open file
    $fh = fopen($name, 'r') or die("File does not exist or you lack permission to open it");
    
    $output = greatestProduct::find_greatest_product($fh);
    echo "Greatest Product: " . $output . "<br>";

}

echo "</body></html>";

class greatestProduct{

    //
    private static function cmp_product($product){
        //compare input with global    
        if($product > $GLOBALS['grestest_product']){
            $GLOBALS['grestest_product'] = $product;
        }
    }

    //function to do row multiplication in right direction
    private static function row_operation($line, $i, $k){
       //declare all variables
        $row_prod = 0; 
        $var1 = 0;
        $var2 = 0;
        $var3 = 0;
        $var4 = 0;

        $index_next_three = $k + $GLOBALS['location3'];
        
        //test if the row operation can be done at current location 
        if($index_next_three < $GLOBALS['line_size']){
            $var1 = $line[$i + $k];
            $var2 = $line[$i + $k + $GLOBALS['location1']];
            $var3 = $line[$i + $k + $GLOBALS['location2']];
            $var4 = $line[$i + $k + $GLOBALS['location3']];
                    
            if(is_numeric($var1) == true AND is_numeric($var2) == true AND is_numeric($var3) == true AND is_numeric($var4) == true ){
                $row_prod = $var1 * $var2 * $var3 * $var4 ;
            }  
        }
       //compare local row product with global greatest product
       self::cmp_product($row_prod);
    }

    //function to do column multiplication
    private static function col_operation($line, $i, $k){
        $prod = 0;
        $var1 = 0;
        $var2 = 0;
        $var3 = 0;
        $var4 = 0;
        
        $index_down_three = ($i + ($GLOBALS['line_size'] * $GLOBALS['location3'])) / $GLOBALS['line_size'];
        
        //test if the col operation can be done at current location 
       if($index_down_three < $GLOBALS['line_size']){
            $var1 = $line[$i + $k];
            $var2 = $line[$i + $k + ($GLOBALS['location1'] * $GLOBALS['line_size'])];
            $var3 = $line[$i + $k + ($GLOBALS['location2'] *  $GLOBALS['line_size'])];
            $var4 = $line[$i + $k + ($GLOBALS['location3'] *  $GLOBALS['line_size'])];

            if(is_numeric($var1) == true AND is_numeric($var2) == true AND is_numeric($var3) == true AND is_numeric($var4) == true){
                $prod = $var1 * $var2 * $var3 * $var4;
            }
        }
        //compare local col product with global greatest product
        self::cmp_product($prod);
    }

    //function to do right diagonal operation
    private static function rt_diag_operation($line, $i, $k){
        $prod = 0; //temp product 
        $var1 = 0;
        $var2 = 0;
        $var3 = 0;
        $var4 = 0;

       $index_next_three = $k + $GLOBALS['location3'];
       $index_down_three = ($i + ($GLOBALS['line_size'] * $GLOBALS['location3'])) / $GLOBALS['line_size'];
        
        //test if the rt_diag operation can be done at current location 
        if($index_down_three < $GLOBALS['line_size'] AND $index_next_three < $GLOBALS['line_size']){
            $var1 = $line[$i + $k];      
            $var2 = $line[$i + $k + ($GLOBALS['location1'] * $GLOBALS['line_size']) + $GLOBALS['location1']];
            $var3 = $line[$i + $k + ($GLOBALS['location2'] *  $GLOBALS['line_size']) + $GLOBALS['location2']];
            $var4 = $line[$i + $k + ($GLOBALS['location3'] *  $GLOBALS['line_size']) + $GLOBALS['location3']];
                    
            if(is_numeric($var1) == true AND is_numeric($var2) == true AND is_numeric($var3) == true AND is_numeric($var4) == true ){
                $prod = $var1 * $var2 * $var3 * $var4;
            }
        }

        self::cmp_product($prod); //compare local rt_diag product with global greatest product
    }

    //function to do left diagonal operation
    private static function lt_diag_operation($line, $i, $k){
        $prod = 0;      
        $var1 = 0;
        $var2 = 0;
        $var3 = 0;
        $var4 = 0;

        $index_prev_three = $k - $GLOBALS['location3'];
        $index_down_three = ($i + ($GLOBALS['line_size'] * $GLOBALS['location3'])) / $GLOBALS['line_size'];
       
        //test if the rt_diag operation can be done at current location 
        if($index_down_three < $GLOBALS['line_size'] AND $index_prev_three >= 0){
            $var1 = $line[$i + $k];
            $var2 = $line[$i + $k + ($GLOBALS['location1'] * $GLOBALS['line_size']) - $GLOBALS['location1']];
            $var3 = $line[$i + $k + ($GLOBALS['location2'] *  $GLOBALS['line_size']) - $GLOBALS['location2']];
            $var4 = $line[$i + $k + ($GLOBALS['location3'] *  $GLOBALS['line_size']) - $GLOBALS['location3']];
                    
            if(is_numeric($var1) == true AND is_numeric($var2) == true AND is_numeric($var3) == true AND is_numeric($var4) == true){
                $prod = $var1 * $var2 * $var3 * $var4; 
            }
        }
        //compare local rt_diag product with global greatest product
        self::cmp_product($prod);
    }

    //check is input file is valid and sanitize
    private static function is_valid_file($fh){
        $line = fgets($fh); //get line
        $line = htmlentities($line); //sanitize line

        //check if file has 400 char (20 by 20 matrix)
        $file_size = strlen($line);
        
        //check if file too small
        if($file_size < $GLOBALS['total_size']){ 
            //print error
            echo "Error, your matrix size is too small! It's ok we will fill it in with 0's<br>";
            $line = str_pad($line, $GLOBALS['total_size'], "0", STR_PAD_RIGHT);
        }

        return $line;
    }
    
    // main function to run all directions
    public static function find_greatest_product($file){
        $line = self::is_valid_file($file);//check for valid file content & sanitize line
        fclose($file);//close file

        //i starts at array location 0 and increments to the next row; k goes across a row
        for ($i = 0; $i <= $GLOBALS['total_size'] - $GLOBALS['line_size']; $i = $i + $GLOBALS['line_size']){
            for ($k = 0; $k < $GLOBALS['line_size']; $k++){
                //row operation
                self::row_operation($line, $i, $k);       
                //col
                self::col_operation($line, $i, $k);
                //right diag
                self::rt_diag_operation($line, $i, $k);

                //left diag
               self::lt_diag_operation($line, $i, $k);          
            }
        }       
        return $GLOBALS['grestest_product'];
    }
}

?>