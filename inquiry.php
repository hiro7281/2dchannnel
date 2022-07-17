<!DOCTYPE html>
<?php
    require_once("MYDB.php");
    $pdo = db_connect("keiji");
    if($_SERVER['REQUEST_METHOD']=='POST'){
        if(isset($_POST['comment'])){
            inquiry($pdo);
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
        }
    }
?>
<html>
    <head>
        <title>問い合わせフォーム｜2Dちゃんねる</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./css/board.css">
    </head>
    <body id="bodywrapper">
        <h1>問い合わせ</h1>
        <div>
            <p>掲示板に関する質問などは、以下のフォームからお願いします。</p>
            <br>
            <div id="inquiry">
                <form method="post" action="inquiry">
                    <span class="form" id="titleform">　件名：<input type="text" name="title" size="60"></span><br>
                    <span class="form" id="emailform"><span style="margin-left: 0px;">e-mail：</span><input type="text" name="email" size="60" required></span><br>
                    <span class="form" id="inquiryform">　内容：<textarea name="comment" rows="20" cols="80" required></textarea></span>
                    <span class="form" id="submitform"><input type="submit" value="送信"></span>
                </form>
            </div>
            <div>書き込みの削除依頼は<a href="delete.html">こちら</a>から。</div>
            <div><a href="/" class="return">板一覧に戻る</a></div>
        </div>
    </body>
</html>