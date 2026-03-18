<?php
include 'db.php';

$conn->query("DELETE FROM students");

header("Location:index.php");
?>
