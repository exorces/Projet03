<?php
$pageTitle = 'Nouvelle annonce';
$navType   = 'user';
$current   = 'mes-annonces';
include 'header.php';
?>

<h2 class="page-title">Nouvelle annonce</h2>
<p class="page-sub">Tous les champs sont obligatoires sauf indication contraire.</p>

<div class="auth-wrap" style="margin-top:0; max-width:720px;">
  <div class="field">
    <label>Catégorie</label>
    <select>
      <option>— Choisir —</option>
      <option>Location</option>
      <option>Recherche</option>
      <option>À vendre</option>
      <option>À donner</option>
      <option>Service offert</option>
      <option>Autre</option>
    </select>
  </div>

  <div class="field">
    <label>Description abrégée</label>
    <input type="text" maxlength="50" placeholder="Maximum 50 caractères">
    <div class="hint">Apparaîtra dans la liste des annonces.</div>
  </div>

  <div class="field">
    <label>Description complète</label>
    <textarea rows="5" maxlength="250" placeholder="Maximum 250 caractères"></textarea>
  </div>

  <div class="field-row">
    <div class="field">
      <label>Prix (en $)</label>
      <input type="text" placeholder="0,00 — laisser vide si à donner">
    </div>
    <div class="field">
      <label>État</label>
      <select>
        <option>Actif</option>
        <option>Inactif</option>
      </select>
    </div>
  </div>

  <div class="field">
    <label>Photo</label>
    <input type="file" accept="image/*">
    <div class="hint">Une vignette sera générée automatiquement (144 px de largeur).</div>
  </div>

  <div class="actions-bar">
    <button class="btn btn-primary">Publier l'annonce</button>
    <a href="mes-annonces.php" class="btn btn-ghost">Annuler</a>
  </div>
</div>

<?php include 'footer.php'; ?>
