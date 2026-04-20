<?php 
include 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ADD MOVIE */
if(isset($_POST['add_movie'])){
    $stmt = $conn->prepare("INSERT INTO movie_watchlist (user_id,movie_title,genre,status,rating,date_added) VALUES (?,?,?,?,?,NOW())");
    $stmt->bind_param("isssi", $user_id, $_POST['movie_title'], $_POST['genre'], $_POST['status'], $_POST['rating']);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

/* EDIT MOVIE */
if(isset($_POST['edit_movie'])){
    $stmt = $conn->prepare("UPDATE movie_watchlist SET movie_title=?, genre=?, status=?, rating=? WHERE watchlist_id=? AND user_id=?");
    $stmt->bind_param("sssiii", $_POST['movie_title'], $_POST['genre'], $_POST['status'], $_POST['rating'], $_POST['id'], $user_id);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// SEARCH + FILTER + SORT (UNCHANGED)
$search = $_GET['search'] ?? "";
$status_filter = $_GET['status'] ?? "";
$sort = $_GET['sort'] ?? "recent";

$query = "SELECT * FROM movie_watchlist WHERE user_id=?";
$params = [$user_id];
$types = "i";

if (!empty($search)) {
    $query .= " AND movie_title LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if (!empty($status_filter)) {
    $query .= " AND status=?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($sort == "rating") {
    $query .= " ORDER BY rating DESC";
} elseif ($sort == "title") {
    $query .= " ORDER BY movie_title ASC";
} else {
    $query .= " ORDER BY date_added DESC";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// STATS (UNCHANGED)
$total = 0;
$watched = 0;
$rating_sum = 0;
$rating_count = 0;

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total++;

    if ($row['status'] == 'watched') $watched++;

    if (!empty($row['rating'])) {
        $rating_sum += $row['rating'];
        $rating_count++;
    }
}

$avg = $rating_count ? round($rating_sum / $rating_count, 1) : "—";
?>

<!DOCTYPE html>
<html>
<head>
<title>My Watchlist</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans&display=swap" rel="stylesheet">

<style>
/* YOUR DESIGN (UNCHANGED) */
* { margin:0; padding:0; box-sizing:border-box; }

body {
    background:#0b0b10;
    color:#eee;
    font-family:'DM Sans';
}

.app {
    max-width:1100px;
    margin:auto;
    padding:30px 15px;
}

.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header-left {
    display:flex;
    flex-direction:column;
    gap:4px;
}

.logo {
    color:#c8a96e;
    font-size:12px;
    letter-spacing:2px;
}

h1 {
    font-family:'Syne';
    font-size:42px;
}

.btn {
    background:#c8a96e;
    color:black;
    padding:10px 18px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
}

.stats {
    display:flex;
    gap:10px;
    margin:20px 0;
}

.stat {
    flex:1;
    background:#14141c;
    padding:18px;
    border-radius:12px;
    text-align:center;
}

.stat h2 { color:#c8a96e; }

.toolbar {
    display:flex;
    gap:8px;
    margin-bottom:15px;
}

.toolbar input,
.toolbar select {
    padding:10px;
    border-radius:10px;
    border:none;
    background:#1a1a24;
    color:white;
}

.toolbar input { flex:1; }

.card {
    background:#14141c;
    padding:18px;
    border-radius:12px;
    margin-bottom:12px;
    display:flex;
    justify-content:space-between;
}

.genre {
    background:#222;
    padding:3px 10px;
    border-radius:20px;
    font-size:12px;
}

.badge {
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
}

.badge-watched { background:green; }
.badge-watching { background:orange; }
.badge-unwatched { background:gray; }

.actions { display:flex; gap:8px; }

.btn-edit {
    background:#1f1f2b;
    color:#fff;
    padding:6px 12px;
    border-radius:10px;
    text-decoration:none;
    font-size:12px;
}

.btn-delete {
    color:#ff4d4d;
    border:1px solid rgba(255,0,0,0.3);
    padding:6px 12px;
    border-radius:10px;
    text-decoration:none;
}

.logout {
    margin-top:30px;
    display:flex;
    justify-content:center;
}

.logout a {
    background:#c8a96e;
    color:#000;
    padding:10px 22px;
    border-radius:12px;
    text-decoration:none;
    font-weight:600;
}

/* MODAL (minimal, does not affect design) */
.modal {
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.7);
    justify-content:center;
    align-items:center;
}

.modal-box {
    background:#14141c;
    padding:20px;
    border-radius:10px;
    width:300px;
}

.modal-box input, .modal-box select {
    width:100%;
    padding:8px;
    margin-bottom:10px;
    background:#1a1a24;
    border:none;
    color:white;
}
</style>
</head>

<body>

<div class="app">

<div class="header">
    <div class="header-left">
        <div class="logo">CINEMA VAULT</div>
        <h1>My Watchlist</h1>
    </div>

    <div style="display:flex; gap:10px;">
        <!-- ADD MODAL BUTTON -->
        <a href="#" class="btn" onclick="openAddModal()">+ Add movie</a>
        <a href="delete_all.php" class="btn">Delete All</a>
    </div>
</div>

<div class="stats">
    <div class="stat"><h2><?= $total ?></h2><small>Total Movies</small></div>
    <div class="stat"><h2><?= $watched ?></h2><small>Watched</small></div>
    <div class="stat"><h2><?= $avg ?></h2><small>Avg Rating</small></div>
</div>

<form method="GET" class="toolbar">
    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
</form>

<?php foreach ($data as $row): ?>
<div class="card">
    <div>
        <strong><?= $row['movie_title'] ?></strong>
        <span class="genre"><?= $row['genre'] ?></span>
        <div>
            <span class="badge badge-<?= $row['status'] ?>">
                <?= ucfirst($row['status']) ?>
            </span>
            <?= $row['rating'] ? $row['rating']."/10" : "No rating" ?>
        </div>
    </div>

    <div class="actions">
        <!-- EDIT MODAL BUTTON -->
        <a href="#" class="btn-edit"
        onclick="openEditModal(
        <?= $row['watchlist_id'] ?>,
        '<?= addslashes($row['movie_title']) ?>',
        '<?= addslashes($row['genre']) ?>',
        '<?= $row['status'] ?>',
        '<?= $row['rating'] ?>'
        )">Edit</a>

        <a href="delete.php?id=<?= $row['watchlist_id'] ?>" class="btn-delete">Delete</a>
    </div>
</div>
<?php endforeach; ?>

<div class="logout">
    <a href="#" onclick="openLogoutModal()">Logout</a>
</div>

</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal">
<div class="modal-box">
<form method="POST">
<input name="movie_title" placeholder="Title" required>
<input name="genre" placeholder="Genre" required>
<select name="status">
<option value="unwatched">Unwatched</option>
<option value="watching">Watching</option>
<option value="watched">Watched</option>
</select>
<input type="number" name="rating" placeholder="Rating">
<button class="btn" name="add_movie">Save</button>
</form>
</div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
<div class="modal-box">
<form method="POST">
<input type="hidden" name="id" id="edit_id">
<input name="movie_title" id="edit_title">
<input name="genre" id="edit_genre">
<select name="status" id="edit_status">
<option value="unwatched">Unwatched</option>
<option value="watching">Watching</option>
<option value="watched">Watched</option>
</select>
<input type="number" name="rating" id="edit_rating">
<button class="btn" name="edit_movie">Update</button>
</form>
</div>
</div>

<!-- LOGOUT MODAL (UNCHANGED) -->
<div id="logoutModal" class="modal">
<div class="modal-box">
<h3>Logout</h3>
<p>Are you sure?</p>
<div class="modal-actions">
<button onclick="closeModal()" class="btn-cancel">Cancel</button>
<a href="logout.php" class="btn-confirm">Logout</a>
</div>
</div>
</div>

<script>
function openAddModal(){
    document.getElementById("addModal").style.display="flex";
}

function openEditModal(id,title,genre,status,rating){
    document.getElementById("editModal").style.display="flex";
    edit_id.value=id;
    edit_title.value=title;
    edit_genre.value=genre;
    edit_status.value=status;
    edit_rating.value=rating;
}

function openLogoutModal(){
    document.getElementById("logoutModal").style.display="flex";
}

function closeModal(){
    document.querySelectorAll('.modal').forEach(m=>m.style.display='none');
}
</script>

</body>
</html>