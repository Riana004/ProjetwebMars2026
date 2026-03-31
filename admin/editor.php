<?php
session_start();

if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

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
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: sans-serif;
        }

        /* Structure de la page */
        .app-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .panel__top {
            padding: 10px;
            background: #2c3e50;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-content {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* L'éditeur à gauche */
        #gjs {
            flex: 1;
            border: none;
        }

        /* La barre de blocs à droite (L'élément MANQUANT) */
        #blocks-container {
            width: 250px;
            background: #34495e;
            color: white;
            overflow-y: auto;
            padding: 10px;
        }

        .gjs-block {
            width: 100% !important;
            min-height: 50px !important;
            margin-bottom: 10px !important;
            background-color: #2c3e50 !important;
            color: white !important;
        }

        .save-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
        }

        .save-btn:hover {
            background: #2ecc71;
        }

        .logout-link {
            color: #fff;
            text-decoration: none;
            margin-right: 12px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="app-container">
        <div class="panel__top">
            <div>
                <strong>Backoffice</strong> |
                Titre : <input type="text" id="page-title" value="<?= htmlspecialchars($title) ?>"
                    style="padding: 5px; border-radius: 3px; border: none;">
            </div>
            <div>
                <a class="logout-link" href="index.php?logout=1">Déconnexion</a>
                <button class="save-btn" onclick="saveContent()">Enregistrer la page</button>
            </div>
        </div>

        <div class="main-content">
            <div id="gjs">
                <h1>Guerre en Iran</h1>
                <p>Commencez à rédiger votre article d'information ici...</p>
            </div>

            <div id="blocks-container">
                <h3 style="font-size: 14px; text-align: center;">Éléments à glisser</h3>
                <div id="blocks"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        const editor = grapesjs.init({
            container: '#gjs',
            fromElement: true,
            height: '100%',
            width: 'auto',
            storageManager: false,
            blockManager: {
                appendTo: '#blocks', // C'est ici que les blocs vont s'afficher
                blocks: [
                    {
                        id: 'h1',
                        label: 'Titre H1',
                        category: 'Titres',
                        content: '<h1 data-gjs-type="text">Titre Principal</h1>'
                    },
                    {
                        id: 'h2',
                        label: 'Sous-titre H2',
                        category: 'Titres',
                        content: '<h2 data-gjs-type="text">Sous-titre</h2>'
                    },
                    {
                        id: 'h3',
                        label: 'Sous-titre h3',
                        category: 'Titres',
                        content: '<h3 data-gjs-type="text">Sous-titre</h3>'
                    },
                    {
                        id: 'h4',
                        label: 'Sous-titre h4',
                        category: 'Titres',
                        content: '<h4 data-gjs-type="text">Sous-titre</h4>'
                    },
                    {
                        id: 'h5',
                        label: 'Sous-titre h5',
                        category: 'Titres',
                        content: '<h5 data-gjs-type="text">Sous-titre</h5>'
                    },
                    {
                        id: 'h6',
                        label: 'Sous-titre h6',
                        category: 'Titres',
                        content: '<h6 data-gjs-type="text">Sous-titre</h6>'
                    },
                    {
                        id: 'text',
                        label: 'Paragraphe',
                        category: 'Contenu',
                        content: '<p data-gjs-type="text">Votre texte ici...</p>'
                    },
                    {
                        id: 'image',
                        label: 'Image',
                        category: 'Contenu',
                        select: true,
                        // Configuration avancée de l'image pour le SEO
                        content: {
                            type: 'image',
                            traits: [ // <--- C'est ici que ça se passe
                                'alt', // Champ simple pour modifier le texte alternatif
                                {
                                    type: 'text',
                                    label: 'Texte alternatif (SEO)', // Libellé pour l'utilisateur
                                    name: 'alt',
                                }
                            ]
                        },
                        activate: true
                    }
                ]
            }
        });

        // Sauvegarde (inchangée mais assure-toi que save_page.php existe)
        async function saveContent() {
            const html = editor.getHtml();
            const css = editor.getCss();
            const title = document.getElementById('page-title').value;
            const fullContent = `<style>${css}</style>${html}`;

            try {
                const response = await fetch('save_page.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: title,
                        content: fullContent,
                        slug: title.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '')
                    })
                });
                const result = await response.json();
                alert(result.status === 'success' ? 'Page enregistrée !' : 'Erreur SQL.');
            } catch (e) { alert('Erreur serveur.'); }
        }
    </script>
</body>

</html>