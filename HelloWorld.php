<?php
session_start();

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
    </head> 
    <body>
        <header><li><a href="logout.php" class="btn btn-danger ml-3">サインアウト</a></li></header>
        <h1>HelloWorld</h1>
        <?php echo $_SESSION['userName']; ?>
    </body>


</html>
