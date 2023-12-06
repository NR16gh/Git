<!DOCUTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Mission_5-1</title>
    </head>
    
    <?php

          //データベースに接続
          $dsn='mysql:dbname=データベース名;host=localhost';
          $user='ユーザ名';
          $password='パスワード';
          $pdo=new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

          //データベースの定義
          $sql="CREATE TABLE IF NOT EXISTS info_db"
          ."("
          ."id INT PRIMARY KEY,"
          ."name CHAR(32),"
          ."comment TEXT,"
          ."date CHAR(32),"
          ."pass CHAR(32)"
          .");";
          $stmt=$pdo->query($sql);

    ?>

    <body>

        <?php

          //新規書き込み中か編集中かを表示する
          $mode="新規書き込み中";

          //更新内容を表示する
          $update="";

          //表示する対象の情報
          $edit_name="";
          $edit_comment="";
          $edit_pass="";

          //編集中かどうかと編集対象の番号を表す
          $edit_mode=-1;
          
          //現在のデータとデータ数
          $sql="SELECT * FROM info_db";
          $stmt=$pdo->query($sql);
          $lines=$stmt->fetchAll();
          $size=count($lines);

          //番号とパスワードが両方とも入力されているときに編集ボタンが押されたら対象の情報を取得して表示
          if( !empty($_POST["edit-number"]) && !empty($_POST["edit-pass"]) && !empty($_POST["edit"]) ){

            $id=$_POST["edit-number"];  //編集対象のコメントのID
            $pass=$_POST["edit-pass"];  //編集対象のコメントのパスワード

            //パスワードが設定してあり入力されたパスワードと一致するなら編集対象の情報を取得
            if( !empty($lines[$id-1]["pass"]) && $lines[$id-1]["pass"]==$pass ){
              
              //対象のコメントの情報を取得
              $edit_name=$lines[$id-1]["name"];
              $edit_comment=$lines[$id-1]["comment"];
              $edit_pass=$lines[$id-1]["pass"];
              $edit_mode=$id;
              $mode=$id."番目のコメントを編集中です";

            }elseif( empty($lines[$id-1]) ){
              $update="適切な番号を入力してください<br>";
            }elseif( empty($lines[$id-1]["pass"]) ){
              $update="編集できないコメントです<br>";
            }elseif( $lines[$id-1]["pass"]!=$pass ){
              $update="コメント番号またはパスワードが間違っています<br>";
            }

          }elseif( !empty($_POST["edit"]) ){

            if( empty($_POST["edit-number"]) ){
              $update=$update."番号が入力されていません<br>";
            }
            if( empty($_POST["edit-pass"]) ){
              $update=$update."パスワードが入力されていません<br>";
            }

          }

        ?>

        <!-- 新規書き込み中か編集中かを表示する -->
        <span style="font-size: 30px;"><?php echo $mode ?></span><hr><br>

        <form action="" method="post">
            
            入力フォーム<br>
            
            <!-- 名前入力 -->
            <input type="text" name="name" placeholder="名前" value=<?php echo $edit_name ?>><br>
            
            <!-- コメント入力 -->
            <input type="text" name="comment" placeholder="コメント" value=<?php echo $edit_comment ?>><br>

            <!-- パスワード入力 -->
            <input type="password" name="pass" placeholder="パスワード" value=<?php echo $edit_pass ?>>

            <!-- 書き込み送信ボタン -->
            <input type="submit" name="submit"><br>

            <!-- 編集対象の番号を保存する -->
            <input type="hidden" name="edit-mode" value=<?php echo $edit_mode ?>><br>

            削除フォーム<br>
            
            <!-- 削除対象番号の指定 -->
            <input type="number" name="delete-number" placeholder="コメント番号"><br>

            <!-- 削除対象のパスワード -->
            <input type="password" name="delete-pass" placeholder="パスワード">
            
            <!-- 削除対象番号の送信 -->
            <input type="submit" name="delete" placeholder="削除" value="削除"><br><br>

            編集フォーム<br>
            
            <!-- 編集対象番号の指定 -->
            <input type="number" name="edit-number" placeholder="コメント番号"><br>

            <!-- 編集対象のパスワード -->
            <input type="password" name="edit-pass" placeholder="パスワード">
            
            <!-- 編集対象番号の送信 -->
            <input type="submit" name="edit" placeholder="編集" value="編集"><br><br>
            
        </form><hr><br>
        
        <?php
          
          //名前とコメントが両方とも入力されているときに送信ボタンが押されたら書き込み処理
          if( !empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["submit"]) ){

              //新規書き込み
              if( $_POST["edit-mode"]==-1 ){

                $id=$size+1;
                $name=$_POST["name"];        //名前
                $comment=$_POST["comment"];  //コメント
                $date=date("Y/m/d/ H:i:s");  //投稿日時
                if( !empty($_POST["pass"]) ){
                  $pass=$_POST["pass"];
                }else{
                  $pass="";
                }
                $update="新規書き込みを受け付けました<br>";
              
                $sql="INSERT INTO info_db (id, name, comment, date, pass) VALUES (:id, :name, :comment, :date, :pass)";
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
                $stmt->bindParam(":date", $date, PDO::PARAM_STR);
                $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                $stmt->execute();

              //コメント編集
              }else{

                $id=$_POST["edit-mode"];     //コメント番号
                $name=$_POST["name"];        //名前
                $comment=$_POST["comment"];  //コメント
                $date=date("Y/m/d/ H:i:s");  //編集日時
                $pass=$_POST["pass"];        //パスワード
                $update=$update.$id."番のコメントを編集しました<br>";

                $sql="UPDATE info_db SET name=:name, comment=:comment, date=:date, pass=:pass WHERE id=:id";
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
                $stmt->bindParam(":date", $date, PDO::PARAM_STR);
                $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();

              }
              
          }elseif( !empty($_POST["submit"]) ){

            if( empty($_POST["name"]) ){
              $update=$update."名前が入力されていません<br>";
            }
            if( empty($_POST["comment"]) ){
              $update=$update."コメントが入力されていません<br>";
            }

          }

          //番号とパスワードが両方とも入力されているときに削除ボタンが押されたら削除処理
          if( !empty($_POST["delete-number"]) && !empty($_POST["delete-pass"]) && !empty($_POST["delete"]) ){
            
            $id=$_POST["delete-number"];  //削除対象のコメントのID
            $pass=$_POST["delete-pass"];  //削除対象のコメントのパスワード

            //パスワードが設定してあり入力されたパスワードと一致するなら削除
            if( !empty($lines[$id-1]["pass"]) && $lines[$id-1]["pass"]==$pass ){

              $update=$id."番のコメントを削除しました<br>";

              //対象のコメントの削除
              $sql="DELETE FROM info_db WHERE id=:id";
              $stmt=$pdo->prepare($sql);
              $stmt->bindParam(":id", $id, PDO::PARAM_INT);
              $stmt->execute();

              //コメント番号の変更
              for($i=$id+1; $i<=$size; $i++){
                  $new_id=$i-1;
                  $sql="UPDATE info_db SET id=:new_id WHERE id=:id";
                  $stmt=$pdo->prepare($sql);
                  $stmt->bindParam(":new_id", $new_id, PDO::PARAM_INT);
                  $stmt->bindParam(":id", $i, PDO::PARAM_INT);
                  $stmt->execute();
              }

            }elseif( empty($lines[$id-1]) ){
              $update="適切な番号を入力してください<br>";
            }elseif( empty($lines[$id-1]["pass"]) ){
              $update="削除できないコメントです<br>";
            }elseif( $lines[$id-1]["pass"]!=$pass ){
              $update="コメント番号またはパスワードが間違っています<br>";
            }

          }elseif( !empty($_POST["delete"]) ){

            if( empty($_POST["delete-number"]) ){
              $update=$update."コメント番号が入力されていません<br>";
            }
            if( empty($_POST["delete-pass"]) ){
              $update=$update."パスワードが入力されていません<br>";
            }

          }

          if( !empty($update) ){
            echo "通知:<br>".$update."<br><hr><br>";
          }

          //コメントの表示
          $sql="SELECT * FROM info_db";
          $stmt=$pdo->query($sql);
          $lines=$stmt->fetchAll();
          foreach($lines as $line){
            echo $line["id"]."　".$line["name"]."<br>".$line["comment"]."<br>".$line["date"]."<br><br>";
          }
        
        ?>
        
    </body>
</html>