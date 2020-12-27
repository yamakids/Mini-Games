<?php
require_once('../config.php');
require_once('../functions.php');

session_start();

$dbh = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // CSRF対策
    setToken();

    $sql = "select score_p from users WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $params = array(
        ":id" => (int)$_GET['id']
    );
    $stmt->execute($params);
    $score = $stmt->fetchColumn();
} else {
    checkToken();

    $sql = "select score_p from users WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $params = array(
        ":id" => (int)$_GET['id']
    );
    $stmt->execute($params);
    $score = $stmt->fetchColumn();

    $newscore = $_POST['score'];

    if($newscore>=$score){
      $sql = "UPDATE users SET score_p = :score WHERE id = :id";
      $stmt = $dbh->prepare($sql);
      $params = array(
          ":score" => $newscore,
          ":id" => (int)$_GET['id']
      );
      $stmt->execute($params);

      $sql = "select score_p from users WHERE id = :id";
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
    <title>PONG GAME</title>
    <style>
        body {
            margin: 0;
            font-family: "Century Gothic";
            font-size: 16px;
        }
        #container {
            text-align: center;
            margin: 5px auto;
        }
        #mycanvas {
            background: #AAEDFF;
        }
        #btn {
            margin: 3px auto;
            width: 200px;
            padding: 5px;
            background: #00AAFF;
            color: #FFFFFF;
            border-radius: 3px;
            cursor: pointer;
        }
        #btn:hover {
            opacity: 0.8;
        }
        #save{
            display: none;
            display: none;
            margin: 0 auto;
            width: 200px;
            padding: 7px;
            background: #0af;
            border-radius: 5px;
            text-align: center;
            color: #ff0;
            font-size: 14px;
            margin-top: 10px;
            cursor: pointer;
      }
    </style>
</head>
<body>
    <div id="container">
        <div>最高スコア：<?php echo h($score); ?></div>
        <canvas width="280" height="280" id="mycanvas">
            Canvasに対応したブラウザを用意してください。
        </canvas>
        <div id="btn">START</div>
        <form action='' method="post">
         <div id="save">SAVE</div>
         <input type="hidden"  name="score" id="score" value=""></input>
         <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
       </form>
       <p><a href="http://localhost/%E3%83%9D%E3%83%BC%E3%83%88%E3%83%95%E3%82%A9%E3%83%AA%E3%82%AA/%E3%83%9F%E3%83%8B%E3%82%B2%E3%83%BC%E3%83%A0/">戻る</a></p>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script>
        $(function() {
            var ctx,
                myPaddle,
                myBall,
                mouseX,
                score,
                scoreLabel,
                isPlaying = false,
                timerId;

            var canvas = document.getElementById('mycanvas');
            if (!canvas || !canvas.getContext) return false;
            ctx = canvas.getContext('2d');

            var Label = function(x, y) {
                this.x = x;
                this.y = y;
                this.draw = function(text) {
                    ctx.font = 'bold 14px "Century Gothic"';
                    ctx.fillStyle = '#00AAFF';
                    ctx.textAlign = 'left';
                    ctx.fillText(text, this.x, this.y);
                }
            }

            var Ball = function(x, y, vx, vy, r) {
                this.x = x;
                this.y = y;
                this.vx = vx;
                this.vy = vy;
                this.r = r;
                this.draw = function() {
                    ctx.beginPath();
                    ctx.fillStyle = '#FF0088';
                    ctx.arc(this.x, this.y, this.r, 0, 2*Math.PI, true);
                    ctx.fill();
                };
                this.move = function() {
                    this.x += this.vx;
                    this.y += this.vy;
                    // 左端 or 右端
                    if (this.x + this.r > canvas.width || this.x - this.r < 0) {
                        this.vx *= -1;
                    }
                    // 上
                    if (this.y - this.r < 0) {
                        this.vy *= -1;
                    }
                    // 下
                    if (this.y + this.r > canvas.height) {
                        // alert("game over");
                        isPlaying = false;
                        $('#btn').text('REPLAY?').fadeIn();
                        $('#save').fadeIn();
                        $('#score').val(score);
                    }
                };
                this.checkCollision = function(paddle) {
                    if ((this.y + this.r > paddle.y && this.y + this.r < paddle.y + paddle.h) &&
                        (this.x > paddle.x - paddle.w / 2 && this.x < paddle.x + paddle.w / 2)) {
                        this.vy *= -1;
                        score++;
                        if (score % 3 === 0) {
                            this.vx *= 1.2;
                            paddle.w *= 0.8;
                        }
                    }
                }
            }

            var Paddle = function(w, h) {
                this.w = w;
                this.h = h;
                this.x = canvas.width / 2;
                this.y = canvas.height - 30;
                this.draw = function() {
                    ctx.fillStyle = '#00AAFF';
                    ctx.fillRect(this.x - this.w / 2, this.y, this.w, this.h);
                };
                this.move = function() {
                    this.x = mouseX - $('#mycanvas').offset().left;
                }
            };

            function rand(min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            function init() {
                score = 0;
                isPlaying = true;
                myPaddle = new Paddle(100, 10);
                myBall = new Ball(rand(50, 250), rand(10, 80), rand(3, 8), rand(3, 8), 6);
                scoreLabel = new Label(10, 25);
                scoreLabel.draw('SCORE: ' + score);
            }

            function clearStage() {
                ctx.fillStyle = '#AAEDFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }

            function update() {
                clearStage();
                scoreLabel.draw('SCORE: ' + score);
                myPaddle.draw();
                myPaddle.move();
                myBall.draw();
                myBall.move();
                myBall.checkCollision(myPaddle);
                timerId = setTimeout(function() {
                    update();
                }, 30);
                if (!isPlaying) clearTimeout(timerId);
            }

            $('#btn').click(function() {
                $(this).fadeOut();
                $('#save').fadeOut();
                init();
                update();
            });

            $('body').mousemove(function(e) {
                mouseX = e.pageX;
            });

            $('#save').click(function() {
                $(this).fadeOut();
                $('form').submit();
            });
        });
    </script>
</body>
</html>
