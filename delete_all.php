<?php
include 'db.php';

$conn->query("DELETE FROM movie_watchlist");

header("Location:index.php");
?>
