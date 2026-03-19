<?php 
include 'db.php';

// SEARCH LOGIC
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM students WHERE name LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM students");
}
?> 

<!DOCTYPE html>
<html>
<head>
   <title>Students CRUD</title>

   <style>
      body {
         font-family: Arial, sans-serif;
         background-color: #f4f6f9;
         margin: 0;
         padding: 20px;
      }

      h2 {
         text-align: center;
         color: #333;
      }

      .container {
         width: 80%;
         margin: auto;
         background: white;
         padding: 20px;
         border-radius: 10px;
         box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }

      .top-bar {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 15px;
      }

      .top-bar a {
         text-decoration: none;
         padding: 8px 12px;
         border-radius: 5px;
         color: white;
         margin-right: 5px;
      }

      .add-btn {
         background-color: #28a745;
      }

      .delete-btn {
         background-color: #dc3545;
      }

      .search-box input {
         padding: 7px;
         border-radius: 5px;
         border: 1px solid #ccc;
      }

      .search-box button {
         padding: 7px 10px;
         border: none;
         background-color: #007bff;
         color: white;
         border-radius: 5px;
         cursor: pointer;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      table th {
         background-color: #007bff;
         color: white;
         padding: 10px;
      }

      table td {
         padding: 10px;
         text-align: center;
      }

      table tr:nth-child(even) {
         background-color: #f2f2f2;
      }

      table tr:hover {
         background-color: #ddd;
      }

      .action a {
         text-decoration: none;
         padding: 5px 8px;
         border-radius: 4px;
         color: white;
         margin: 2px;
      }

      .edit-btn {
         background-color: #ffc107;
         color: black;
      }

      .del-btn {
         background-color: #dc3545;
      }
   </style>
</head>

<body>

<div class="container">
   <h2>Students List</h2>

   <div class="top-bar">
      <div>
         <a href="create.php" class="add-btn">Add Student</a>
         <a href="delete_all.php" class="delete-btn">Delete All</a>
      </div>

      <!-- 🔍 SEARCH -->
      <form method="GET" class="search-box">
         <input type="text" name="search" placeholder="Search by name..." value="<?php echo $search; ?>">
         <button type="submit">Search</button>
      </form>
   </div>

   <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr> 
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo $row["name"]; ?></td>
            <td><?php echo $row["email"]; ?></td>
            <td><?php echo $row["course"]; ?></td>
            <td class="action">
                <a href="edit.php?id=<?php echo $row["id"]; ?>" class="edit-btn">Edit</a> 
                <a href="delete.php?id=<?php echo $row["id"]; ?>" class="del-btn" onclick="return confirm('Delete this record?');">Delete</a>
            </td>
        </tr>
        <?php } ?>
   </table>
</div>

</body>
</html>