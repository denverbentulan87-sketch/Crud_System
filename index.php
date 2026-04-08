<?php 
include 'db.php';


session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// SEARCH LOGIC
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM movie_watchlist WHERE name LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM movie_watchlist");
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Movie Watchlist</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0e0e12;
    --surface: #17171e;
    --surface2: #1f1f28;
    --surface3: #2a2a36;
    --border: rgba(255,255,255,0.07);
    --border2: rgba(255,255,255,0.12);
    --accent: #c8a96e;
    --accent2: #e8c98e;
    --text: #f0ece4;
    --text2: #a09a90;
    --text3: #6a6560;
    --watched: #4caf82;
    --watching: #c8a96e;
    --unwatched: #6a6a80;
    --danger: #e05c5c;
    --font-head: 'Syne', sans-serif;
    --font-body: 'DM Sans', sans-serif;
    --radius: 10px;
    --radius-lg: 16px;
  }

  body {
    font-family: var(--font-body);
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    line-height: 1.6;
  }

  /* ── Layout ── */
  .app { max-width: 860px; margin: 0 auto; padding: 2.5rem 1.5rem 4rem; }

  /* ── Header ── */
  .header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .header-left {}
  .logo {
    font-family: var(--font-head);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 4px;
  }
  .header h1 {
    font-family: var(--font-head);
    font-size: 36px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.1;
  }

  /* ── Stats ── */
  .stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 2rem;
  }
  .stat {
    background: var(--surface);
    border: 0.5px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1rem 1.2rem;
    text-align: center;
  }
  .stat-val {
    font-family: var(--font-head);
    font-size: 28px;
    font-weight: 700;
    color: var(--accent);
    line-height: 1;
  }
  .stat-lbl {
    font-size: 11px;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-top: 4px;
  }

  /* ── Toolbar ── */
  .toolbar {
    display: flex;
    gap: 8px;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
  }
  .toolbar input,
  .toolbar select {
    background: var(--surface);
    border: 0.5px solid var(--border);
    border-radius: var(--radius);
    padding: 8px 12px;
    color: var(--text);
    font-family: var(--font-body);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
  }
  .toolbar input { flex: 1; min-width: 160px; }
  .toolbar select { min-width: 140px; }
  .toolbar input:focus,
  .toolbar select:focus { border-color: var(--accent); }
  .toolbar select option { background: var(--surface2); }

  /* ── Buttons ── */
  .btn {
    padding: 8px 16px;
    font-family: var(--font-body);
    font-size: 13px;
    border-radius: var(--radius);
    border: 0.5px solid var(--border2);
    background: transparent;
    color: var(--text);
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
    white-space: nowrap;
  }
  .btn:hover { background: var(--surface3); }
  .btn-primary {
    background: var(--accent);
    border-color: var(--accent);
    color: #0e0e12;
    font-weight: 500;
  }
  .btn-primary:hover { background: var(--accent2); border-color: var(--accent2); }
  .btn-sm { padding: 5px 10px; font-size: 12px; }
  .btn-danger { color: var(--danger); border-color: rgba(224,92,92,0.3); }
  .btn-danger:hover { background: rgba(224,92,92,0.1); }

  /* ── Cards ── */
  .cards { display: grid; gap: 10px; }
  .card {
    background: var(--surface);
    border: 0.5px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
    align-items: start;
    transition: border-color 0.2s;
  }
  .card:hover { border-color: var(--border2); }
  .card-left { min-width: 0; }
  .card-title {
    font-family: var(--font-head);
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .card-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 8px;
    font-size: 12px;
    color: var(--text3);
  }
  .card-bottom {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .card-actions { display: flex; gap: 6px; flex-shrink: 0; margin-top: 2px; }

  /* ── Badges ── */
  .badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.05em;
  }
  .badge-watched { background: rgba(76,175,130,0.15); color: var(--watched); }
  .badge-watching { background: rgba(200,169,110,0.15); color: var(--watching); }
  .badge-unwatched { background: rgba(106,106,128,0.15); color: var(--unwatched); }

  .genre-tag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 99px;
    font-size: 11px;
    background: var(--surface3);
    color: var(--text2);
    border: 0.5px solid var(--border);
  }
  .dot { width: 3px; height: 3px; border-radius: 50%; background: var(--border2); }

  /* ── Stars ── */
  .stars { color: var(--accent); font-size: 13px; letter-spacing: 1px; }
  .rating-text { font-size: 12px; color: var(--text3); }

  /* ── Notes ── */
  .card-notes {
    font-size: 12px;
    color: var(--text3);
    margin-top: 7px;
    padding-top: 7px;
    border-top: 0.5px solid var(--border);
    font-style: italic;
  }

  /* ── Modal ── */
  .modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(4px);
  }
  .modal-overlay.open { display: flex; }
  .modal {
    background: var(--surface);
    border: 0.5px solid var(--border2);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    width: 100%;
    max-width: 440px;
    max-height: 90vh;
    overflow-y: auto;
  }
  .modal h2 {
    font-family: var(--font-head);
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 1.25rem;
  }
  .form-row { margin-bottom: 13px; }
  .form-row label {
    display: block;
    font-size: 11px;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 5px;
  }
  .form-row input,
  .form-row select,
  .form-row textarea {
    width: 100%;
    background: var(--surface2);
    border: 0.5px solid var(--border);
    border-radius: var(--radius);
    padding: 8px 12px;
    color: var(--text);
    font-family: var(--font-body);
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
  }
  .form-row input:focus,
  .form-row select:focus,
  .form-row textarea:focus { border-color: var(--accent); }
  .form-row textarea { resize: vertical; min-height: 70px; }
  .form-row select option { background: var(--surface2); }
  .form-row input.error { border-color: var(--danger); }
  .form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 1.25rem;
  }

  /* ── Empty ── */
  .empty {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text3);
    font-size: 14px;
  }
  .empty-icon {
    font-size: 40px;
    margin-bottom: 0.75rem;
    opacity: 0.4;
  }

  /* ── Confirm dialog ── */
  .confirm-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 200;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .confirm-overlay.open { display: flex; }
  .confirm-box {
    background: var(--surface);
    border: 0.5px solid var(--border2);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    width: 100%;
    max-width: 340px;
    text-align: center;
  }
  .confirm-box h3 { font-family: var(--font-head); font-size: 17px; font-weight: 600; margin-bottom: 8px; }
  .confirm-box p { font-size: 13px; color: var(--text2); margin-bottom: 1.25rem; }
  .confirm-actions { display: flex; gap: 8px; justify-content: center; }

  /* ── Scrollbar ── */
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: var(--surface3); border-radius: 3px; }
</style>
</head>
<body>

<div class="app">
  <div class="header">
    <div class="header-left">
      <div class="logo">Cinema vault</div>
      <h1>My Watchlist</h1>
    </div>
    <button class="btn btn-primary" onclick="openModal()">+ Add movie</button>
  </div>

  <div class="stats">
    <div class="stat">
      <div class="stat-val" id="s-total">0</div>
      <div class="stat-lbl">Total movies</div>
    </div>
    <div class="stat">
      <div class="stat-val" id="s-watched">0</div>
      <div class="stat-lbl">Watched</div>
    </div>
    <div class="stat">
      <div class="stat-val" id="s-avg">—</div>
      <div class="stat-lbl">Avg rating</div>
    </div>
  </div>

  <div class="toolbar">
    <input id="search" placeholder="Search by title or genre..." oninput="render()">
    <select id="filter-status" onchange="render()">
      <option value="">All statuses</option>
      <option value="unwatched">Unwatched</option>
      <option value="watching">Watching</option>
      <option value="watched">Watched</option>
    </select>
    <select id="sort" onchange="render()">
      <option value="added">Recently added</option>
      <option value="title">Title A–Z</option>
      <option value="year">Year</option>
      <option value="rating">Rating</option>
    </select>
  </div>

  <div class="cards" id="cards"></div>
</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay" id="modal-overlay">
  <div class="modal">
    <h2 id="modal-title">Add movie</h2>
    <div class="form-row">
      <label>Title *</label>
      <input id="f-title" placeholder="Movie title">
    </div>
    <div class="form-row">
      <label>Genre</label>
      <input id="f-genre" placeholder="e.g. Action, Drama, Sci-Fi">
    </div>
    <div class="form-row">
      <label>Year</label>
      <input id="f-year" type="number" placeholder="e.g. 2024" min="1900" max="2030">
    </div>
    <div class="form-row">
      <label>Director</label>
      <input id="f-director" placeholder="e.g. Christopher Nolan">
    </div>
    <div class="form-row">
      <label>Watch status</label>
      <select id="f-status">
        <option value="unwatched">Unwatched</option>
        <option value="watching">Watching</option>
        <option value="watched">Watched</option>
      </select>
    </div>
    <div class="form-row">
      <label>Rating (1–10)</label>
      <input id="f-rating" type="number" min="1" max="10" placeholder="Leave blank if not watched">
    </div>
    <div class="form-row">
      <label>Notes</label>
      <textarea id="f-notes" placeholder="Your thoughts, reminders, anything..."></textarea>
    </div>
    <div class="form-actions">
      <button class="btn" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveMovie()">Save</button>
    </div>
  </div>
</div>

<!-- Confirm delete dialog -->
<div class="confirm-overlay" id="confirm-overlay">
  <div class="confirm-box">
    <h3>Remove movie?</h3>
    <p>This will permanently remove the movie from your watchlist.</p>
    <div class="confirm-actions">
      <button class="btn" onclick="cancelDelete()">Cancel</button>
      <button class="btn btn-danger" onclick="confirmDelete()">Remove</button>
    </div>
  </div>
</div>

<script>
  
</head>

<body>

<div class="container">
   <h2>Movie List</h2>

   <div class="top-bar">
      <div>
         <a href="create.php" class="add-btn">Add Movie</a>
         <a href="delete_all.php" class="delete-btn">Delete All</a>
      </div>

      <!-- 🔍 SEARCH -->
      <form method="GET" class="search-box">
         <input type="text" name="search" placeholder="Search by movie name..." value="<?php echo $search; ?>">
         <button type="submit">Search</button>
      </form>
   </div>

   <table>
        <tr>
            <th>Watchlist_id</th>
            <th>User_id</th>
            <th>Movie_Title</th>
            <th>Genre</th>
            <th>Status</th>
            <th>Rating</th>
            <th>Date_Added</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr> 
            <td><?php echo $row["Watchlist_id"]; ?></td>
            <td><?php echo $row["User_id"]; ?></td>
            <td><?php echo $row["Movie_Title"]; ?></td>
            <td><?php echo $row["Genre"]; ?></td>
             <td><?php echo $row["Status"]; ?></td>
            <td><?php echo $row["Rating"]; ?></td>
            <td><?php echo $row["Date_Added"]; ?></td>
            <td class="action">
                <a href="edit.php?id=<?php echo $row["id"]; ?>" class="edit-btn">Edit</a> 
                <a href="delete.php?id=<?php echo $row["id"]; ?>" class="del-btn" onclick="return confirm('Delete this record?');">Delete</a>
                <a href="logout.php" class="btn">Logout</a>
            </td>
        </tr>
        <?php } ?>
   </table>
</div>

</body>
</html>