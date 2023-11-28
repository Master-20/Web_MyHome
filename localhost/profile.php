<?php
session_start();
// Проверка авторизации пользователя
if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit;
}

// Подключение к базе данных PostgreSQL
try {
    $pdo = new PDO("pgsql:host='localhost';port=5434;dbname='postgres';", "postgres", "postgres", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Обработка данных из формы редактирования
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["changeData"])) {
        $surname = !empty($_POST["surname"]) ? $_POST["surname"] : null;
        $firstname = !empty($_POST["firstname"]) ? $_POST["firstname"] : null;
        $lastname = !empty($_POST["lastname"]) ? $_POST["lastname"] : null;
        $birth_date = !empty($_POST["birth_date"]) ? $_POST["birth_date"] : null;
        $address = !empty($_POST["address"]) ? $_POST["address"] : null;
        $gender = !empty($_POST["gender"]) ? $_POST["gender"] : null;
        $interests = !empty($_POST["interests"]) ? $_POST["interests"] : null;
        $vk = !empty($_POST["vk"]) ? $_POST["vk"] : null;
        $blood_type = !empty($_POST["blood_type"]) ? $_POST["blood_type"] : null;
        $rhFactor = !empty($_POST["rh_factor"]) ? $_POST["rh_factor"] : null;

        // Сохранение данных в базу данных
        $stmt = $pdo->prepare("UPDATE user_data SET Surname=?, Firstname=?, Lastname=?, Birth_date=?, Address=?, Gender=?, Interests=?, Vk=?, Blood_type=?, Rh_factor=? WHERE User_id=?");
        $stmt->execute([$surname, $firstname, $lastname, $birth_date, $address, $gender, $interests, $vk, $blood_type, $rhFactor, $_SESSION['userid']]);

    } elseif (isset($_POST["changePassword"])) {
        // Изменение пароля, если введены новые значения
        $newPassword = $_POST["newPassword"];
        $confirmPassword = $_POST["confirmPassword"];
    
        if (!empty($newPassword) && $newPassword == $confirmPassword) {
            // Хеширование нового пароля
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Обновление пароля в базе данных
            $stmt = $pdo->prepare("UPDATE User_data SET Pass=? WHERE User_id=?");
            $stmt->execute([$hashedPassword, $_SESSION['userid']]);
	    
	    echo "Пароль успешно изменен!";
	    header("refresh: 4;");
        }
    }
}

// Получение данных о пользователе из базы данных
$stmt = $pdo->prepare("SELECT * FROM User_data WHERE User_id=?");
$stmt->execute([$_SESSION['userid']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("SELECT * FROM Service_requests WHERE User_id=?");
$stmt->execute([$_SESSION['userid']]);
$serviceRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="IMAGES/icons8-real-estate-96.png" type="image/x-icon">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">
	    <img src="IMAGES/icons8-real-estate-96.png" width="29" height="29" class="d-inline-block align-top" alt="Icon">ТСЖ "Наш Дом"
	</a>
  	<div class="navbar-collapse justify-content-end">
    	    <ul class="navbar-nav">
      	    	<li class="nav-item">
       		    <a class="nav-link" href="logout.php">Выйти</a>
      		</li>
      		<li class="nav-item">
        	    <span class="nav-link">Собственник №<?php echo $_SESSION['userid']; ?></span>
      		</li>
    	    </ul>
  	</div>
    </nav>
    <div class="container">
        <h1>Личный кабинет</h1>
        <?php if (!empty($userData['firstname']) && !empty($userData['lastname'])): ?>
            <p>Здравствуйте, <?php echo $userData['firstname']." ".$userData['lastname']; ?>!</p>
        <?php else: ?>
            <p>Здравствуйте, собственник №<?php echo $_SESSION['userid']; ?>!</p>
        <?php endif; ?>
	<div class="row">
	    <!-- Форма для редактирования данных пользователя -->
	    <div class="col-md-6">
		<h2>Редактировать данные</h2>
        	<form action="" method="post">
	    	    <div class="form-group">
        		<label for="user_id">Идентификатор собственника:</label>
        		<input type="text" class="form-control" id="user_id" name="user_id" value="<?php echo $userData['user_id']; ?>" readonly>
	    	    </div>
	    	    <div class="form-group">
                	<label for="surname">Фамилия:</label>
                	<input type="text" class="form-control" id="surname" name="surname" value="<?php echo $userData['surname']; ?>">
	    	    </div>
	    	    <div class="form-group">
	        	<label for="firstname">Имя:</label>
                	<input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $userData['firstname']; ?>">
	    	    </div>
	    	    <div class="form-group">
    	        	<label for="lastname">Отчество:</label>
                	<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $userData['lastname']; ?>">
	            </div>
	    	    <div class="form-group">
                	<label for="birth_date">Дата рождения:</label>
                	<input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo $userData['birth_date']; ?>">
	    	    </div>
	    	    <div class="form-group">
                	<label for="address">Адрес:</label>
                	<input type="text" class="form-control" id="address" name="address" value="<?php echo $userData['address']; ?>">
	    	    </div>
	    	    <div class="form-group">
                	<label for="gender">Пол:</label>
                	<select class="form-control" id="gender" name="gender">
                    	    <option value="male" <?php echo ($userData['gender'] == 'Мужской') ? 'selected' : ''; ?>>Мужской</option>
                    	    <option value="female" <?php echo ($userData['gender'] == 'Женский') ? 'selected' : ''; ?>>Женский</option>
                	</select>
	    	    </div>
	    	    <div class="form-group">
                	<label for="interests">Интересы:</label>
                	<textarea class="form-control" id="interests" name="interests"><?php echo $userData['interests']; ?></textarea>
	    	    </div>
	    	    <div class="form-group">
                	<label for="vk">Ссылка на профиль ВК:</label>
                	<input type="text" class="form-control" id="vk" name="vk" value="<?php echo $userData['vk']; ?>">
	    	    </div>
	    	    <div class="form-group">
                	<label for="blood_type">Группа крови:</label>
                	<input type="number" class="form-control" id="blood_type" name="blood_type" min="1" max="4" value="<?php echo $userData['blood_type']; ?>">
	            </div>
	    	    <div class="form-group">
                	<label for="rh_factor">Резус-фактор:</label>
                	<select class="form-control" id="rh_factor" name="rh_factor">
        		    <option value="+" <?php echo ($userData['rh_factor'] == '+') ? 'selected' : ''; ?>>+</option>
        		    <option value="-" <?php echo ($userData['rh_factor'] == '-') ? 'selected' : ''; ?>>-</option>
    		        </select>
	    	    </div>
            	    <button type="submit" class="btn btn-primary" name="changeData">Сохранить изменения</button>
        	</form>
	    </div>
            <div class="col-md-6">
		<!-- Форма изменения пароля -->
                <h2>Изменить пароль</h2>
                <form action="" method="post" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="newPassword">Новый пароль:</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Повторите пароль:</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="changePassword">Изменить пароль</button>
                </form>
		<div style="margin-bottom: 40px;"></div>
		<h2>Ваши обращения:</h2>
        	<?php if (empty($serviceRequests)): ?>
            	    <p>У вас нет обращений.</p>
        	<?php else: ?>
            	    <ul>
                	<?php foreach ($serviceRequests as $request): ?>
                    	    <li>
                        	<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#request<?php echo $request['request_id']; ?>" aria-expanded="false" aria-controls="request<?php echo $request['request_id']; ?>">Обращение №<?php echo $request['request_id']; ?></button>
                        	<div class="collapse" id="request<?php echo $request['request_id']; ?>">
                            	    <div class="card card-body">
                                	<p><strong>Имя:</strong> <?php echo $request['username']; ?></p>
                                	<p><strong>Email:</strong> <?php echo $request['email']; ?></p>
                                	<p><strong>Телефон:</strong> <?php echo $request['phone']; ?></p>
					<p><strong>Услуга:</strong> <?php echo $request['service_id']; ?></p>
                                	<p><strong>Детали обслуживания:</strong> <?php echo $request['details']; ?></p>
                            	    </div>
                        	</div>
                    	    </li>
                	<?php endforeach; ?>
            	    </ul>
        	<?php endif; ?>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="errorModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ошибка</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
            </div>
        </div>
    </div>
    <footer>
	<div class="copyright text-center">
            <p>&copy; 2023, ТСЖ "Наш Дом". Все права защищены.</p>
    	</div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            var newPassword = document.getElementById('newPassword').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            // Валидация паролей
            if (!validatePassword(newPassword)) {
                showErrorModal('Пароль не соответствует требованиям: длиннее 6 символов, содержит большие и маленькие латинские буквы, спецсимволы (знаки препинания, арифметические действия и т.п.), пробел, дефис, подчеркивание и цифры.');
                return false;
            }

            // Проверка совпадения паролей
            if (newPassword !== confirmPassword) {
                showErrorModal('Пароли не совпадают.');
                return false;
            }

            return true;
        }

        function validatePassword(newPassword) {
            // Регулярное выражение для проверки требований к паролю
            var pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\p{P}\p{S}\p{Z}]).{6,}$/u;
            return pattern.test(newPassword);
        }

        function showErrorModal(message) {
            var errorMessageElement = document.getElementById('errorMessage');
            errorMessageElement.textContent = message;

            $('#errorModal').modal('show');
        }
    </script>
</body>
</html>