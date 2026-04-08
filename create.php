<?php
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['submit'])){

    $user_id = $_SESSION['user_id']; // ✅ IMPORTANT
    $movie_title = $_POST['movie_title'];
    $genre = $_POST['genre'];
    $status = $_POST['status'];
    $rating = !empty($_POST['rating']) ? $_POST['rating'] : NULL;
    $date_added = $_POST['date_added'];

    $stmt = $conn->prepare("INSERT INTO movie_watchlist (user_id, movie_title, genre, status, rating, date_added) VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("isssis", $user_id, $movie_title, $genre, $status, $rating, $date_added);

    if($stmt->execute()){
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<h2>Add Movie</h2>

<form method="POST">

    Movie Title:
    <input type="text" name="movie_title" required><br><br>

    Genre:
    <input type="text" name="genre" required><br><br>

    Status:
    <select name="status">
        <option value="unwatched">Unwatched</option>
        <option value="watching">Watching</option>
        <option value="watched">Watched</option>
    </select><br><br>

    Rating:
    <input type="number" name="rating" min="1" max="10"><br><br>

    Date Added:
    <input type="date" name="date_added" required><br><br>

    <button type="submit" name="submit">Save</button>

</form>