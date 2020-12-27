<?php
require_once('../config.php');
require_once('../functions.php');

session_start();

$dbh = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // CSRF対策
    setToken();

    $sql = "select score_m from users WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $params = array(
        ":id" => (int)$_GET['id']
    );
    $stmt->execute($params);
    $score = $stmt->fetchColumn();
} else {
    checkToken();

    $sql = "select score_m from users WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $params = array(
        ":id" => (int)$_GET['id']
    );
    $stmt->execute($params);
    $score = $stmt->fetchColumn();

    $newscore = $_POST['score'];

    if($newscore>=$score){
      $sql = "UPDATE users SET score_m = :score WHERE id = :id";
      $stmt = $dbh->prepare($sql);
      $params = array(
          ":score" => $newscore,
          ":id" => (int)$_GET['id']
      );
      $stmt->execute($params);

      $sql = "select score_m from users WHERE id = :id";
      $stmt = $dbh->prepare($sql);
      $params = array(
          ":id" => (int)$_GET['id']
      );
      $stmt->execute($params);
      $score = $stmt->fetchColumn();
   }
  }

?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>Panel Game</title>
    <link rel="stylesheet" href="CSS/styles.css">
  </head>
  <body>
    <div>最高スコア：<?php echo h($score); ?></div>
    <canvas width="250" height="250" id="stage"></canvas>
    <a href="" id="replay" class="hidden">Replay?</a>
    <form action='' method="post">
     <div id="save">SAVE AND REPLAY</div>
     <input type="hidden"  name="score" id="score" value=""></input>
     <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
    </form>
    <p><a href="http://localhost/%E3%83%9D%E3%83%BC%E3%83%88%E3%83%95%E3%82%A9%E3%83%AA%E3%82%AA/%E3%83%9F%E3%83%8B%E3%82%B2%E3%83%BC%E3%83%A0/">戻る</a></p>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>
