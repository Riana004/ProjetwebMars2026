<?php
// Protection simple par session (à coupler avec votre système de login)
session_start();
// if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

// Simulation de récupération d'une page existante (pour le mode édition)
$page_id = $_GET['id'] ?? 1;
$title = "Nouvelle page sur l'Iran";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Backoffice - Éditeur de contenu</title>
    <link rel="stylesheet" href="//unpkg.com/grapesjs/dist/css/grapes.min.css">
    <script src="//unpkg.com/grapesjs"></script>
    <style> 
        body, html { height: 100%; margin: 0; overflow: hidden; }
        #gjs { border: 3px solid #444; }
        .panel__top { padding: 10px; background: #2c3e50; color: white; display: flex; justify-content: space-between; }
        .save-btn { background: #27ae60; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        .save-btn:hover { background: #2ecc71; }
    </style>
</head>
<body>

    <div class="panel__top">
        <div>
            <strong>Backoffice</strong> | 
            Titre : <input type="text" id="page-title" value="<?= htmlspecialchars($title) ?>" style="padding: 5px;">
        </div>
        <button class="save-btn" onclick="saveContent()">Enregistrer la page</button>
    </div>

    <div id="gjs">
        <h1>Guerre en Iran</h1>
        <p>Commencez à rédiger votre article d'information ici...</p>
    </div>

    <script type="text/javascript">
        // Initialisation de GrapesJS
        const editor = grapesjs.init({
            container: '#gjs',
            fromElement: true,
            height: '90vh',
            width: 'auto',
            storageManager: false, // On gère le stockage nous-mêmes via l'API
            blockManager: {
                appendTo: '#blocks',
                blocks: [
                    { id: 'section', label: '<b>Section</b>', attributes: { class: 'gjs-block-section' }, content: '<section><h1>Titre H1</h1><p>Texte de la section</p></section>' },
                    { id: 'text', label: 'Texte', content: '<div data-gjs-type="text">Insérez votre texte ici</div>' },
                    { id: 'image', label: 'Image', select: true, content: { type: 'image' }, activate: true }
                ]
            }
        });

        // Fonction pour envoyer les données au serveur PHP (save_page.php)
        async function saveContent() {
            const htmlContent = editor.getHtml();
            const cssContent = editor.getCss();
            const title = document.getElementById('page-title').value;

            // Fusion du HTML et CSS pour le stockage
            const fullContent = `<style>${cssContent}</style>${htmlContent}`;

            try {
                const response = await fetch('save_page.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: title,
                        content: fullContent,
                        slug: title.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '')
                    })
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert('Page enregistrée avec succès !');
                } else {
                    alert('Erreur lors de l\'enregistrement.');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion au serveur.');
            }
        }
    </script>
</body>
</html>