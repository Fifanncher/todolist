<?php
session_start();
if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
} 
// Create connection
$conn = new mysqli("server", "login", "password", "db_name");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['add'])) { //проверяем, есть ли переменная на добавление
	if (isset($_POST['header'])) { //Если новое имя предано, то добавляем
        $sql = "INSERT INTO tasks (header, description,	date_end, date_create, priority, status, creator, responsible) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        $date_create = date("Y-m-d H:i:s");
        $stmt->bind_param("ssssssii", $_POST['header'], $_POST['description'], $_POST['date_end'], $date_create, $_POST['priority'], $_POST['status'], $_SESSION['userid'], $_POST['responsible']);     
        $stmt->execute();
        header("Location: index.php");
		}      
	}

if (isset($_GET['edit_id'])) { //Проверяем, передана ли переменная на редактирования
    if (isset($_POST['header'])) {
        if (isset($_POST['responsible']))
        {
            $sql = "UPDATE tasks SET header=?, description=?, date_end=?, date_update=?, priority=?, status=?, responsible=?
            WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            $date_update = date("Y-m-d H:i:s");
            $stmt->bind_param("ssssiiii", $_POST['header'], $_POST['description'], $_POST['date_end'], $date_update, $_POST['priority'], $_POST['status'], $_POST['responsible'], $_GET['edit_id']);
            $stmt->execute();
            header("Location: index.php");
        }
        else
        {
            $sql = "UPDATE tasks SET date_update=?, status=?
            WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            $date_update = date("Y-m-d H:i:s");
            $stmt->bind_param("sii", $date_update, $_POST['status'], $_GET['edit_id']);
            $stmt->execute();
            header("Location: index.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/jquery-3.2.1.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
    <link rel="stylesheet" type="text/css" href="DataTables/DataTables-1.10.20/css/jquery.dataTables.css">
    <script type="text/javascript" src="DataTables/DataTables-1.10.20/js/jquery.dataTables.js"></script>

    <title>ToDo list</title>
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
                       <?php if ($_SESSION['userid'] == 1)
                        {
                           echo "<a class='dropdown-item' href='profile.php'>Мой профиль</a>
                            <div class='dropdown-divider'></div>";
                        }
                        ?>
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
                    <th>Заголовок</th>
                    <th>Приоритет</th>
                    <th>Дата окончания</th>
                    <th>Ответственный</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $query = "SELECT tasks.id, header, date_end, task_priority.priority, task_status.status, users.fio 
                    FROM tasks
                     INNER JOIN task_status ON task_status.id = tasks.status
			         INNER JOIN task_priority ON task_priority.id = tasks.priority
                     INNER JOIN users ON users.id = tasks.responsible
                     WHERE (creator = ? OR responsible = ?)
                     ORDER BY id";
                    $stmt = mysqli_prepare($conn, $query);
                    $stmt->bind_param("ii", $_SESSION['userid'], $_SESSION['userid']);
                    $stmt->execute();
                    $result = $stmt->get_result() or die($conn->error);
                    $a=0;
                    while($row = $result->fetch_assoc())
                    {
                        $date_now = date("Y-m-d H:i:s");
                        if($row['status'] == "Выполнена")
                        {   
                            $color = "green";        
                        }
                        elseif ($row['date_end'] < $date_now)
                        {
                            $color = "red";
                        }
                        else
                        {
                            $color = "DimGrey";
                        }
                     echo "<tr>
                        <td>".++$a."</td>
                        <td><a href = '?edit_id=".$row['id']."' style='color:".$color."'>".$row['header']."</a></td>
                        <td>".$row['priority']."</td>
                        <td>".date("d.m.Y\ H:i", strtotime($row['date_end']))."</td>
                        <td>".$row['fio']."</td>
                        <td>".$row['status']."</td>
                    </tr>";
                    }
                ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary" data-toggle="modal" data-target="#ModalAdd">Добавить запись</button>


        <?php
        $conn = new mysqli("server", "login", "password", "db_name");
  if (isset($_GET['edit_id'])) { //Если передана переменная на редактирование
          $sql = ("SELECT tasks.id, header, description, date_create, date_update, date_end, priority, status, responsible, users.id as userid, users.fio as creator 
                    FROM tasks 
                    INNER JOIN users on users.id = creator 
                    WHERE tasks.id = ?");
          $stmt = mysqli_prepare($conn, $sql);
      
          $stmt->bind_param("i", $_GET['edit_id']);
          $stmt->execute();
          $result = $stmt->get_result();
          $rowedit = $result->fetch_array(MYSQLI_ASSOC);
          $iscreator = ($rowedit['userid'] == $_SESSION['userid']);
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
                        <h5 class="modal-title" id="exampleModalLabel">Добавить задачу</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="?add=1" method="POST">
                            <div class="form-group">
                                <label>Заголовок</label>
                                <input type="text" class="form-control" name="header" placeholder="Заголовок">
                            </div>
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea class="form-control" name="description" placeholder="Описание"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Дата окончания</label>
                                <input type="datetime-local" class="form-control" name="date_end" placeholder="Дата окончания">
                            </div>
                            <div class="form-group">
                                <label>Приоритет</label>
                                <select class="form-control" name="priority">
                                    <option value="1">Высокий</option>
                                    <option value="2">Средний</option>
                                    <option value="3">Низкий</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Статус</label>
                                <select class="form-control" name="status">
                                    <option value="1">К выполнению</option>
                                    <option value="2">Выполняется</option>
                                    <option value="3">Выполнена</option>
                                    <option value="4">Отменена</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ответственный</label>
                                <select class="form-control" name="responsible">
                                    <?php
                                    $sql = "SELECT id, fio FROM users WHERE (id=? or head=?)";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    $stmt->bind_param("ii", $_SESSION['userid'], $_SESSION['userid']);
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
                        <h5 class="modal-title" id="exampleModalLabel">Атрибуты задачи</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Заголовок</label>
                                <input type="text" class="form-control" name="header" <?php if (!$iscreator) {echo "readonly";}?> value="<?php echo $rowedit['header']; ?>">
                            </div>
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea class="form-control" name="description" <?php if (!$iscreator) {echo "readonly";}?>><?php echo $rowedit['description']; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Дата окончания</label>
                                <input type="datetime-local" class="form-control" name="date_end" <?php if (!$iscreator) {echo "readonly";}?> value="<?php echo date("Y-m-d\TH:i:s", strtotime($rowedit['date_end'])); ?>">
                            </div>
                            <div class="form-group">
                                <label>Дата создания</label>
                                <input type="text" class="form-control" name="date_create" readonly value="<?php echo date("d.m.Y\ H:i", strtotime($rowedit['date_create'])); ?>">
                            </div>
                            <div class="form-group">
                                <label>Дата обновления</label>
                                <input type="text" class="form-control" name="date_update" readonly value="<?php if($rowedit['date_update'] != NULL) {echo date("d.m.Y\ H:i", strtotime($rowedit['date_update']));} ?>">
                            </div>
                            <div class="form-group">
                                <label>Приоритет</label>
                                <select class="form-control" name="priority" <?php if (!$iscreator) {echo 'disabled="true"';}?>>
                                    <option value="1" <?php if($rowedit['priority'] == 1){echo "selected";} ?>>Высокий</option>
                                    <option value="2" <?php if($rowedit['priority'] == 2){echo "selected";} ?>>Средний</option>
                                    <option value="3" <?php if($rowedit['priority'] == 3){echo "selected";} ?>>Низкий</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Статус</label>
                                <select class="form-control" name="status">
                                    <option value="1" <?php if($rowedit['status'] == 1){echo "selected";} ?>>К выполнению</option>
                                    <option value="2" <?php if($rowedit['status'] == 2){echo "selected";} ?>>Выполняется</option>
                                    <option value="3" <?php if($rowedit['status'] == 3){echo "selected";} ?>>Выполнена</option>
                                    <option value="4" <?php if($rowedit['status'] == 4){echo "selected";} ?>>Отменена</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Создатель</label>
                                <input type="text" class="form-control" name="creator" readonly value="<?php echo $rowedit['creator']; ?>">
                            </div>
                            <div class="form-group">
                                <label>Ответственный</label>
                                <select class="form-control" name="responsible" <?php if (!$iscreator) {echo 'disabled="true"';}?>>
                                    <?php
                                    $sql = "SELECT id, fio FROM users WHERE (id=? or head=?)";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    $stmt->bind_param("ii", $_SESSION['userid'], $_SESSION['userid']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                        while($row = $result->fetch_assoc()) {
                                            if ($row['id'] == $rowedit['responsible'])
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
    </div>
</body>

</html>
