<!DOCTYPE html>
<?php
    require_once("MYDB.php");
    $pdo = db_connect("keiji");
    if(isset($_POST['thread_id'])){
        $thread_id = $_POST['thread_id'];
    }else{
        die('不正アクセス');
    }
    
    session_cache_limiter('none');
    session_start();
    $post_token = isset($_POST['token']) ? $_POST['token'] : '';
    $session_token = isset($_SESSION['token']) ? $_SESSION['token'] : '';
    unset($_SESSION['token']);
    if($post_token != '' && $session_token == $post_token){
        kakikomi($thread_id, $pdo);
    }
    $token = rtrim(base64_encode(openssl_random_pseudo_bytes(32)),'=');
    $_SESSION['token'] = $token;
    
    list($stmh1, $stmh2) = getThread($thread_id, $pdo);
    $row2 = $stmh2->fetch(PDO::FETCH_ASSOC);
    $title = $row2['title'];
?>

<html>
    <head>
        <title>スレ一覧｜2Dちゃんねる</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./css/board.css">
    </head>
    <body id="bodywrapper">
        <div id="wrapper">
            <div id="header">
                <a href="./">
                    2Dちゃんねる
                </a>
            </div>            
            <div id="content">
                <div id="threadTitle">
                    <h1><?php echo $title; ?></h1>
                </div>
                <?php
                    $n = 0;
                    while($row = $stmh1->fetch(PDO::FETCH_ASSOC)){
                        $n++;
                ?>
                <hr class="bound">
                <span class="id"><?php echo $n ?>:</span>
                <span class="name">　名前：<?php echo $row['name']; ?></span>
                <span class="date">　投稿日：<?php echo $row['date']; ?></span>
                <span class="res_id">　<?php //echo $row['res_id']; ?></span>
                <br>
                <div class="comment"><?php echo nl2br($row['comment']); ?></div>
                <?php } ?>
                <hr class="bound">
            </div>

        </div>
        <footer id="footer">
            <div class="menu">
                <input type="checkbox" id="Panel1" class="on-off" />
                <div id = "kakikomi">
                  <form method="post" action="content">
                      <div class="form" id="nameform">名前：<input type="text" name="name" style="width: 100%;"></div>
                      <span class="form" id="contentform">内容：<textarea name="comment" rows="5" style="width: 100%;" required></textarea></span><br>
                      <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
                      <input type="hidden" name="token" value="<?php echo $token;?>">
                      <span class="form" id="wbutton"><input type="submit" value="この内容で書き込む"></span>
                  </form>
                </div>
                <label for="Panel1">書き込む</label>
            </div>
            <hr id="hr">
            <ul>
                <li><a class="footer_link" href="howtouse.html">使い方＆注意</a>｜</li>
                <li><a class="footer_link" href="inquiry">問い合わせ</a>｜</li>
                <li><a class="footer_link" href="delete.html">削除依頼</a></li>
            </ul>
        </footer>
    </body>
</html>
