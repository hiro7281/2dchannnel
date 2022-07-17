<!DOCTYPE html>
<?php
	foreach($_POST as $key => $value){
		echo $key;
		echo ": ";
		echo $value;
		echo "<br>";
	}
?>