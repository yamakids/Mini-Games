<?php
require_once('../config.php');
require_once('../functions.php');

session_start();

$dbh = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // CSRF対策
    setToken();

    $sql = "select time from users WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $params = array(
        ":id" => (int)$_GET['id']
    );
    $stmt->execute($params);
    $time = $stmt->fetchColumn();
    $time = number_format($time, 2);
} else {
    checkToken();

    $sql = "select time from users WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $params = array(
        ":id" => (int)$_GET['id']
    );
    $stmt->execute($params);
    $time = $stmt->fetchColumn();

    $newtime = $_POST['time'];

  if($newtime<=$time||$time==0){
      $sql = "UPDATE users SET time = :time WHERE id = :id";
      $stmt = $dbh->prepare($sql);
      $params = array(
          ":time" => $_POST['time'],
          ":id" => (int)$_GET['id']
      );
      $stmt->execute($params);

      $sql = "select time from users WHERE id = :id";
      $stmt = $dbh->prepare($sql);
      $params = array(
          ":id" => (int)$_GET['id']
      );
      $stmt->execute($params);
      $time = $stmt->fetchColumn();
   }
      $time = number_format($time, 2);
  }

?>

<!DOCTYPE html>
  <html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>Numbers Game</title>
    <link rel="stylesheet" href="CSS/styles.css">
  </head>
  <body>
    <div id="container">
      <div>最高タイム：<?php echo h($time); ?></div>
      <div id="timer">0.0</div>
      <ul id="board">
      </ul>
      <div id="btn">START</div>
      <form action='' method="post">
       <div id="save">SAVE</div>
       <input type="hidden"  name="time" id="time" value=""></input>
       <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
      </form>
      <p id="back"><a href="http://localhost/%E3%83%9D%E3%83%BC%E3%83%88%E3%83%95%E3%82%A9%E3%83%AA%E3%82%AA/%E3%83%9F%E3%83%8B%E3%82%B2%E3%83%BC%E3%83%A0/">戻る</a></p>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/main.js"></script>
  </body>
  </html>
