<?php
$conn = new mysqli("server", "login", "password", "db_name");;
session_start();

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
} 
elseif ($_SESSION['userid'] != 1)
{
	header("Location: index.php");
}


if (isset($_GET['add'])) { //проверяем, есть ли переменная на добавление
	if (isset($_POST['login'])) { //Если новое имя предано, то добавляем
		
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);   
        $sql = "INSERT INTO users (fio, login, password, head) VALUES (?,?,?,?)";
        $stmt = mysqli_prepare($conn, $sql);
        $stmt->bind_param("sssi", $_POST['fio'], $_POST['login'], $hash, $_POST['head']);
        $stmt->execute();
        header("Location: profile.php");     
    }
 }

if (isset($_GET['edit_id'])) { //Проверяем, передана ли переменная на редактирования
    if (isset($_POST['fio'])) {
        if ($_POST['password'] != NULL)
        {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);  
            $sql = "UPDATE users SET fio=?, login=?, password=?, head=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            $stmt->bind_param("sssii", $_POST['fio'], $_POST['login'], $hash, $_POST['head'], $_GET['edit_id']);
            $stmt->execute();
            header("Location: profile.php");
        }
        else
        {
            $sql = "UPDATE users SET fio=?, login=?, head=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            $stmt->bind_param("ssii", $_POST['fio'], $_POST['login'], $_POST['head'], $_GET['edit_id']);
            $stmt->execute();
            header("Location: profile.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/jquery-3.2.1.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
    <link rel="stylesheet" type="text/css" href="DataTables/DataTables-1.10.20/css/jquery.dataTables.css">
    <script type="text/javascript" src="DataTables/DataTables-1.10.20/js/jquery.dataTables.js"></script>
    <meta charset="UTF-8">
    <title>Мой профиль</title>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                "language": {
                    "url": "DataTables/DataTables-1.10.20/plug-ins/Russian.json"
                }
            });
        });

    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">ToDo list</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            </ul>
            <ul class="navbar-nav navbar-right">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo $_SESSION['username']; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="profile.php">Мой профиль</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Разлогиниться</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid">
        <table id="myTable" class="display">
            <thead>
                <tr>
                    <th>№</th>
                    <th>ФИО</th>
                    <th>Логин</th>
                    <th>Руководитель</th>
                </tr>
            </thead>
            <tbody>
                <?php
                   $sql = "SELECT tbl1.id, tbl1.fio, tbl1.login, tbl2.fio as head FROM users AS tbl1 LEFT JOIN users AS tbl2 ON tbl2.id = tbl1.head";
                   $stmt = mysqli_prepare($conn, $sql);
                   $stmt->execute();
                   $result = $stmt->get_result();
                   $a=0;
                   while($row = $result->fetch_assoc())
                   {
                       echo "<tr>
                            <td>".++$a."</td>
                            <td><a href = '?edit_id=".$row['id']."'>".$row['fio']."</a></td>
                            <td>".$row['login']."</td>
                            <td>".$row['head']."</td>
                       </tr>";
                   }
               ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary" data-toggle="modal" data-target="#ModalAdd">Добавить нового пользователя</button>
    </div>

    <?php
        $conn = new mysqli("server", "login", "password", "db_name");;
  if (isset($_GET['edit_id'])) { //Если передана переменная на редактирование
          $sql = ("SELECT tbl1.id, tbl1.fio, tbl1.login, tbl2.fio as head, tbl1.head as head_id FROM users AS tbl1 LEFT JOIN users AS tbl2 ON tbl2.id = tbl1.head
                    WHERE tbl1.id = ?");
          $stmt = mysqli_prepare($conn, $sql);
          $stmt->bind_param("i", $_GET['edit_id']);
          $stmt->execute();
          $result = $stmt->get_result();
          $rowedit = $result->fetch_array(MYSQLI_ASSOC);
    ?>
    <script>
        $(document).ready(function() {
            $("#ModalEdit").modal('show');
        });

    </script>

    <?php     
		}
	?>

    <!-- Modal Add-->
    <div class="modal fade" id="ModalAdd" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Добаление пользователя</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="?add=1" method="POST">
                        <div class="form-group">
                            <label>ФИО</label>
                            <input type="text" class="form-control" name="fio" placeholder="ФИО">
                        </div>
                        <div class="form-group">
                            <label>Логин</label>
                            <input type="text" class="form-control" name="login" placeholder="Логин">
                        </div>
                        <div class="form-group">
                            <label>Пароль</label>
                            <input type="password" class="form-control" name="password" placeholder="Пароль">
                        </div>
                        <div class="form-group">
                            <label>Руководитель</label>
                            <select class="form-control" name="head">
                                <?php
                                    $sql = "SELECT id, fio FROM users";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                        while($row = $result->fetch_assoc()) {
                                            echo "<option value=".$row["id"].">".$row["fio"]."</option>";
                                        } 
                                    ?>
                            </select>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Edit-->
    <div class="modal fade" id="ModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Редактирование пользователя</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label>ФИО</label>
                            <input type="text" class="form-control" name="fio" value="<?php echo $rowedit['fio']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Логин</label>
                            <input type="text" class="form-control" name="login" value="<?php echo $rowedit['login']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Пароль</label>
                            <input type="password" class="form-control" name="password" placeholder="Оставьте поле пустым, чтобы сохранить текущий пароль">
                        </div>
                        <div class="form-group">
                            <label>Руководитель</label>
                            <select class="form-control" name="head">
                                <?php
                                    $sql = "SELECT id, fio FROM users";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                        while($row = $result->fetch_assoc()) {
                                            if ($row['id'] == $rowedit['head_id'])
                                            {
                                               echo "<option value=".$row['id']." selected>".$row['fio']."</option>"; 
                                            }
                                            else
                                            {
                                               echo "<option value=".$row['id'].">".$row['fio']."</option>";
                                            }
                                        } 
                                    ?>
                            </select>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
