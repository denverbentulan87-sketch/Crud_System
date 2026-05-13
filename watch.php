<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user's watchlist
$stmt = $conn->prepare("SELECT * FROM movie_watchlist WHERE user_id=? ORDER BY date_added DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$movies = [];
while ($row = $result->fetch_assoc()) $movies[] = $row;

// Featured = first watched movie, else first movie
$featured = null;
foreach ($movies as $m) { if ($m['status'] === 'watched') { $featured = $m; break; } }
if (!$featured && !empty($movies)) $featured = $movies[0];

// TMDB poster map (extend as needed)
$posters = [
    'avatar'      => 'https://image.tmdb.org/t/p/w500/jRXYjXNq0Cs2TcJjLkki24MLp7u.jpg',
    'spider-man'  => 'https://image.tmdb.org/t/p/w500/niqFnuAHxiMZpxibBhSGKFoC3eP.jpg',
    'spider man'  => 'https://image.tmdb.org/t/p/w500/niqFnuAHxiMZpxibBhSGKFoC3eP.jpg',
    'one piece'   => 'https://image.tmdb.org/t/p/w500/e3NBGiAifW9Xt8xD5tpARskjccO.jpg',
    'ant-man'     => 'https://image.tmdb.org/t/p/w500/9qGHifroCbLbPRqU5fvBCqimLi3.jpg',
    'ant man'     => 'https://image.tmdb.org/t/p/w500/9qGHifroCbLbPRqU5fvBCqimLi3.jpg',
    'avengers'    => 'https://image.tmdb.org/t/p/w500/RYMX2wcKCBAr24UyPD7KE3wh1wi.jpg',
    'interstellar'=> 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
    'inception'   => 'https://image.tmdb.org/t/p/w500/oYuLEt3zVCKq57qu2F8dT7NIa6f.jpg',
    'default'     => 'https://image.tmdb.org/t/p/w500/jRXYjXNq0Cs2TcJjLkki24MLp7u.jpg',
];
$backdrops = [
    'avatar'      => 'https://image.tmdb.org/t/p/w1280/o0s4XsEDfDlvit5pDRKjzXR4pp2.jpg',
    'spider-man'  => 'https://image.tmdb.org/t/p/w1280/1Rr5SrvHxMXmlFzvlmzO6RRDNUU.jpg',
    'spider man'  => 'https://image.tmdb.org/t/p/w1280/1Rr5SrvHxMXmlFzvlmzO6RRDNUU.jpg',
    'one piece'   => 'https://image.tmdb.org/t/p/w1280/2rmK7mnchw9Xr3XdiAwdt5LMaEH.jpg',
    'ant-man'     => 'https://image.tmdb.org/t/p/w1280/bIlYH4l2AJYpJKgfMCTMN5M2PUP.jpg',
    'ant man'     => 'https://image.tmdb.org/t/p/w1280/bIlYH4l2AJYpJKgfMCTMN5M2PUP.jpg',
    'avengers'    => 'https://image.tmdb.org/t/p/w1280/suaEOtk1N1sgg2MTM7oZd2cfVp3.jpg',
    'interstellar'=> 'https://image.tmdb.org/t/p/w1280/xJHokMbljvjADYdit5fK5VQsXEG.jpg',
    'inception'   => 'https://image.tmdb.org/t/p/w1280/s2bT29y0ngXxxu2IA8AOzzXTRhd.jpg',
    'default'     => 'https://image.tmdb.org/t/p/w1280/o0s4XsEDfDlvit5pDRKjzXR4pp2.jpg',
];

function getPoster($title, $map) {
    $key = strtolower(trim($title));
    return $map[$key] ?? $map['default'];
}

$featuredPoster   = $featured ? getPoster($featured['movie_title'], $posters)   : $posters['default'];
$featuredBackdrop = $featured ? getPoster($featured['movie_title'], $backdrops) : $backdrops['default'];

// Encode movies to JSON for JS
$moviesJson = json_encode(array_map(function($m) use ($posters, $backdrops) {
    return [
        'id'      => $m['watchlist_id'],
        'title'   => $m['movie_title'],
        'genre'   => $m['genre'] ?? '',
        'status'  => $m['status'],
        'rating'  => $m['rating'] ?? 0,
        'date'    => date('M j, Y', strtotime($m['date_added'])),
        'poster'  => getPoster($m['movie_title'], $posters),
        'backdrop'=> getPoster($m['movie_title'], $backdrops),
    ];
}, $movies));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cinema Vault — Browse</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
    --red:#e50914;
    --dark:#141414;
    --darker:#0a0a0a;
    --surface:#1a1a1a;
    --surface2:#222;
    --text:#fff;
    --muted:rgba(255,255,255,0.6);
    --gold:#e8c97a;
}
html{scroll-behavior:smooth}
body{background:var(--dark);color:var(--text);font-family:'Barlow',sans-serif;overflow-x:hidden;min-height:100vh}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:var(--darker)}
::-webkit-scrollbar-thumb{background:#333;border-radius:99px}

/* ══════════════════════════════════
   NAVBAR
══════════════════════════════════ */
.navbar{
    position:fixed;top:0;left:0;right:0;z-index:500;
    display:flex;align-items:center;justify-content:space-between;
    padding:0 4%;height:68px;
    background:linear-gradient(to bottom,rgba(0,0,0,0.9) 0%,transparent 100%);
    transition:background .3s;
}
.navbar.scrolled{background:rgba(20,20,20,0.98)}
.nav-logo{font-family:'Bebas Neue',sans-serif;font-size:28px;letter-spacing:.2em;color:var(--red);text-decoration:none;flex-shrink:0}
.nav-links{display:flex;gap:20px;align-items:center}
.nav-link{color:rgba(255,255,255,0.75);text-decoration:none;font-size:13px;font-weight:500;transition:color .2s}
.nav-link:hover,.nav-link.active{color:#fff}
.nav-right{display:flex;align-items:center;gap:14px}
.nav-user{font-size:13px;color:var(--muted)}
.nav-user span{color:var(--gold);font-weight:600}
.nav-btn{display:flex;align-items:center;gap:5px;padding:6px 14px;border-radius:4px;border:none;cursor:pointer;font-size:12px;font-weight:600;font-family:'Barlow',sans-serif;text-decoration:none;transition:all .2s}
.nav-btn-list{background:var(--surface);color:#fff;border:1px solid rgba(255,255,255,0.15)}
.nav-btn-list:hover{background:var(--surface2)}
.nav-btn-back{background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2)}
.nav-btn-back:hover{background:rgba(255,255,255,0.2)}

/* ══════════════════════════════════
   HERO BANNER
══════════════════════════════════ */
.hero{
    position:relative;width:100%;height:100vh;min-height:580px;max-height:800px;
    display:flex;align-items:flex-end;padding:0 4% 12%;overflow:hidden;
}
.hero-bg{
    position:absolute;inset:0;
    background-size:cover;background-position:center top;
    transition:opacity .6s;
}
.hero-bg::after{
    content:'';position:absolute;inset:0;
    background:
        linear-gradient(to right, rgba(20,20,20,0.85) 0%, rgba(20,20,20,0.4) 50%, transparent 100%),
        linear-gradient(to top,   rgba(20,20,20,1) 0%, rgba(20,20,20,0.5) 30%, transparent 70%);
}
/* Netflix red curve divider */
.hero-divider{
    position:absolute;bottom:-2px;left:0;right:0;z-index:2;
    height:80px;overflow:hidden;
}
.hero-divider svg{width:100%;height:100%}

.hero-content{position:relative;z-index:3;max-width:520px}
.hero-tag{
    display:inline-flex;align-items:center;gap:8px;
    font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;
    color:var(--red);margin-bottom:10px;
}
.hero-tag::before{content:'';width:28px;height:2px;background:var(--red);border-radius:2px}
.hero-title{
    font-family:'Bebas Neue',sans-serif;font-size:clamp(48px,7vw,90px);
    letter-spacing:.04em;line-height:.95;margin-bottom:14px;
    text-shadow:0 4px 30px rgba(0,0,0,0.5);
}
.hero-meta{display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap}
.hero-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:3px;letter-spacing:.08em}
.hb-watched  {background:#1a3a22;color:#4ade80}
.hb-watching {background:#3a2500;color:#f59e0b}
.hb-unwatched{background:#1a2035;color:#818cf8}
.hero-rating{display:flex;align-items:center;gap:4px;font-size:13px;color:var(--gold)}
.hero-genre{font-size:12px;color:var(--muted)}
.hero-dot{color:rgba(255,255,255,0.25)}
.hero-desc{font-size:14px;color:rgba(255,255,255,0.75);line-height:1.6;margin-bottom:18px;max-width:420px}
.hero-btns{display:flex;gap:10px;flex-wrap:wrap}
.hero-btn{
    display:flex;align-items:center;gap:8px;
    padding:11px 26px;border-radius:4px;border:none;
    cursor:pointer;font-size:15px;font-weight:700;
    font-family:'Barlow',sans-serif;transition:all .2s;
}
.hbtn-play{background:#fff;color:#000}
.hbtn-play:hover{background:#e0e0e0}
.hbtn-info{background:rgba(109,109,110,0.7);color:#fff}
.hbtn-info:hover{background:rgba(109,109,110,0.9)}
.hbtn-list{background:rgba(109,109,110,0.5);color:#fff;border:1px solid rgba(255,255,255,0.3)!important}
.hbtn-list:hover{background:rgba(109,109,110,0.8)}

/* ══════════════════════════════════
   CONTENT AREA
══════════════════════════════════ */
.content{position:relative;z-index:4;background:var(--dark);padding-bottom:60px;margin-top:-2px}

/* ══════════════════════════════════
   SECTION TITLE
══════════════════════════════════ */
.section{padding:0 4%;margin-bottom:40px}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.section-title{font-size:20px;font-weight:700;color:#e5e5e5;letter-spacing:.02em}
.section-see-all{font-size:12px;color:var(--red);text-decoration:none;font-weight:600;transition:opacity .2s}
.section-see-all:hover{opacity:.7}

/* ══════════════════════════════════
   TRENDING ROW  (numbered cards)
══════════════════════════════════ */
.trending-row{
    display:flex;gap:0;overflow-x:auto;
    padding-bottom:10px;scrollbar-width:none;
    position:relative;
}
.trending-row::-webkit-scrollbar{display:none}
/* scroll arrows */
.row-wrap{position:relative}
.scroll-btn{
    position:absolute;top:0;bottom:10px;width:50px;z-index:10;
    background:rgba(20,20,20,0.7);border:none;color:#fff;cursor:pointer;
    display:flex;align-items:center;justify-content:center;font-size:22px;
    opacity:0;transition:opacity .2s;
}
.row-wrap:hover .scroll-btn{opacity:1}
.scroll-btn.left{left:0;background:linear-gradient(to right,rgba(20,20,20,.9),transparent)}
.scroll-btn.right{right:0;background:linear-gradient(to left,rgba(20,20,20,.9),transparent)}

.t-card{
    flex-shrink:0;position:relative;
    width:160px;cursor:pointer;
    transition:transform .3s;margin-right:-30px;
}
.t-card:last-child{margin-right:0}
.t-card:hover{transform:scale(1.08);z-index:5;margin-right:10px}
.t-card:hover .t-overlay{opacity:1}
.t-num{
    position:absolute;bottom:-8px;left:-6px;
    font-family:'Bebas Neue',sans-serif;font-size:120px;line-height:1;
    color:var(--dark);-webkit-text-stroke:3px rgba(255,255,255,0.2);
    pointer-events:none;z-index:2;
    text-shadow:-2px 0 0 rgba(255,255,255,0.05);
}
.t-img{
    width:100%;height:230px;object-fit:cover;
    border-radius:5px;display:block;position:relative;z-index:3;
}
.t-overlay{
    position:absolute;inset:0;z-index:4;
    background:linear-gradient(to top,rgba(0,0,0,.85) 0%,transparent 50%);
    border-radius:5px;opacity:0;transition:opacity .2s;
    display:flex;flex-direction:column;justify-content:flex-end;padding:10px;
}
.t-title{font-size:11px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.t-status{font-size:9px;font-weight:700;padding:2px 6px;border-radius:2px;display:inline-block;margin-top:3px}
.ts-w{background:#1a3a22;color:#4ade80}
.ts-u{background:#1a2035;color:#818cf8}
.ts-wg{background:#3a2500;color:#f59e0b}

/* ══════════════════════════════════
   REGULAR CARD ROW (My List)
══════════════════════════════════ */
.card-row{display:flex;gap:6px;overflow-x:auto;padding-bottom:10px;scrollbar-width:none}
.card-row::-webkit-scrollbar{display:none}
.m-card{
    flex-shrink:0;width:180px;border-radius:5px;overflow:hidden;
    cursor:pointer;transition:transform .25s,box-shadow .25s;position:relative;
}
.m-card:hover{transform:scale(1.07);z-index:5;box-shadow:0 12px 40px rgba(0,0,0,.8)}
.m-card:hover .m-overlay{opacity:1}
.m-img{width:100%;height:260px;object-fit:cover;display:block}
.m-overlay{
    position:absolute;inset:0;opacity:0;transition:opacity .2s;
    background:linear-gradient(to top,rgba(0,0,0,.9) 0%,transparent 50%);
    display:flex;flex-direction:column;justify-content:flex-end;padding:10px;
}
.m-card-title{font-size:12px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.m-card-genre{font-size:10px;color:var(--muted);margin-top:2px}
.m-card-btns{display:flex;gap:5px;margin-top:6px}
.mc-btn{background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:11px;padding:4px 10px;border-radius:3px;cursor:pointer;font-family:'Barlow',sans-serif;font-weight:600;display:flex;align-items:center;gap:3px}
.mc-btn-play{background:#fff;color:#000}

/* ══════════════════════════════════
   MORE REASONS  (Netflix feature cards)
══════════════════════════════════ */
.reasons-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:8px;
}
.reason-card{
    background:linear-gradient(135deg,#1c1c1c 0%,#2a2a2a 100%);
    border:1px solid rgba(255,255,255,0.07);
    border-radius:8px;padding:28px 22px 20px;
    position:relative;overflow:hidden;
    transition:transform .2s,border-color .2s;
}
.reason-card:hover{transform:translateY(-4px);border-color:rgba(255,255,255,0.15)}
.reason-card::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(229,9,20,.03) 0%,transparent 60%);pointer-events:none}
.reason-icon{font-size:36px;margin-bottom:14px;display:block}
.reason-title{font-size:17px;font-weight:700;margin-bottom:8px;line-height:1.3}
.reason-desc{font-size:13px;color:var(--muted);line-height:1.6}

/* ══════════════════════════════════
   PLAYER VIEW
══════════════════════════════════ */
.player-view{
    display:none;position:fixed;inset:0;z-index:900;
    flex-direction:column;background:#000;
}
.player-bg{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.35) saturate(1.2)}
.player-vignette{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.1) 0%,rgba(0,0,0,.5) 55%,rgba(0,0,0,.98) 100%),linear-gradient(to right,rgba(0,0,0,.7) 0%,transparent 50%)}
.player-nav{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:16px 4%}
.p-back{display:flex;align-items:center;gap:7px;background:rgba(0,0,0,.5);border:1px solid rgba(255,255,255,.2);color:#fff;padding:8px 18px;border-radius:5px;cursor:pointer;font-size:13px;font-weight:600;font-family:'Barlow',sans-serif;backdrop-filter:blur(8px);transition:background .2s}
.p-back:hover{background:rgba(0,0,0,.8)}
.p-logo{font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:.2em;color:var(--red)}

.player-poster{
    position:absolute;right:4%;top:80px;z-index:10;
    width:200px;border-radius:8px;overflow:hidden;
    box-shadow:0 24px 60px rgba(0,0,0,.9);cursor:pointer;
}
.player-poster img{width:100%;height:295px;object-fit:cover;display:block}
.poster-hover{position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;border-radius:8px}
.player-poster:hover .poster-hover{opacity:1}
.ph-circle{width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.9);display:flex;align-items:center;justify-content:center}

.player-info{
    position:relative;z-index:10;flex:1;
    display:flex;flex-direction:column;justify-content:flex-end;
    padding:0 4% 0;
}
.pi-tag{font-size:10px;font-weight:700;letter-spacing:.2em;color:var(--red);text-transform:uppercase;margin-bottom:6px}
.pi-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(40px,6vw,72px);letter-spacing:.04em;line-height:1;margin-bottom:10px}
.pi-meta{display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap}
.pi-status{font-size:10px;font-weight:700;padding:3px 10px;border-radius:3px;letter-spacing:.08em}
.pi-genre{font-size:13px;color:var(--muted)}
.pi-dot{color:rgba(255,255,255,.25)}
.pi-date{font-size:12px;color:rgba(255,255,255,.35)}
.pi-stars{display:flex;align-items:center;gap:5px;margin-bottom:16px}
.pi-star{font-size:15px}
.pi-star.on{color:var(--gold)}
.pi-star.off{color:rgba(255,255,255,.2)}
.pi-rating{font-size:15px;color:var(--gold);font-weight:700;margin-left:4px}
.pi-btns{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.pi-btn{display:flex;align-items:center;gap:8px;padding:10px 26px;border-radius:5px;border:none;cursor:pointer;font-size:15px;font-weight:700;font-family:'Barlow',sans-serif;transition:all .2s}
.pib-play{background:#fff;color:#000}
.pib-play:hover{background:#e0e0e0}
.pib-list{background:rgba(109,109,110,.7);color:#fff}
.pib-list:hover{background:rgba(109,109,110,.9)}
.pib-like{background:rgba(109,109,110,.7);color:#fff;padding:10px 16px}
.pib-edit{background:rgba(109,109,110,.7);color:#fff}

.player-bar{position:relative;z-index:10;padding:0 4% 10px}
.now-playing{display:inline-flex;align-items:center;gap:6px;background:rgba(229,9,20,.15);border:1px solid rgba(229,9,20,.4);color:#ff5555;font-size:10px;font-weight:700;padding:3px 12px;border-radius:3px;letter-spacing:.1em;margin-bottom:10px}
.np-dot{width:7px;height:7px;border-radius:50%;background:var(--red);animation:blink 1s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}

.progress-wrap{cursor:pointer;padding:5px 0}
.p-track{height:3px;background:rgba(255,255,255,.2);border-radius:2px;transition:height .15s}
.progress-wrap:hover .p-track{height:5px}
.p-fill{height:100%;background:var(--red);border-radius:2px;position:relative;transition:width .3s linear}
.p-fill::after{content:'';position:absolute;right:-7px;top:50%;transform:translateY(-50%);width:14px;height:14px;border-radius:50%;background:#fff;opacity:0;transition:opacity .15s}
.progress-wrap:hover .p-fill::after{opacity:1}
.p-times{display:flex;justify-content:space-between;margin-top:5px;font-size:11px;color:rgba(255,255,255,.4)}

.player-controls{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:6px 4% 20px}
.ctrl-center{display:flex;align-items:center;gap:18px}
.c-btn{background:none;border:none;color:rgba(255,255,255,.8);cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;transition:color .15s,transform .15s}
.c-btn:hover{color:#fff;transform:scale(1.1)}
.c-btn-main{width:48px;height:48px;border-radius:50%;background:#fff;color:#000;font-size:20px}
.c-btn-main:hover{background:#e0e0e0;transform:scale(1.05)}
.ctrl-right{display:flex;align-items:center;gap:12px}
.vol-row{display:flex;align-items:center;gap:6px}
.vol-range{width:70px;accent-color:#fff;cursor:pointer}
.mute-btn{font-size:18px}

/* ══════════════════════════════════
   FOOTER
══════════════════════════════════ */
.footer{background:var(--darker);border-top:1px solid rgba(255,255,255,.06);padding:40px 4% 30px}
.footer-logo{font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:.2em;color:var(--red);margin-bottom:6px}
.footer-tagline{font-size:12px;color:var(--muted);margin-bottom:24px}
.footer-links{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px}
.footer-link{font-size:12px;color:rgba(255,255,255,.4);text-decoration:none;transition:color .2s}
.footer-link:hover{color:rgba(255,255,255,.8)}
.footer-copy{font-size:11px;color:rgba(255,255,255,.2)}

/* ══════════════════════════════════
   RESPONSIVE
══════════════════════════════════ */
@media(max-width:900px){
    .reasons-grid{grid-template-columns:repeat(2,1fr)}
    .player-poster{display:none}
}
@media(max-width:600px){
    .reasons-grid{grid-template-columns:1fr 1fr}
    .nav-links{display:none}
    .t-card{width:120px}
    .t-num{font-size:90px}
}
</style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav class="navbar" id="navbar">
    <a href="index.php" class="nav-logo">CINEMA VAULT</a>
    <div class="nav-links">
        <a href="index.php" class="nav-link">My Watchlist</a>
        <a href="watch.php" class="nav-link active">Browse</a>
    </div>
    <div class="nav-right">
        <span class="nav-user">Hello, <span><?= htmlspecialchars($username) ?></span></span>
        <a href="index.php" class="nav-btn nav-btn-back">
            ← Back to Vault
        </a>
    </div>
</nav>

<!-- ══ HERO BANNER ══ -->
<div class="hero" id="hero">
    <div class="hero-bg" id="heroBg" style="background-image:url('<?= $featuredBackdrop ?>')"></div>
    <div class="hero-divider">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 Q360,80 720,40 Q1080,0 1440,40 L1440,80 L0,80 Z" fill="#141414"/>
            <path d="M0,42 Q360,82 720,42 Q1080,2 1440,42" fill="none" stroke="#e50914" stroke-width="2" opacity="0.7"/>
        </svg>
    </div>
    <?php if($featured): ?>
    <div class="hero-content">
        <div class="hero-tag">Now Featuring</div>
        <div class="hero-title" id="heroTitle"><?= htmlspecialchars(strtoupper($featured['movie_title'])) ?></div>
        <div class="hero-meta">
            <span class="hero-badge hb-<?= $featured['status'] ?>"><?= strtoupper($featured['status']) ?></span>
            <?php if($featured['rating']): ?>
            <span class="hero-rating">★ <?= $featured['rating'] ?>/10</span>
            <?php endif; ?>
            <?php if($featured['genre']): ?>
            <span class="hero-dot">•</span>
            <span class="hero-genre"><?= htmlspecialchars($featured['genre']) ?></span>
            <?php endif; ?>
        </div>
        <p class="hero-desc">Added to your vault on <?= date('M j, Y', strtotime($featured['date_added'])) ?>. Click play to enter watch mode.</p>
        <div class="hero-btns">
            <button class="hero-btn hbtn-play" onclick="openPlayer(<?= $featured['watchlist_id'] ?>)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>
                Play
            </button>
            <button class="hero-btn hbtn-info" onclick="openPlayer(<?= $featured['watchlist_id'] ?>)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                More Info
            </button>
            <button class="hero-btn hbtn-list">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                My List
            </button>
        </div>
    </div>
    <?php else: ?>
    <div class="hero-content">
        <div class="hero-tag">Your Vault</div>
        <div class="hero-title">Welcome Back</div>
        <p class="hero-desc">Your watchlist is empty. <a href="index.php" style="color:var(--red)">Add some movies</a> to get started.</p>
    </div>
    <?php endif; ?>
</div>

<!-- ══ MAIN CONTENT ══ -->
<div class="content">

    <?php if(!empty($movies)): ?>

    <!-- TRENDING / WATCHLIST NUMBERED ROW -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">Trending in Your Vault</div>
            <a href="index.php" class="section-see-all">See all →</a>
        </div>
        <div class="row-wrap">
            <button class="scroll-btn left" onclick="scrollRow('trendRow',-1)">&#8249;</button>
            <div class="trending-row" id="trendRow">
                <?php foreach($movies as $i => $m):
                    $poster = getPoster($m['movie_title'], $posters);
                    $sClass = ['watched'=>'ts-w','unwatched'=>'ts-u','watching'=>'ts-wg'][$m['status']] ?? 'ts-u';
                    $sLabel = strtoupper($m['status']);
                ?>
                <div class="t-card" onclick="openPlayer(<?= $m['watchlist_id'] ?>)">
                    <div class="t-num"><?= $i+1 ?></div>
                    <img class="t-img" src="<?= $poster ?>" alt="<?= htmlspecialchars($m['movie_title']) ?>"
                         onerror="this.src='https://via.placeholder.com/160x230/1a1a1a/555?text=No+Image'" />
                    <div class="t-overlay">
                        <div class="t-title"><?= htmlspecialchars($m['movie_title']) ?></div>
                        <span class="t-status <?= $sClass ?>"><?= $sLabel ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="scroll-btn right" onclick="scrollRow('trendRow',1)">&#8250;</button>
        </div>
    </div>

    <!-- MY LIST (regular poster cards) -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">My List</div>
            <a href="index.php" class="section-see-all">Manage →</a>
        </div>
        <div class="row-wrap">
            <button class="scroll-btn left" onclick="scrollRow('myListRow',-1)">&#8249;</button>
            <div class="card-row" id="myListRow">
                <?php foreach($movies as $m):
                    $poster = getPoster($m['movie_title'], $posters);
                    $sClass = ['watched'=>'ts-w','unwatched'=>'ts-u','watching'=>'ts-wg'][$m['status']] ?? 'ts-u';
                ?>
                <div class="m-card" onclick="openPlayer(<?= $m['watchlist_id'] ?>)">
                    <img class="m-img" src="<?= $poster ?>" alt="<?= htmlspecialchars($m['movie_title']) ?>"
                         onerror="this.src='https://via.placeholder.com/180x260/1a1a1a/555?text=No+Image'" />
                    <div class="m-overlay">
                        <div class="m-card-title"><?= htmlspecialchars($m['movie_title']) ?></div>
                        <div class="m-card-genre"><?= htmlspecialchars($m['genre'] ?? '') ?></div>
                        <div class="m-card-btns">
                            <button class="mc-btn mc-btn-play">▶ Play</button>
                            <button class="mc-btn">+ List</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="scroll-btn right" onclick="scrollRow('myListRow',1)">&#8250;</button>
        </div>
    </div>

    <?php else: ?>
    <div style="padding:80px 4%;text-align:center;color:rgba(255,255,255,.4)">
        <div style="font-size:56px;margin-bottom:16px">🎬</div>
        <div style="font-size:20px;font-weight:700;margin-bottom:8px;color:#fff">Your vault is empty</div>
        <p style="margin-bottom:20px">Start building your watchlist to see it here.</p>
        <a href="index.php" style="display:inline-flex;align-items:center;gap:7px;padding:10px 24px;background:#e50914;color:#fff;text-decoration:none;border-radius:4px;font-weight:700;font-size:14px">+ Add Movies</a>
    </div>
    <?php endif; ?>


    <!-- MORE REASONS TO JOIN -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">More Reasons to Join</div>
        </div>
        <div class="reasons-grid">
            <div class="reason-card">
                <span class="reason-icon">📺</span>
                <div class="reason-title">Enjoy on your TV</div>
                <div class="reason-desc">Watch on Smart TVs, PlayStation, Xbox, Chromecast, Apple TV, Blu-ray players, and more.</div>
            </div>
            <div class="reason-card">
                <span class="reason-icon">⬇️</span>
                <div class="reason-title">Download your shows to watch offline</div>
                <div class="reason-desc">Save your favourites easily and always have something to watch.</div>
            </div>
            <div class="reason-card">
                <span class="reason-icon">📱</span>
                <div class="reason-title">Watch everywhere</div>
                <div class="reason-desc">Stream unlimited movies and TV shows on your phone, tablet, laptop, and TV.</div>
            </div>
            <div class="reason-card">
                <span class="reason-icon">👧</span>
                <div class="reason-title">Create profiles for kids</div>
                <div class="reason-desc">Send kids on adventures with their favourite characters in a space made just for them.</div>
            </div>
        </div>
    </div>

</div><!-- /.content -->

<!-- ══ FOOTER ══ -->
<footer class="footer">
    <div class="footer-logo">CINEMA VAULT</div>
    <div class="footer-tagline">Your personal movie collection, beautifully organized.</div>
    <div class="footer-links">
        <a href="index.php" class="footer-link">My Watchlist</a>
        <a href="watch.php" class="footer-link">Browse</a>
        <a href="logout.php" class="footer-link">Sign Out</a>
    </div>
    <div class="footer-copy">© <?= date('Y') ?> Cinema Vault · <?= htmlspecialchars($username) ?>'s Vault</div>
</footer>


<!-- ══ PLAYER VIEW ══ -->
<div class="player-view" id="playerView">
    <div class="player-bg" id="playerBg"></div>
    <div class="player-vignette"></div>

    <div class="player-nav">
        <button class="p-back" onclick="closePlayer()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Browse
        </button>
        <div class="p-logo">CINEMA VAULT</div>
        <div style="width:120px"></div>
    </div>

    <div class="player-poster" id="playerPoster" onclick="togglePlay()">
        <img id="playerPosterImg" src="" alt="" />
        <div class="poster-hover">
            <div class="ph-circle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#000" id="posterPlayIcon"><polygon points="5,3 19,12 5,21"/></svg>
            </div>
        </div>
    </div>

    <div class="player-info">
        <div class="pi-tag" id="piTag">ACTION</div>
        <div class="pi-title" id="piTitle">AVATAR</div>
        <div class="pi-meta">
            <span class="pi-status" id="piStatus">WATCHED</span>
            <span class="pi-dot">•</span>
            <span class="pi-genre" id="piGenre">Action</span>
            <span class="pi-dot">•</span>
            <span class="pi-date" id="piDate">May 11, 2026</span>
        </div>
        <div class="pi-stars" id="piStars"></div>
        <div class="pi-btns">
            <button class="pi-btn pib-play" onclick="togglePlay()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" id="piBtnIcon"><polygon points="5,3 19,12 5,21"/></svg>
                <span id="piBtnLabel">Play</span>
            </button>
            <button class="pi-btn pib-list">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                My List
            </button>
            <button class="pi-btn pib-like">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3z"/><path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/></svg>
            </button>
            <button class="pi-btn pib-edit" onclick="window.location='index.php'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </button>
        </div>
    </div>

    <div class="player-bar">
        <div class="now-playing" id="nowPlayingTag" style="display:none">
            <div class="np-dot"></div> NOW PLAYING
        </div>
        <div class="progress-wrap" onclick="scrubProgress(event)">
            <div class="p-track"><div class="p-fill" id="pFill" style="width:0%"></div></div>
        </div>
        <div class="p-times"><span id="pCurrTime">0:00</span><span id="pTotalTime">2:01:45</span></div>
    </div>

    <div class="player-controls">
        <div style="display:flex;gap:10px">
            <button class="c-btn" title="Bookmark">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
            </button>
            <button class="c-btn" title="Like">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </button>
        </div>
        <div class="ctrl-center">
            <button class="c-btn" title="Previous" onclick="prevMovie()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="19,20 9,12 19,4"/><line x1="5" y1="19" x2="5" y2="5"/></svg>
            </button>
            <button class="c-btn" title="-10s">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1,4 1,10 7,10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            </button>
            <button class="c-btn c-btn-main" onclick="togglePlay()" id="mainPlayBtn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#000" id="mainPlayIcon"><polygon points="5,3 19,12 5,21"/></svg>
            </button>
            <button class="c-btn" title="+10s">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23,4 23,10 17,10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
            </button>
            <button class="c-btn" title="Next" onclick="nextMovie()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5,4 15,12 5,20"/><line x1="19" y1="5" x2="19" y2="19"/></svg>
            </button>
        </div>
        <div class="ctrl-right">
            <div class="vol-row">
                <button class="c-btn mute-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/><path d="M15.54 8.46a5 5 0 010 7.07"/><path d="M19.07 4.93a10 10 0 010 14.14"/></svg>
                </button>
                <input type="range" class="vol-range" min="0" max="100" value="80" />
            </div>
            <button class="c-btn" title="Subtitles">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M7 11h10M7 15h6"/></svg>
            </button>
            <button class="c-btn" title="Fullscreen">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,3 21,3 21,9"/><polyline points="9,21 3,21 3,15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
            </button>
        </div>
    </div>
</div>


<script>
const MOVIES = <?= $moviesJson ?>;
const statusStyle = {
    watched:  'background:#1a3a22;color:#4ade80',
    unwatched:'background:#1a2035;color:#818cf8',
    watching: 'background:#3a2500;color:#f59e0b'
};

/* ── Navbar scroll ── */
window.addEventListener('scroll',()=>{
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 60);
});

/* ── Row scroll ── */
function scrollRow(id, dir) {
    const el = document.getElementById(id);
    el.scrollBy({ left: dir * 500, behavior: 'smooth' });
}

/* ── Player state ── */
let isPlaying = false, progressVal = 0, ticker = null, currentIdx = 0;
const TOTAL_SECS = 7305;

function openPlayer(id) {
    const idx = MOVIES.findIndex(m => m.id == id);
    if (idx === -1) return;
    currentIdx = idx;
    loadPlayer(MOVIES[idx]);
    document.getElementById('playerView').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    isPlaying = false; progressVal = 0;
    setPlayState(false);
    updateProgress();
}

function loadPlayer(m) {
    document.getElementById('playerBg').style.backgroundImage = `url('${m.backdrop}')`;
    document.getElementById('playerPosterImg').src = m.poster;
    document.getElementById('piTitle').textContent = m.title.toUpperCase();
    document.getElementById('piTag').textContent = (m.genre || 'FILM').toUpperCase();
    document.getElementById('piGenre').textContent = m.genre || '—';
    document.getElementById('piDate').textContent = m.date;
    const ps = document.getElementById('piStatus');
    ps.textContent = m.status.toUpperCase();
    ps.style.cssText = (statusStyle[m.status] || '') + ';font-size:10px;font-weight:700;padding:3px 10px;border-radius:3px;letter-spacing:.08em';
    const sr = document.getElementById('piStars');
    const r = parseInt(m.rating) || 0;
    sr.innerHTML = Array.from({length:10},(_,i)=>
        `<span class="pi-star ${i<r?'on':'off'}">★</span>`
    ).join('') + (r ? `<span class="pi-rating">${r}/10</span>` : '');
}

function closePlayer() {
    if(ticker){ clearInterval(ticker); ticker=null; }
    isPlaying = false;
    document.getElementById('playerView').style.display = 'none';
    document.body.style.overflow = '';
}

function setPlayState(playing) {
    const playPath  = '<polygon points="5,3 19,12 5,21"/>';
    const pausePath = '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
    const svg = playing ? pausePath : playPath;
    document.getElementById('mainPlayIcon').innerHTML = svg;
    document.getElementById('piBtnIcon').innerHTML = svg;
    document.getElementById('posterPlayIcon').innerHTML = svg;
    document.getElementById('piBtnLabel').textContent = playing ? 'Pause' : 'Play';
    document.getElementById('nowPlayingTag').style.display = playing ? 'inline-flex' : 'none';
}

function togglePlay() {
    isPlaying = !isPlaying;
    setPlayState(isPlaying);
    if(isPlaying) {
        ticker = setInterval(() => {
            progressVal = Math.min(progressVal + 0.035, 100);
            updateProgress();
            if(progressVal >= 100) { clearInterval(ticker); ticker=null; isPlaying=false; setPlayState(false); }
        }, 300);
    } else {
        clearInterval(ticker); ticker = null;
    }
}

function updateProgress() {
    document.getElementById('pFill').style.width = progressVal + '%';
    const s = Math.round((progressVal/100) * TOTAL_SECS);
    const h = Math.floor(s/3600), m = Math.floor((s%3600)/60), sec = s%60;
    document.getElementById('pCurrTime').textContent =
        (h ? h+':' : '') + String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
}

function scrubProgress(e) {
    const rect = e.currentTarget.getBoundingClientRect();
    progressVal = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
    updateProgress();
}

function prevMovie() {
    if(currentIdx > 0) { currentIdx--; loadPlayer(MOVIES[currentIdx]); progressVal=0; updateProgress(); if(isPlaying){ clearInterval(ticker); isPlaying=false; setPlayState(false); } }
}
function nextMovie() {
    if(currentIdx < MOVIES.length-1) { currentIdx++; loadPlayer(MOVIES[currentIdx]); progressVal=0; updateProgress(); if(isPlaying){ clearInterval(ticker); isPlaying=false; setPlayState(false); } }
}

/* Close player on Escape */
document.addEventListener('keydown', e => { if(e.key==='Escape') closePlayer(); });
</script>
</body>
</html>