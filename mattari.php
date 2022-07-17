<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
    require_once("MYDB.php");
    $pdo = db_connect("keiji");
    
    session_cache_limiter('none');
    session_start();
    $post_token = isset($_POST['token']) ? $_POST['token'] : '';
    $session_token = isset($_SESSION['token']) ? $_SESSION['token'] : '';
    unset($_SESSION['token']);
    if($post_token != '' && $session_token == $post_token){
        if(isset($_POST['comment'])){
            mkThre(1, getClientIp(), $pdo);
        }
    }
    $token = rtrim(base64_encode(openssl_random_pseudo_bytes(32)),'=');
    $_SESSION['token'] = $token;
    
    try{
        $pdo->beginTransaction();
        $sql = "SELECT * FROM thread_header WHERE board_id = 1;";
        $stmh = $pdo->prepare($sql);
        $stmh->execute();
        $pdo->commit();
    } catch (Exception $ex) {
        $pdo->rollBack();
    }
?>
<html>
    <head>
        <title>掲示板｜2Dちゃんねる</title>
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
            <hr>
            <h1>まったり</h1>
            <div id="thread">
                <table id="threadTable">
                    <thead>
                        <tr>
                            <th id="id">id</th>
                            <th id="title">タイトル</th>
                            <th id="response">レス数</th>
                            <th id="last">最終書込</th>
                            <th id="since">since</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $n = 0;
                            $class = "even";
                            while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
                                if($n % 2 == 0){
                                    $class = "even";
                                }else{
                                    $class = "odd";
                                }
                                $n++;
                                print "<tr class=\"{$class}\">";
                        ?>
                            <td id="id"><?php echo $row['id'] ?></td>
                            <td id="title">
                                <?php
    $text = "<form action=\"content\" name=\"form".$row['id']."\" method=\"POST\">"
            . " <input type=\"hidden\" name=\"thread_id\" value=".$row['id']." >"
            . " <a href=\"\" onclick=\"document.form".$row['id'].".submit();return false;\">"
            .$row['title']."</a></form>";
                                    echo $text;
                                ?>
                            </td>
                            <td id="response"><?php echo $row['response'] ?></td>
                            <td id="last"><?php echo $row['lastUpdate'] ?></td>
                            <td id="since"><?php echo $row['since'] ?></td>
                        <?php print "</tr>"; } ?>
                    </tbody>

                </table>
            </div>

            <div id = "mkThre">
                <div id="text">新規スレッド作成</div>
                <br>
                <div id="form">
                    <form method="post" action="mattari">
                        <span class="form" id="titleform">タイトル：<input type="text" name="title" size="60"></span><br>
                        <span class="form" id="nameform">　　名前：<input type="text" name="name" size="60"></span><br>
                        <span class="form" id="contentform">　　内容：<textarea name="comment" rows="7" cols="80" required></textarea></span>
                        <span class="form" id="submitform"><input type="submit" value="書込"></span>
                        <input type="hidden" name="token" value="<?php echo $token;?>">
                    </form>
                </div>

            </div>
        </div>  
        
        <footer id="footer">
            <hr id="hr">
            <ul>
                <li><a class="footer_link" href="howtouse.html">使い方＆注意</a>｜</li>
                <li><a class="footer_link" href="inquiry">問い合わせ</a>｜</li>
                <li><a class="footer_link" href="delete.html">削除依頼</a></li>
            </ul>
        </footer>
    </body>
</html>
