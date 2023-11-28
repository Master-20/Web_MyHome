<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ТСЖ "Наш Дом"</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="IMAGES/icons8-real-estate-96.png" type="image/x-icon">
    <style>
        body {
            background-color: #f2f2f2;
        }
        .container {
            background-color: #fff;
            padding: 17px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php
    // Подключение к базе данных PostgreSQL
    try {
        $pdo = new PDO("pgsql:host='localhost';port=5434;dbname='postgres';", "postgres", "postgres", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
    session_start();

    // Подготовка к заполнению поля "Имя" в форме записи на обслуживание
    if (isset($_SESSION['userid'])) {
	$stmt = $pdo->prepare("SELECT Firstname, Lastname FROM User_data WHERE User_id = ?");
        $stmt->execute([$_SESSION['userid']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($result) {
            $firstname = trim($result['firstname']);
	    $lastname = trim($result['lastname']);
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Обработка формы входа
	if (isset($_POST["loginUserid"]) && isset($_POST["loginPassword"])) {
	    // Получение данных из формы
            $userid = $_POST["loginUserid"];
            $password = $_POST["loginPassword"];

            // Проверка наличия пользователя в базе данных
            $stmtCheck = $pdo->prepare("SELECT Pass FROM User_data WHERE User_id = ?");
            $stmtCheck->execute([$userid]);
            $hashedPassword = $stmtCheck->fetchColumn();

            if (!$hashedPassword || !password_verify($password, $hashedPassword)) {
                echo "Ошибка: Неверное имя пользователя или пароль.";
                header("refresh: 4;");
                exit;
            }

            // Установка сессии
            session_start();
            $_SESSION["userid"] = $userid;

            // Переход на профиль
            header("Location: profile.php");
            exit;
        }

        // Обработка формы регистрации
        elseif (isset($_POST["userid"]) && isset($_POST["password"])) {
            // Получение данных из формы
            $userid = $_POST["userid"];
            $password = $_POST["password"];

            // Проверка, зарегистрирован ли уже пользователь с таким именем
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM User_data WHERE User_id = ?");
            $stmtCheck->execute([$userid]);
            $userCount = $stmtCheck->fetchColumn();

            if ($userCount > 0) {
                echo "Ошибка: Пользователь с таким идентификатором уже зарегистрирован.";
	        header("refresh: 4;");
	        exit;
            }

            // Хеширование пароля
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Вставка данных в базу данных
            $stmt = $pdo->prepare("INSERT INTO User_data (User_id, Pass) VALUES (?, ?)");
            $stmt->execute([$userid, $hashedPassword]);

            // Установка сессии
            session_start();
            $_SESSION['userid'] = $userid;

            // Переход на профиль
            header("Location: profile.php");
            exit;
        }

	// Обработка формы записи на обслуживание
	elseif (isset($_POST["username"]) && isset($_POST["phone"]) && isset($_POST["details"])) {
	    $username = $_POST["username"];
    	    $email = isset($_POST["email"]) ? $_POST["email"] : null;
    	    $phone = $_POST["phone"];
    	    $details = $_POST["details"];
	    $service_id = $_POST["service_id"];
    	    $user_id = $_SESSION['userid'];

    	    // Вставка данных в таблицу Service_requests
    	    $stmt = $pdo->prepare("INSERT INTO Service_requests (Username, Email, Phone, Details, Service_id, User_id) VALUES (?, ?, ?, ?, ?, ?)");
    	    $stmt->execute([$username, $email, $phone, $details, $service_id, $user_id]);

    	    // Вывод сообщения
    	    if ($user_id) {
        	echo "Ваш запрос успешно отправлен!";
		header("refresh: 4;");
    	    } else {
		echo "Запрос не был отправлен!";
        	header("refresh: 4;");
        	exit;
    	    }	
	}
    }
    ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">
	    <img src="IMAGES/icons8-real-estate-96.png" width="29" height="29" class="d-inline-block align-top" alt="Icon">ТСЖ "Наш Дом"
	</a>
  	<div class="navbar-collapse justify-content-end">
    	    <ul class="navbar-nav">
		<?php if (isset($_SESSION['userid'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Выйти</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Личный кабинет</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Собственник №<?php echo $_SESSION['userid']; ?></span>
                    </li>
                <?php else: ?>
      	    	    <li class="nav-item">
       		        <a class="nav-link" href="#" data-toggle="modal" data-target="#loginModal">Вход</a>
      		    </li>
      		    <li class="nav-item">
        	        <a class="nav-link" href="#" data-toggle="modal" data-target="#registerModal">Регистрация</a>
      		    </li>
		<?php endif; ?>
    	    </ul>
  	</div>
    </nav>
    <div class="container">
        <h1 class="text-center">Товарищество собственников жилья "Наш Дом"</h1>
        <p>Добро пожаловать на сайт нашего Товарищества собственников жилья. У нас здесь вы найдете полезную информацию о наших домах, услугах, рабочих, и многое другое.</p>
        <h2>Наши услуги</h2>
        <div id="servicesCarousel" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="card">
                                <img src="IMAGES/1.jpeg" class="card-img-top" alt="Услуга 1">
                                <div class="card-body">
                                    <h5 class="card-title">Обслуживание и ремонт</h5>
                                    <p class="card-text">Исполнитель: Иван Петров</p>
                                    <p class="card-text">Описание: Регулярное обслуживание и ремонт домов</p>
                                    <p class="card-text">Стоимость: 1000 руб.</p>
                                </div>
				<button type="button" class="service-btn btn btn-primary" data-service-id="1" data-toggle="modal" data-target="#appointmentModal">Записаться</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <img src="IMAGES/2.jpg" class="card-img-top" alt="Услуга 2">
                                <div class="card-body">
                                    <h5 class="card-title">Благоустройство территории</h5>
                                    <p class="card-text">Исполнитель: Елена Сидорова</p>
                                    <p class "card-text">Описание: Уборка и уход за территорией ЖК</p>
                                    <p class="card-text">Стоимость: 800 руб.</p>
                                </div>
				<button type="button" class="service-btn btn btn-primary" data-service-id="2" data-toggle="modal" data-target="#appointmentModal">Записаться</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="card">
                                <img src="IMAGES/3.jpg" class="card-img-top" alt="Услуга 3">
                                <div class="card-body">
                                    <h5 class="card-title">Электрические работы</h5>
                                    <p class="card-text">Исполнитель: Алексей Иванов</p>
                                    <p class="card-text">Описание: Ремонт и обслуживание электрооборудования</p>
                                    <p class="card-text">Стоимость: 500 руб.</p>
                                </div>
				<button type="button" class="service-btn btn btn-primary" data-service-id="3" data-toggle="modal" data-target="#appointmentModal">Записаться</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <img src="IMAGES/4.jpg" class="card-img-top" alt="Услуга 4">
                                <div class="card-body">
                                    <h5 class="card-title">Сантехнические работы</h5>
                                    <p class="card-text">Исполнитель: Мария Козлова</p>
                                    <p class="card-text">Описание: Устранение поломок и обслуживание водопровода</p>
                                    <p class="card-text">Стоимость: 600 руб.</p>
                                </div>
				<button type="button" class="service-btn btn btn-primary" data-service-id="4" data-toggle="modal" data-target="#appointmentModal">Записаться</button>
                            </div>
                        </div>
                    </div>
                </div>
		<div class="carousel-item">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="card">
                                <img src="IMAGES/5.jpeg" class="card-img-top" alt="Услуга 5">
                                <div class="card-body">
                                    <h5 class="card-title">Кровельные работы</h5>
                                    <p class="card-text">Исполнитель: Петр Смирнов</p>
                                    <p class="card-text">Описание: Организация работ по ремонту и обслуживанию кровли</p>
                                    <p class="card-text">Стоимость: 700 руб.</p>
                                </div>
				<button type="button" class="service-btn btn btn-primary" data-service-id="5" data-toggle="modal" data-target="#appointmentModal">Записаться</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <img src="IMAGES/6.jpg" class="card-img-top" alt="Услуга 6">
                                <div class="card-body">
                                    <h5 class="card-title">Безопасность и видеонаблюдение</h5>
                                    <p class="card-text">Исполнитель: Наталья Иванова</p>
                                    <p class="card-text">Описание: Безопасность и видеонаблюдение на территории ЖК</p>
                                    <p class="card-text">Стоимость: 900 руб.</p>
                                </div>
				<button type="button" class="service-btn btn btn-primary" data-service-id="6" data-toggle="modal" data-target="#appointmentModal">Записаться</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#servicesCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#servicesCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
        <h2>Наши дома</h2>
        <p>Мы управляем несколькими жилыми комплексами в нашем районе. Наши дома всегда в отличном состоянии благодаря нашим рабочим и регулярному обслуживанию.</p>
	<div id="housesCarousel" class="carousel slide" data-ride="carousel">
    	    <div class="carousel-inner">
        	<div class="carousel-item active">
            	    <div class="row">
                	<div class="col-md-6">
                    	    <div class="card">
                        	<img src="IMAGES/admiral.jpg" class="card-img-top" alt="ЖК Адмиралтейский">
                                <div class="card-body">
                                    <h5 class="card-title text-center">ЖК Адмиралтейский</h5>
                                </div>
                    	    </div>
                	</div>
                	<div class="col-md-6">
                    	    <div class="card">
                        	<img src="IMAGES/univer.jpg" class="card-img-top" alt="ЖК Университетский">
                                <div class="card-body">
                                    <h5 class="card-title text-center">ЖК Университетский</h5>
                                </div>
                    	    </div>
                	</div>
            	    </div>
        	</div>
        	<div class="carousel-item">
            	    <div class="row">
                	<div class="col-md-6">
                    	    <div class="card">
                        	<img src="IMAGES/stalin.jpg" class="card-img-top" alt="ЖК Сталинградский">
                                <div class="card-body">
                                    <h5 class="card-title text-center">ЖК Сталинградский</h5>
                                </div>
                    	    </div>
                	</div>
                	<div class="col-md-6">
                    	    <div class="card">
                        	<img src="IMAGES/kraya.jpg" class="card-img-top" alt="ЖК Теплые края">
                                <div class="card-body">
                                    <h5 class="card-title text-center">ЖК Теплые края</h5>
                                </div>
                    	    </div>
                	</div>
            	    </div>
        	</div>
    	    </div>
    	    <a class="carousel-control-prev" href="#housesCarousel" role="button" data-slide="prev">
        	<span class="carousel-control-prev-icon" aria-hidden="true"></span>
        	<span class="sr-only">Previous</span>
    	    </a>
    	    <a class="carousel-control-next" href="#housesCarousel" role="button" data-slide="next">
        	<span class="carousel-control-next-icon" aria-hidden="true"></span>
        	<span class="sr-only">Next</span>
    	    </a>
	</div>
        <h2>Наши жильцы</h2>
        <p>Наши жильцы - это наша гордость. Мы стремимся создать комфортное и безопасное место для всех, кто живет в наших домах.</p>
        <h2>Контакты</h2>
        <address>
            <strong>ТСЖ "Наш Дом"</strong><br>
            Адрес: ул. Примерная, 123<br>
            Телефон: +7 (123) 456-7890<br>
            Email: info@nash-dom-tsgj.ru
        </address>
    </div>
    <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Запись на обслуживание</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="username">Ваше имя:</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_SESSION['userid']) ? $firstname.' '.$lastname : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="phone">Телефон:</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="details">Детали обслуживания:</label>
                            <textarea class="form-control" id="details" name="details" required></textarea>
                        </div>
			<input type="hidden" id="service_id" name="service_id" value="">
			<div class="modal-footer">
                    	    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
			    <?php if (isset($_SESSION['userid'])): ?>
                    	        <button type="submit" class="btn btn-primary">Отправить запрос</button>
			    <?php else: ?>
				<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#registerModal">Отправить запрос</button>
			    <?php endif; ?>
                	</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Регистрация</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" onsubmit="return validateForm()">
			<div class="form-group">
    			    <label for="userid">Ваш уникальный номер собственника:</label>
    			    <input type="text" class="form-control" id="userid" name="userid" maxlength="10" required>
			</div>
			<div class="form-group">
    			    <label for="password">Пароль:</label>
    			    <input type="password" class="form-control" id="password" name="password" maxlength="45" required>
			</div>
			<div class="form-group">
            		    <label for="confirmPassword">Повторите пароль:</label>
            		    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
        		</div>
			<div class="modal-footer">
			    <button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#loginModal">Есть аккаунт?</button>
    			    <button type="submit" class="btn btn-success" value="Зарегистрироваться">Зарегистрироваться</button>
			</div>
		    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Вход</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
			<div class="form-group">
    			    <label for="loginUserid">Ваш уникальный номер собственника:</label>
    			    <input type="text" class="form-control" id="loginUserid" name="loginUserid" maxlength="10" required>
			</div>
			<div class="form-group">
    			    <label for="loginPassword">Пароль:</label>
    			    <input type="password" class="form-control" id="loginPassword" name="loginPassword" maxlength="45" required>
			</div>
			<div class="modal-footer">
			    <button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#registerModal">Нет аккаунта?</button>
    			    <button type="submit" class="btn btn-success" value="Войти">Войти</button>
			</div>
		    </form>
                </div>
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
	$(document).ready(function() {
    	// Обработчик события клика по кнопке услуги
    	    $(".service-btn").click(function() {
        	// Устанавливаем значение data-service-id из кнопки в скрытое поле формы
        	$("#service_id").val($(this).data("service-id"));
    	    });
	});

        function validateForm() {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            // Валидация паролей
            if (!validatePassword(password)) {
                showErrorModal('Пароль не соответствует требованиям: длиннее 6 символов, содержит большие и маленькие латинские буквы, спецсимволы (знаки препинания, арифметические действия и т.п.), пробел, дефис, подчеркивание и цифры.');
                return false;
            }

            // Проверка совпадения паролей
            if (password !== confirmPassword) {
                showErrorModal('Пароли не совпадают.');
                return false;
            }

            return true;
        }

        function validatePassword(password) {
            // Регулярное выражение для проверки требований к паролю
            var pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\p{P}\p{S}\p{Z}]).{6,}$/u;
            return pattern.test(password);
        }

        function showErrorModal(message) {
            var errorMessageElement = document.getElementById('errorMessage');
            errorMessageElement.textContent = message;

            $('#errorModal').modal('show');
        }
    </script>
</body>
</html>