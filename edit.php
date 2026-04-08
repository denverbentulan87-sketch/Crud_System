<?php
include 'db.php';

$id = $_GET['id'];

$result = $conn->query("SELECT * FROM movie_watchlist WHERE id=$id");
$row = $result->fetch_assoc();

if(isset($_POST['update'])){

$movie_title = $_POST['movie_title'];
$genre = $_POST['genre'];
$status = $_POST['status'];
$rating = $_POST['rating'];
$date_added = $_POST['date_added'];

$sql = "UPDATE movie_watchlist
        SET movie_title='$movie_title', genre='$genre', status='$status',rating=$rating,date_added=$date_added
        WHERE id=$id";

$conn->query($sql);

header("Location: index.php");
}
?>

<h2>Edit Movie Watchlist</h2>

<form method="POST">

    Movie_Title:
    <input type="text" name="movie_title" required>
    <br><br>

    Genre:
    <input type="text" name="genre" required>
    <br><br>

    Status:
    <input type="text" name="status" required>
    <br><br>

    Rating:
    <input type=number name=rating required>
    <br><br>

    Date_Added:
    <input type=number name=number required>
    <br><br>


    <button type="submit" name="update">Update</button>

</form>