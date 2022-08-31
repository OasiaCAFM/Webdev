<?php
require_once 'imageFunction.php';

$pdo = connectDB();
$err_msg = '';
session_start();

// userNameにログイン中のnameを代入
if (isset($_SESSION['name'])) {
    $userName = $_SESSION['name'];
}


// 画像を取得
$sql = "SELECT * FROM images WHERE userName = '$userName' ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$images = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 画像を保存
    if (!empty($_FILES['image']['name'])) {
        $name = $_FILES['image']['name'];
        $type = $_FILES['image']['type'];
        $content = file_get_contents($_FILES['image']['tmp_name']);
        $size = $_FILES['image']['size'];

        // 画像のサイズ・形式チェック
        $maxFileSize = 1048576;
        $validFileTypes = ['image/png', 'image/jpeg'];
        if ($size > $maxFileSize || !in_array($type, $validFileTypes)) {
            $err_msg =
                '* jpg, jpeg, png 形式で 1 MB までの画像を選択してください。';
        }
    
    $imageTitle = $_POST["imageTitle"];
    $imageMemo = $_POST["imageMemo"];
    

        if ($err_msg == '') {
            $sql = 'INSERT INTO images(image_name, image_type, image_content, image_size, userName, imageTitle, imageMemo, created_at)
                    VALUES (:image_name, :image_type, :image_content, :image_size, :userName, :imageTitle, :imageMemo, now())';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':image_name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':image_type', $type, PDO::PARAM_STR);
            $stmt->bindValue(':image_content', $content, PDO::PARAM_STR);
            $stmt->bindValue(':image_size', $size, PDO::PARAM_INT);
            $stmt->bindValue(':userName', $userName, PDO::PARAM_STR);
            $stmt->bindValue(':imageTitle', $imageTitle, PDO::PARAM_STR);
            $stmt->bindValue(':imageMemo', $imageMemo, PDO::PARAM_STR);
            $stmt->execute();

            header('Location:list.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Image Test</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
</head>
<body>

<header>
<li><a href="logout.php" class="btn btn-danger ml-3">サインアウト</a></li>
<br>

</header>

<!--セッションにいるアカウントの名前を表示-->
<h4>Hello<?php echo $userName;
      
?></h4>
<!--ここからHTML-->
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 border-right">
            <ul class="list-unstyled">
                <?php for ($i = 0; $i < count($images); $i++): ?>
                    <li class="media mt-5">
                        <a href="#lightbox" data-toggle="modal" data-slide-to="<?= $i ?>">
                            <img src="image.php?id=<?= $images[$i][
                                'image_id'
                            ] ?>" width="100" height="auto" class="mr-3">
                        </a>
                        <div class="media-body">
                            <h4><?= $images[$i]['imageTitle'] ?> </h4>
                            <br> メモ: <br> 
                            <h5><?= $images[$i]['imageMemo']?> </h5>
                            <a href="javascript:void(0);" onclick="var ok = confirm('削除しますか？'); if (ok) location.href='imageDelete.php?id=<?= $images[
                                $i]['image_id'] ?>'"><i class="far fa-trash-alt"></i> 削除</a>
                        </div>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
        <!--入力フォーム-->
        <div class="col-md-4 pt-4 pl-4">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>画像を選択</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png" required>
                    <?php if ($err_msg != ''): ?>
                        <div class="invalid-feedback d-block"><?= $err_msg ?></div>
                    <?php endif; ?>
                    <p>タイトル:<br><input type="title" name="imageTitle" required="required"></p>
                    <p>メモ:<textarea name="imageMemo" cols="50" rows="5"></textarea></p>
                </div>
                
              <button type="submit" class="btn btn-primary">保存</button>
            </form>
            
        </div>
    </div>
</div>

<div class="modal carousel slide" id="lightbox" tabindex="-1" role="dialog" data-ride="carousel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <ol class="carousel-indicators">
            <?php for ($i = 0; $i < count($images); $i++): ?>
                <li data-target="#lightbox" data-slide-to="<?= $i ?>" <?php if (
    $i == 0
) {
    echo 'class="active"';
} ?>></li>
            <?php endfor; ?>
        </ol>
        <div class="carousel-inner">
            <?php for ($i = 0; $i < count($images); $i++): ?>
                <div class="carousel-item <?php if ($i == 0) {
                    echo 'active';
                } ?>">
                  <img src="image.php?id=<?= $images[$i][
                      'image_id'
                  ] ?>" class="d-block w-100">
                </div>
            <?php endfor; ?>
        </div>
        <a class="carousel-control-prev" href="#lightbox" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#lightbox" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>