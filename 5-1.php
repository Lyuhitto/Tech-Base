<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>mission_5-2</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>好きなファッションスタイルを教えてください。</h1>
  
<?php
  // DBに接続
  $dsn = 'mysql:dbname=tb_db;host=localhost';
  $user = 'EXAMPLE_ID';
  $password = 'EXAMPLE_PASSWORD';
  $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

  // テーブルの作成, テーブルname = m5_1
  // id, name(char 32), comment(text), post_time(text), password(text)
  $sql = "CREATE TABLE IF NOT EXISTS m5_1"
      ."("
      ."id INT AUTO_INCREMENT PRIMARY KEY,"
      ."name char(32),"
      ."comment TEXT,"
      ."password TEXT,"
      ."post_time TEXT"
      .");";
  $stmt = $pdo -> query($sql);

  
  // 初期値設定
  $table_name = "m5_1";
  $edit_name = "";  
  $edit_comment = "";
  $edit_password = "";
  $status = "";

  // コメント追加または編集
  if (isset($_POST["submit_button"])){
    $name = $_POST["name"];
    $comment = $_POST["comment"];
    $password = $_POST["password"];
    $post_time = date("Y/m/d H:i:s");
    
      if (is_numeric($_POST["edit_id"])) { // 編集モード
        $id = $_POST["edit_id"];
        $sql = 'UPDATE m5_1 SET '
          .'name=:name, '
          .'comment=:comment, '
          .'password=:password, '
          .'post_time=:post_time '
          .'Where id=:id';
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
        $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
        $stmt -> bindParam(':post_time', $post_time, PDO::PARAM_STR);
        $stmt -> bindParam(':id', $id, PDO::PARAM_STR);
        $stmt -> execute();

        $status = "成功的に編集されました。"
            .(empty($password) ? "パスワードがないため編集・削除はできません。" : "");
          } else { //追加モード 
          if (empty($name) || empty($comment)) {
              $status =  (empty($name) ? "名前" : "")
              .(empty($name) && empty($comment) ? "と" : "")
              .(empty($comment) ? "コメント" : "")
              ."が入力されていない";
        } else {
              $sql = 'INSERT INTO m5_1 '
              .'(name, comment, password, post_time) '
              .'VALUES (:name, :comment, :password, :post_time)';
              $stmt = $pdo -> prepare($sql);
          $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
          $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
          $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
          $stmt -> bindParam(':post_time', $post_time, PDO::PARAM_STR); 
          $stmt -> execute();

          $status = "内容を送信しました。"
              .(empty($password) ? "パスワードがないため編集・削除はできません。" : "");
        } 
      }
  }
  
  // コメント削除
  if (isset($_POST["delete_button"])) {
    $delete_id = $_POST["delete_id"];
    $delete_password = $_POST["delete_password"];
    if (empty($delete_id) || empty($delete_password)) {
      $status =  (empty($delete_id) ? "削除対象番号" : "")
        .(empty($delete_id) && empty($delete_password) ? "と" : "")
        .(empty($delete_password) ? "パスワード" : "")
        ."が入力されていない";
    } else {
      $status = "この番号は存在しません。";

      $sql = 'SELECT * FROM m5_1 WHERE id=:id';
      $stmt = $pdo -> prepare($sql);
      $stmt -> bindParam(':id', $delete_id, PDO::PARAM_INT);
      $stmt -> execute();
      $results = $stmt -> fetchAll();

      foreach ($results as $r) {
          $db_delete_password = $r['password'];
      }

      if ($delete_password == $db_delete_password) {
        $sql = 'delete from m5_1 where id=:id';
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt -> execute();

        $status = "成功的に{$delete_id}番を削除しました。";
      } else {$status = "パスワードが一致しません";}
    }
  }
  
  //　編集番号を受け取る
  if(isset($_POST["get_edit_button"])) {
      $get_edit_id = $_POST["get_edit_id"];
      $get_edit_password = $_POST["get_edit_password"];

      if (empty($get_edit_id) || empty($get_edit_password)) {
          $status =  (empty($get_edit_id) ? "編集対象番号" : "")
              .(empty($get_edit_id) && empty($get_edit_password) ? "と" : "")
              .(empty($get_edit_password) ? "パスワード" : "")
              ."が入力されていない";
      } else {
          $status = "この番号は存在しません。";

          $sql = 'SELECT * FROM m5_1 WHERE id=:id';
          $stmt = $pdo -> prepare($sql);
          $stmt -> bindParam(':id', $get_edit_id, PDO::PARAM_INT);
          $stmt -> execute();
          $results = $stmt -> fetchAll();

          foreach ($results as $r) {
              $edit_password = $r['password'];
          }

          if ($get_edit_password == $edit_password) {
              foreach ($results as $r) {
                  $edit_id = $r['id'];
                  $edit_name = $r['name'];
                  $edit_comment = $r['comment'];
              }

              $status = "{$edit_id}の内容を編集してください。";
          } else {$status = "パスワードが一致しません";}
      }
  }
?>
<span class="status">パスワードがないと、後で編集や削除はできません</span><br>
<!-- formです -->
<div class="container">
  <div class="box01">
    <form  action="" method="post">
      名前：
      <input type="text" name="name"
          value="<?php echo $edit_name; ?>"><br>
      コメント：
      <input type="text" name="comment" 
          value="<?php echo $edit_comment; ?>"><br>
      パスワード：
      <input type="password" name="password" 
      value="<?php echo $edit_password; ?>"><br>
      
      <input type="submit" name="submit_button" value="送信"><br>
      <input type="hidden" name="edit_id"
          value="<?php echo $edit_id; ?>"><br>
    </form>
  </div>
  <div class="box02">
    <form action="" method="post">
      削除対象番号：
      <input type="number" name="delete_id" min='1'
          value=""><br>
      パスワード：
      <input type="password" name="delete_password" 
          value=""><br>
      <input type="submit" name="delete_button" value="削除">
    </form>
  </div>
  <div class="box03">
    <form action="" method="post">
      編集対象番号：
      <input type="number" name="get_edit_id" min='1'
          value=""><br>
      パスワード：
      <input type="password" name="get_edit_password" value=""><br>
      <input type="submit" name="get_edit_button" value="編集">
    </form>
  </div>
</div>
<br>
<span class="status">status : <?php echo $status;?></span><br>
<hr><hr>
<h2>Log</h2>
<?php
  //　テーブルの内容を表示する
  $sql = 'SELECT * FROM m5_1';
  $stmt = $pdo -> query($sql);
  $results = $stmt -> fetchAll();

  foreach ($results as $r) {
      echo $r['id'] . ' :: ' . $r['name'] . ' :: ' . $r['post_time']. ' :: ' . '<br>';
      echo  $r['comment'] . '<br>';
      echo '<hr>';
  }
?>
</body>
</html>