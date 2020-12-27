<?php

require_once('config.php');
require_once('functions.php');

session_start();

if (empty($_SESSION['me'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$me = $_SESSION['me'];

$dbh = connectDb();

$users = array();

$sql = "select * from users order by created desc";
foreach ($dbh->query($sql) as $row) {
    array_push($users, $row);
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>ホーム画面</title>
    <style>
     body{
       text-align: center;
       background-color: gray;
     }
     .container{
       width: 500px;
       margin: 10px auto;
       background-color: white;
       padding: 20px;
     }
     ul{
       margin: 0;
       padding: 0;
       list-style: none;
     }
     li + li{
       margin-top: 10px;
     }
    </style>
  </head>
  <body>
  <div class="container">
    <p>
        Logged in as <?php echo h($me['name']); ?> (<?php echo h($me['email']); ?>) <a href="logout.php">[logout]</a>
    </p>
    <h1>ユーザー一覧</h1>
      <ul>
        <li><a href="ピンポンゲーム/index.php?id=<?php echo h($me['id']); ?>">ピンポンゲーム</a></li>
        <li><a href="数字タッチゲーム/index.php?id=<?php echo h($me['id']); ?>">数字タッチゲーム</a></li>
        <li><a href="間違い探しゲーム/index.php?id=<?php echo h($me['id']); ?>">間違い探しゲーム</a></li>
      </ul>
  </div>
  </body>
</html>
