<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="assets/css/planning.css">

	<title>Planning</title>
	<style>
		.profile-menu {
			position: absolute;
			top: 60px;
			right: 10px;
			background-color: #fff;
			border: 1px solid #ccc;
			border-radius: 15px;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
			display: none;
			z-index: 9999;
			padding: 10px 0;
			max-height: 0;
			overflow: hidden;
			margin-right: 5px;
			transition: max-height 0.3s ease, padding 0.3s ease;
		}

		/* @media screen and (max-width: 768px) {
			.container {
				flex-direction: column;
			}
		}
	 */
		.profile-menu ul {
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.profile-menu ul li {
			padding: 10px 20px;
			display: flex;
			align-items: center;
			/* Centrer verticalement */
			margin-right: 10px;
		}

		.profile-menu ul li i {
			margin-right: 20px;
			/* Espace entre l'icône et le texte */
		}

		.profile-menu ul li:hover {
			background-color: #f0f0f0;
			cursor: pointer;
		}

		.profile-menu.active {
			display: block;
			max-height: 300px;
			padding: 10px 0;
			/* Ajustement du padding */
		}

		.container {
			display: flex;
			justify-content: space-between;
		}

		/* @media screen and (max-width: 576px) {
			.container {
				flex-direction: column;
			}
		} */
		@media screen and (max-width: 1085px) {
			.container {
				/* display: flex; */
				flex-direction: column;
			}
		}
	</style>
</head>

<body>


	<!-- SIDEBAR -->
	<section id="sidebar" class="hide">
		<a href="#" class="brand">
			<i class='bx bx-grid-alt'></i>
			<span class="text">Admin</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="dashboard.php">
					<!-- <i class='bx bxs-dashboard' ></i> -->
					<i class='bx bx-stats'></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="enseignant.php">
					<i class='bx bx-user'></i>
					<span class="text">Enseignants</span>
				</a>
			</li>
			<li>
				<a href="etudiant.php">
					<i class='bx bxs-group'></i>
					<span class="text">Etudiants</span>
				</a>
			</li>
			<li>
				<a href="salle.php">
					<i class='bx bxs-school'></i>
					<span class="text">Salles</span>
				</a>
			</li>
			<li>
				<a href="module.php">
					<i class='bx bx-file'></i>
					<span class="text">Modules</span>
				</a>
			</li>
			<li>
				<a href="notifAdmin.php">
					<i class='bx bx-message'></i>
					<span class="text">Notifications</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="planning.php">
					<i class='bx bxs-cog'></i>
					<span class="text">Generation Planning</span>
				</a>
			</li>
			<li>
				<a href="session.php?logout=true" class="logout" onclick="return logoutConfirmation()">
					<i class='bx bxs-log-out-circle'></i>
					<span class="text">Déconnexion</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->



	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu'></i>
			<a href="#" class="nav-link">Categories</a>
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell'></i>
				<!-- <span class="num">8</span> -->
			</a>
			<a href="#" class="profile" onclick="toggleProfileMenu()">
				<i class='bx bx-user'></i>
			</a>
			<div class="profile-menu" id="profileMenu">
				<ul>
					<li>
						<i class='bx bx-home'></i>
						<a href="dashboard.php"> Dashboard</a>
					</li>
					<li>
						<i class='bx bx-user'></i>
						<a href="profil.php"> Mon Profil</a>
					</li>
					<li>
						<i class='bx bx-edit'></i>
						<a href="profil.php"> Edit Profile</a>
					</li>
					<li>
						<i class='bx bx-log-out'></i>
						<a href="#" onclick="logoutConfirmation()"> Logout</a>
					</li>
				</ul>
			</div>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<section id="dash">
				<div class="head-title">
					<div class="left">
						<h1>Dashboard</h1>
						<ul class="breadcrumb">
							<li>
								<a href="#">Dashboard</a>
							</li>
							<li><i class='bx bx-chevron-right'></i></li>
							<li>
								<a class="active" href="#">Planning</a>
							</li>
						</ul>
					</div>

				</div>





				<!-- Dashboard content -->
				<h1>Génération du planning d'examens</h1>
				<form>
					<label for="specialite">Spécialité :</label>
					<select name="specialite" id="specialite">
					</select><br><br>

					<label for="periode">Période des examens :</label>
					<input type="text" id="periode" name="periode" placeholder="Format : YYYY-MM-DD à YYYY-MM-DD"><br><br>

					<input type="submit" name="submit" value="Générer le planning">
				</form>





				<div class="table-data">
					<div class="order">
						<div class="head">
							<h3>Generation de planning</h3>
							<i class='bx bx-filter'></i>
						</div>
					</div>
				</div>
			</section>

		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<script>
		function toggleProfileMenu() {
			var profileMenu = document.getElementById('profileMenu');
			profileMenu.classList.toggle('active');
		}

		function logoutConfirmation() {
			if (confirm("Êtes-vous sûr de vouloir vous déconnecter ?")) {
				return true; // Si l'utilisateur confirme, la déconnexion se produit
			} else {
				return false; // Si l'utilisateur annule, la déconnexion est annulée
			}
		}
	</script>

	<script src="assets/js/planning.js"></script>
</body>

</html>