<?php
include 'db.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    height: 100vh;
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)),
                url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4');
    background-size: cover;
    background-position: center;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
}

/* LOGO */
.logo {
    position: absolute;
    top: 20px;
    left: 40px;
    font-size: 28px;
    font-weight: bold;
    color: #e50914;
}

/* FORM BOX */
.form-container {
    background: rgba(0,0,0,0.75);
    padding: 40px;
    border-radius: 10px;
    width: 350px;
    backdrop-filter: blur(6px);
}

.form-container h2 {
    margin-bottom: 20px;
    font-size: 28px;
}

/* INPUTS */
input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 5px;
    border: none;
    background: #333;
    color: white;
    font-size: 14px;
}

input:focus {
    outline: none;
    background: #444;
}

/* BUTTON */
button {
    width: 100%;
    padding: 12px;
    background: #e50914;
    border: none;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
}

button:hover {
    background: #f6121d;
}

/* LINK */
.bottom-text {
    margin-top: 15px;
    font-size: 13px;
    color: #bbb;
}

.bottom-text a {
    color: white;
    text-decoration: none;
}

.bottom-text a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<div class="logo">MOVIEFLIX</div>

<div class="form-container">
    <h2>Sign Up</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="register">Sign Up</button>
    </form>

    <div class="bottom-text">
        Already have an account? <a href="login.php">Sign in now</a>
    </div>
</div>

</body>
</html>