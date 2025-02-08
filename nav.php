<html>
	<head>
		<title>Stellar by HTML5 UP</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="shortcut icon" href="images/favicon.png" type="images/png">
		<script src="script.js"></script>
		<noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
	</head>
	<body>
		<!-- Nav -->
		<nav id="nav">
			<ul>
				<?php
				// Connexion à la base de données
				$conn = new mysqli('mysql-badiane.alwaysdata.net', 'badiane_13', 'faloudu95**', 'badiane_site');

				// Vérification de la connexion
				if ($conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				}

				// Récupération des éléments du menu
				$sql = "SELECT title, link FROM menu_items ORDER BY order_num";
				$result = $conn->query($sql);

				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						echo '<li><a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['title']) . '</a></li>';
					}
				}

				$conn->close();
				?>
			</ul>
		</nav>
	</body>
</html>
