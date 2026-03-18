<?php 
include 'db.php';

$result = $conn->query("SELECT * FROM students");
?> 

<!DOCTYPE html>
<html>
<head>
   <title>Students CRUD</title>
</head>

<body>
   <h2>Students List</h2>
   
   <a href="create.php">Add New Students</a>
   <a href="delete_all.php">Delete All</a>

   <br><br>

   <table border="1" cellpadding="10" cellspacing="0">
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
            <td>
                <a href="edit.php?id=<?php echo $row["id"]; ?>">Edit</a> 
                <a href="delete.php?id=<?php echo $row["id"]; ?>" onclick="return confirm('Delete this record?');">Delete</a>
            </td>
        </tr>
        <?php } ?>
   </table>
</body>
</html>