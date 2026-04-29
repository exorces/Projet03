<?php
/**
 * Page d'aperçu — outil de développement uniquement.
 * Liste tous les écrans de la maquette pour faciliter la navigation pendant le développement.
 * À RETIRER avant la remise finale.
 */
$pageTitle = 'Aperçu de la maquette';
$navType   = 'public';
$current   = '';
include 'header.php';

$screens = [
  'Public — non authentifié' => [
    ['index.php',          'Connexion',                    'Point d\'entrée. Saisie courriel + mot de passe.'],
    ['enregistrement.php', 'Enregistrement',               'Création de compte avec validation client.'],
    ['recuperation.php',   'Récupération du mot de passe', 'Envoi du mot de passe par courriel.'],
    ['confirmation.php',   'Confirmation d\'inscription',  'Page d\'atterrissage après clic sur le lien courriel.'],
  ],
  'Utilisateur authentifié' => [
    ['annonces.php',          'Affichage des annonces',  'Liste avec recherche, tri et pagination.'],
    ['annonce-detail.php',    'Détail d\'une annonce',   'Photo, description complète, coordonnées de l\'auteur.'],
    ['contact.php',           'Contacter l\'auteur',     'Formulaire d\'envoi de courriel à l\'auteur.'],
    ['mes-annonces.php',      'Gestion de mes annonces', 'Liste personnelle avec actions (modifier/désactiver/retirer).'],
    ['annonce-form.php',      'Nouvelle annonce',        'Formulaire de saisie / modification d\'une annonce.'],
    ['annonce-retrait.php',   'Retrait d\'une annonce',  'Confirmation avant changement d\'état à « Retiré ».'],
    ['profil.php',            'Mon profil',              'Identité, statut d\'employé, téléphones (publics ou non).'],
    ['motdepasse.php',        'Modification du mdp',     'Saisie du mot de passe actuel + nouveau.'],
  ],
  'Administrateur' => [
    ['admin.php',                'Menu administrateur',         'Trois cartes : annonces, utilisateurs, nettoyage.'],
    ['admin-utilisateurs.php',   'Tous les utilisateurs',       'Tableau alphabétique avec stats et historique.'],
    ['admin-nettoyage.php',      'Nettoyage de la base',        'Suppressions physiques (comptes non confirmés, annonces retirées).'],
  ],
];
?>

<h2 class="page-title">Aperçu de la maquette</h2>
<p class="page-sub">Outil de développement — liste de tous les écrans. À retirer avant la remise finale.</p>

<div class="alert warn">
  <h4>Note</h4>
  <p>Cette page n'est pas requise par l'énoncé. Elle sert uniquement à naviguer rapidement entre les écrans pendant le développement.</p>
</div>

<?php foreach ($screens as $section => $pages): ?>
  <div class="section-label"><?= $section ?></div>
  <div class="apercu-list">
    <?php foreach ($pages as [$file, $name, $desc]): ?>
      <a href="<?= $file ?>" class="apercu-item">
        <div class="apercu-name"><?= $name ?></div>
        <div class="apercu-file"><?= $file ?></div>
        <div class="apercu-desc"><?= $desc ?></div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<style>
  .apercu-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
    margin-bottom: 28px;
  }
  .apercu-item {
    display: block;
    padding: 16px 18px;
    background: #fff;
    border: 1px solid var(--rule);
    border-left: 3px solid var(--accent);
    text-decoration: none;
    color: var(--ink);
    transition: transform 0.1s, box-shadow 0.1s;
  }
  .apercu-item:hover {
    transform: translate(-2px, -2px);
    box-shadow: 4px 4px 0 var(--ink);
  }
  .apercu-name {
    font-family: 'Fraunces', serif;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 2px;
  }
  .apercu-file {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 11px;
    color: var(--ink-faint);
    margin-bottom: 8px;
  }
  .apercu-desc {
    font-size: 12px;
    color: var(--ink-soft);
    line-height: 1.4;
  }
</style>

<?php include 'footer.php'; ?>
