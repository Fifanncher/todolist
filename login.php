<?php
session_start();
if(isset($_POST['login']))
 {
     $conn = new mysqli("server", "login", "password", "db_name");
     $sql = "SELECT * FROM users WHERE login=?";
     $stmt = mysqli_prepare($conn, $sql);
     $stmt->bind_param("s", $_POST['login']);
     $stmt->execute();
     $result = $stmt->get_result();
     $row = $result->fetch_array(MYSQLI_ASSOC);

     if(mysqli_num_rows($result) == 1){
         if (password_verify($_POST['password'], $row["password"]))
         {
            $_SESSION['userid']=$row["id"];
            $_SESSION['username']=$_POST['login'];   
            header("Location: index.php"); // Redirecting to other page 
         }
         else
         {
             echo '<script>alert("Неверный логин или пароль");</script>';
         }
     }
     else
     {
        echo '<script>alert("Неверный логин или пароль");</script>';
     }
     mysqli_close($conn); // Closing connection
 }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/signin.css">
    <title>Вход в систему</title>
</head>

<body>
    <form class="form-signin" role="form" method="POST">
        <h1 class="h3 mb-3 font-weight-normal text-center">Вход в систему</h1>
        <input type="text" name="login" class="form-control" placeholder="Логин" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="Пароль" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
    </form>
</body>
</html>

