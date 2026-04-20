<!DOCTYPE html>
<html>
<head>
<title>Cinema Vault</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=DM+Sans&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'DM Sans';
    color:white;
    background:black;
}

/* HERO */
.hero{
    height:100vh;
    background:linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)),
    url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4');
    background-size:cover;
    background-position:center;
    display:flex;
    flex-direction:column;
}

/* NAV */
.nav{
    display:flex;
    justify-content:space-between;
    padding:20px 50px;
}

.logo{
    font-family:'Syne';
    color:#e50914;
    font-size:28px;
}

.nav a{
    background:#e50914;
    padding:8px 16px;
    border-radius:6px;
    text-decoration:none;
    color:white;
}

/* CENTER TEXT */
.hero-content{
    margin:auto;
    text-align:center;
    max-width:700px;
}

.hero-content h1{
    font-size:50px;
    margin-bottom:10px;
}

.hero-content p{
    font-size:20px;
    margin-bottom:20px;
}

.hero-content a{
    background:#e50914;
    padding:12px 25px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    font-weight:bold;
}

/* ABOUT */
.about{
    padding:60px 20px;
    text-align:center;
    background:#0b0b10;
}

.about h2{
    font-size:32px;
    margin-bottom:15px;
}

.about p{
    max-width:700px;
    margin:auto;
    color:#bbb;
}

/* FEATURES */
.features{
    display:flex;
    gap:20px;
    padding:40px;
    justify-content:center;
    flex-wrap:wrap;
}

.feature{
    background:#14141c;
    padding:20px;
    border-radius:12px;
    width:250px;
    text-align:center;
}

.feature h3{
    margin-bottom:10px;
    color:#c8a96e;
}
</style>
</head>

<body>

<!-- HERO -->
<div class="hero">

<div class="nav">
    <div class="logo">CINEMA VAULT</div>
    <a href="login.php">Sign In</a>
</div>

<div class="hero-content">
    <h1>Unlimited movies tracking</h1>
    <p>Organize your watchlist. Track what you love. Never forget a movie again.</p>
    <a href="register.php">Get Started</a>
</div>

</div>

<!-- ABOUT -->
<div class="about">
    <h2>About Us</h2>
    <p>
        Cinema Vault is your personal movie tracking system.  
        Easily add, edit, and manage your watchlist with a clean and modern interface.  
        Designed for movie lovers who want simplicity and style.
    </p>
</div>

<!-- FEATURES -->
<div class="features">
    <div class="feature">
        <h3>Track Movies</h3>
        <p>Keep all your movies in one place.</p>
    </div>

    <div class="feature">
        <h3>Rate Easily</h3>
        <p>Give ratings and track your favorites.</p>
    </div>

    <div class="feature">
        <h3>Smart Filters</h3>
        <p>Search, sort, and filter instantly.</p>
    </div>
</div>

</body>
</html>