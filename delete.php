<?php
include 'db.php';

$id = $_GET['id'];

$conn->query("DELETE FROM movie_watchlist WHERE id=$id");

header("Location: index.php");

?>