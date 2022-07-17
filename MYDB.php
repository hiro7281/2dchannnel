<?php
//データベース接続用関数
function db_connect($db_name){
    $db_user = "daichan";
    $db_pass = "stock2376";
    $db_host = "localhost";
    $db_type = "mysql";
    
    $dsn = "$db_type:host=$db_host; dbname=$db_name; charset=utf8";
    
    try{
        $pdo=new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $ex) {
        die('エラー:'. $ex->getMessage());
    }
    return $pdo;
}

//XSS対策用
function h_($str){
    return htmlspecialchars($str);
}

function mkThre($board, $ip, $pdo){
    //スレッドヘーダのテーブルにデータを追加
    try{
        //トランザクション処理を開始
        $pdo->beginTransaction();
        //名前付きプレースホルダを作る
        $sql = "INSERT INTO `thread_header` (`title`, `response`, `lastUpdate`, "
                . "`since`, `authorIP`, `board_id`) VALUES (:title, '1', :lastUpdate,"
                . " :since, :authorIP, :board_id);";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(':title', h_($_POST['title']), PDO::PARAM_STR);
        $date = new DateTime("now"); //現在時刻を取得,PDOにDateTimeのデータ型が存在しないので、STR型で挿入
        $stmh->bindValue(':lastUpdate', $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmh->bindValue(':since', $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmh->bindValue(':authorIP', $ip, PDO::PARAM_INT);
        $stmh->bindValue(':board_id', $board, PDO::PARAM_INT);
        $stmh->execute();
        $last_id = $pdo->lastInsertId('id');

        //新しいスレの1コメを追加する
        $sql = "INSERT INTO `response` (`name`, `comment`, `date`, `res_id`,"
                . "`thread_id`, `authorIP`) VALUES (:name, :comment, :date,"
                . " :res_id, :thread_id, :authorIP);";
        $stmh1 = $pdo->prepare($sql);
        $name = (h_($_POST['name']) == "") ? "匿名" : h_($_POST['name']);
        $stmh1->bindValue(':name', $name, PDO::PARAM_STR);
        $commentNR = nl2br(h_($_POST['comment']));
        $stmh1->bindValue(':comment', $commentNR, PDO::PARAM_STR);
        $stmh1->bindValue(':date', $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $ID = userIP2ID($ip);
        $stmh1->bindValue(':res_id', $ID, PDO::PARAM_STR);
        $stmh1->bindValue(':thread_id', $last_id, PDO::PARAM_INT);
        $stmh1->bindValue(':authorIP', $ip, PDO::PARAM_INT);
        $stmh1->execute();
        $pdo->commit();
        echo "スレッド作成成功";
    } catch (Exception $ex) {
        $pdo->rollBack();
        echo $ex->getMessage();
        echo "<br>";
    }
}

function getThread($thread_id, $pdo){
    try{
        $pdo->beginTransaction();
        $sql1 = "SELECT * FROM response WHERE thread_id = :thread_id;";
        $stmh1 = $pdo->prepare($sql1);
        $stmh1->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmh1->execute();
        $sql2 = "SELECT title FROM thread_header WHERE id = :thread_id;";
        $stmh2 = $pdo->prepare($sql2);
        $stmh2->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmh2->execute();
        $pdo->commit();
    } catch (Exception $ex) {
        $pdo->rollBack();
    }
    return array($stmh1, $stmh2);
}

function kakikomi($thread_id, $pdo){
    try{
        $pdo->beginTransaction();
        $sql1 = "INSERT INTO `response` (`name`, `comment`, `date`, `res_id`,"
                . "`thread_id`, `authorIP`) VALUES (:name, :comment, :date,"
                . " :res_id, :thread_id, :authorIP);";
        $stmh1 = $pdo->prepare($sql1);
        $name = (h_($_POST['name']) == "") ? "匿名" : h_($_POST['name']);
        $stmh1->bindValue(':name', $name, PDO::PARAM_STR);
	//$comment = str_replace("\r\n", '\r', $_POST['comment']);
        $commentNR = nl2br(h_($_POST['comment']));
        $stmh1->bindValue(':comment', $_POST['comment'], PDO::PARAM_STR);
        $date = new DateTime("now");
        $stmh1->bindValue(':date', $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $ip = getClientIp();
        $ID = userIP2ID($ip);
        $stmh1->bindValue(':res_id', $ID, PDO::PARAM_STR);
        $stmh1->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
        $stmh1->bindValue(':authorIP', $ip, PDO::PARAM_INT);
        $stmh1->execute();
        
        //レス数を1増やす
        $sql2 = "UPDATE thread_header SET response = response + 1 WHERE id = :id";
        $stmh2 = $pdo->prepare($sql2);
        $stmh2->bindValue(':id', $thread_id, PDO::PARAM_INT);
        $stmh2->execute();
        
        //lastUpdateを更新する
        $sql3 = "UPDATE thread_header SET lastUpdate = :lastUpdate WHERE id = :id";
        $stmh3 = $pdo->prepare($sql3);
        $stmh3->bindValue(':id', $thread_id, PDO::PARAM_INT);
        $stmh3->bindValue(':lastUpdate', $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmh3->execute();
        $pdo->commit();
        echo "書き込み成功";
    } catch (Exception $ex) {
        $pdo->rollBack();
        echo $ex->getMessage();
    }
}

function inquiry($pdo){
        try{
        //inquiryテーブルに問い合わせ内容を追加する
        $pdo->beginTransaction();
        $sql = "INSERT INTO `inquiry` (`title`, `email`, `comment`, `authorIP`, `date`)"
                . " VALUES (:title, :email, :comment, :authorIP, :date);";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(':title', h_($_POST['title']), PDO::PARAM_STR);
        $stmh->bindValue(':email', h_($_POST['email']), PDO::PARAM_STR);
        $stmh->bindValue(':comment', h_($_POST['comment']), PDO::PARAM_STR);
        $date = new DateTime("now"); //現在時刻を取得,PDOにDateTimeのデータ型が存在しないので、STR型で挿入
        $stmh->bindValue(':date', $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $ip = getClientIp();
        $stmh->bindValue(':authorIP', $ip, PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();
        echo "送信成功";
    } catch (Exception $ex) {
        $pdo->rollBack();
        echo $ex->getMessage();
        echo "<br>";
    }
}

//ipとIDを紐づけるテーブルを作る予定
function userIP2ID($ip){
    $ID = '{$ip}';
    return $ID;
}

// 指定されたサーバー環境変数を取得する(getClientIpで使う)
function getServer($key, $default = null)
{
    return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
}
 
// クライアントのIPアドレスを取得する
function getClientIp($checkProxy = true)
{
    /*
     *  プロキシサーバ経由の場合は、プロキシサーバではなく
     *  接続もとのIPアドレスを取得するために、サーバ変数
     *  HTTP_CLIENT_IP および HTTP_X_FORWARDED_FOR を取得する。
     */
    if ($checkProxy && getServer('HTTP_CLIENT_IP') != null) {
        $ip = getServer('HTTP_CLIENT_IP');
    } else if ($checkProxy && getServer('HTTP_X_FORWARDED_FOR') != null) {
        $ip = getServer('HTTP_X_FORWARDED_FOR');
    } else {
        // プロキシサーバ経由でない場合は、REMOTE_ADDR から取得する
        $ip = getServer('REMOTE_ADDR');
    }
    return $ip;
}

function doubleCheck($pdo, $board_id, $title="0000000", $name, $comment){
    try{
        //レス書き込みの場合
        if($title == "0000000"){
            $sql = "SELECT * FROM response WHERE (thread_id = :thread_id "
                    . "AND name = :name AND comment = :comment);";
            $stmh = $pdo->prepare($sql);
            $stmh->bindValue(':board', $board, PDO::PARAM_INT);
            $stmh->bindValue(':name', $name, PDO::PARAM_STR);
            $stmh->bindValue(':comment', $comment, PDO::PARAM_INT);
            $stmh->execute();
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
            echo "連投されました";
        }else{//スレ作成の場合
            $sql = "SELECT count(*) FROM thread_header WHERE (board_id = :board_id "
                    . "AND title = :title);";
            $stmh = $pdo->prepare($sql);
            $stmh->bindValue(':board_id', $board_id, PDO::PARAM_INT);
            $stmh->bindValue(':title', $title, PDO::PARAM_STR);
            $stmh->execute();
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
            echo "連投されました";
            echo $row;
        }
        if($row >= 1){
            echo $stmh->fetchColumn();
            return true;
        }else{
            echo "not rentou";
            return false;
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}
?>
