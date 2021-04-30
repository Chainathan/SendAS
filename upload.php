<?php

if (isset($_SERVER['HTTP_USER_AGENT'])
        && preg_match('/bot|curl|wget|crawl|slurp|spider|mediapartners/i',
        $_SERVER['HTTP_USER_AGENT']) )
    die("Detected as a bot. This site is not for bots.");

//Including the encryted php
include 'file_encryptor.php';
include 'db_connect.php';

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;

// Check if file already exists
if (file_exists($target_file)) {
  echo "Sorry, file already exists.";
  $uploadOk = 0;
}

$fname = $_FILES["fileToUpload"]["name"];
$sql="SELECT * FROM file_details where file_name='$fname'";
$result = mysqli_query($conn, $sql);
$num = mysqli_num_rows($result);
if ($num) {
  echo "Sorry, file already exists.";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, a file with same already exists.";
  // if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    //Encrypting file and decrypting it.
    encryptFile($target_file,$dKey);
    //deleting plain file
    unlink($target_file);
    echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";

  } else {
    echo "Sorry, there was an error uploading your file.";
    $uploadOk =0;
  }
}
echo "<br>";
// Random key generator
function random_str(
  int $length = 64,
  string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
  if ($length < 1) {
      throw new RangeException("Length must be a positive integer");
  }
  $pieces = [];
  $max = mb_strlen($keyspace, '8bit') - 1;
  for ($i = 0; $i < $length; ++$i) {
      $pieces []= $keyspace[random_int(0, $max)];
  }
  return implode('', $pieces);
}

// Function to insert file details and key
function insert_details(string $filename, string $datetime, string $key, object $conn){
  $sql="INSERT INTO file_details(file_name,key_file,inserted_time) VALUES ('$filename','$key','$datetime')";
  $result = mysqli_query($conn, $sql);

  // Check for the database creation success
  if(!$result){
    echo "The insertion of the values failed because of this error ---> ". mysqli_error($conn);
    return false;
  }
  return true;
}

// Function to check for duplicate key
function checkKey(string $key, object $conn){
  $sql="SELECT * FROM file_details where key_file='$key'";
  $result = mysqli_query($conn, $sql);
  $num=mysqli_num_rows($result);
  if($num>=1)
    return false;
  return true;
}

// Update database if the file was uploaded successfully
if($uploadOk != 0){

  $filename = htmlspecialchars( basename( $_FILES["fileToUpload"]["name"]));
  $datetime = time();
  do{
  $key = random_str(6);
  }while(!checkKey($key, $conn));

  if(insert_details($filename, $datetime, $key, $conn)){
    echo "<br>Your Key for the file: ".$key;
    echo "<br> <a href='/download.php?key=$key'> Download link </a>";
  }
}
echo '<br><a href="index.html">Go back</a>';
?>

