<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit();
}
// Récupérer le contenu existant pour la page d'accueil
$query_home_content = "SELECT * FROM home_content";
$result_home_content = $conn->query($query_home_content);
$home_data = [];
while ($row = $result_home_content->fetch_assoc()) {
    $home_data[$row['section_name']] = $row['content'];

    // Also load the new competence fields.
    $home_data['first_item1_icon'] = $row['first_item1_icon'];
    $home_data['first_item1_title'] = $row['first_item1_title'];
    $home_data['first_item1_text'] = $row['first_item1_text'];
    $home_data['first_item2_icon'] = $row['first_item2_icon'];
    $home_data['first_item2_title'] = $row['first_item2_title'];
    $home_data['first_item2_text'] = $row['first_item2_text'];
    $home_data['first_item3_icon'] = $row['first_item3_icon'];
    $home_data['first_item3_title'] = $row['first_item3_title'];
    $home_data['first_item3_text'] = $row['first_item3_text'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        $sections = [
            'header_title', 'header_subtitle', 'intro_title', 'intro_text',
            'first_title',
            'first_item1_icon', 'first_item1_title', 'first_item1_text',
            'first_item2_icon', 'first_item2_title', 'first_item2_text',
            'first_item3_icon', 'first_item3_title', 'first_item3_text',
            'second_title', 'second_text', 'second_content',
            'second_button_text', 'second_button_link', 'cta_title', 'cta_text',
            'about_title', 'about_text', 'projects_title', 'gallery_title'
        ];

        for ($i = 1; $i <= 5; $i++) {
            $sections[] = "second_stat{$i}_icon";
            $sections[] = "second_stat{$i}_number";
            $sections[] = "second_stat{$i}_label";
        }

        // Update the new columns too.
        $sections[] = "first_item1_icon";
        $sections[] = "first_item1_title";
        $sections[] = "first_item1_text";
        $sections[] = "first_item2_icon";
        $sections[] = "first_item2_title";
        $sections[] = "first_item2_text";
        $sections[] = "first_item3_icon";
        $sections[] = "first_item3_title";
        $sections[] = "first_item3_text";

        $stmt = $conn->prepare("UPDATE home_content SET content = ? WHERE section_name = ?");

        foreach ($sections as $section) {
            if (isset($_POST[$section])) { // Check if the post variable is set
                $value = $_POST[$section];
                $stmt->bind_param('ss', $value, $section);

                if (!$stmt->execute()) {
                    throw new Exception("Erreur lors de la mise à jour de $section: " . $stmt->error);
                }
            }

        }

        $conn->commit();
        $_SESSION['message'] = "Mise à jour réussie !";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
        header('Location: dashboard.php');
        exit();
    }
}
?>
