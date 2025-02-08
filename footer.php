<footer id="footer">
						<section>
							<h2>Aliquam sed mauris</h2>
							<p>Sed lorem ipsum dolor sit amet et nullam consequat feugiat consequat magna adipiscing tempus etiam dolore veroeros. eget dapibus mauris. Cras aliquet, nisl ut viverra sollicitudin, ligula erat egestas velit, vitae tincidunt odio.</p>
							<ul class="actions">
								<li><a href="#" class="button">Learn More</a></li>
							</ul>
						</section>
						<section>
							<h2>Etiam feugiat</h2>
							<dl class="alt">
								<dt>Address</dt>
								<dd>1234 Somewhere Road &bull; Nashville, TN 00000 &bull; USA</dd>
								<dt>Phone</dt>
								<dd>(000) 000-0000 x 0000</dd>
								<dt>Email</dt>
								<dd><a href="#">information@untitled.tld</a></dd>
							</dl><?php
    // Inclut le fichier de connexion à la base de données
    include 'msql.php';

    // Prépare la requête SQL pour sélectionner le nom et le lien des réseaux sociaux
    $sql = "SELECT nom, link FROM social_media";

    // Exécute la requête SQL
    $result = $conn->query($sql);

    // Vérifie si des résultats ont été trouvés
    if ($result->num_rows > 0) {
        // Parcourt chaque ligne de résultat
        while ($row = $result->fetch_assoc()) {
            // Génère un élément de liste pour chaque réseau social
            echo '<li><a href="' . $row['link'] . '" target="_blank" class="icon brands alt fa-' . $row['nom'] . '">
                    <span class="label">' . ucfirst($row['nom']) . '</span>
                  </a></li>';
        }
    } else {
        // Affiche un message si aucun réseau social n'est trouvé
        echo "<li>Aucun réseau social trouvé.</li>";
    }
?>
</section>
						<p class="copyright">&copy; Untitled. Design: <a href="https://html5up.net">HTML5 UP</a>.</p>
					</footer>
