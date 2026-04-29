<?php
$pageTitle = 'Mes annonces';
$navType   = 'user';
$current   = 'mes-annonces';
include 'header.php';
?>

<h2 class="page-title">Mes annonces</h2>
<p class="page-sub">Toutes vos annonces, actives et inactives, en ordre chronologique inverse.</p>

<div class="toolbar">
  <div class="group">
    <label>Trier par</label>
    <select>
      <option>Date de parution</option>
      <option>Catégorie</option>
      <option>Description abrégée</option>
      <option>État</option>
    </select>
    <select>
      <option>Décroissant</option>
      <option>Croissant</option>
    </select>
  </div>
  <div class="group">
    <span class="count"><strong>7</strong> annonces au total</span>
  </div>
  <div class="group">
    <a href="annonce-form.php" class="btn btn-primary btn-sm">+ Nouvelle annonce</a>
  </div>
</div>

<div class="annonces-list">

  <article class="annonce-row">
    <div class="num">01</div>
    <div class="thumb" style="background: linear-gradient(135deg, #8b9c7e, #5a6b50);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-26, 11h08</span>
        <span class="id">Annonce #1041</span>
        <span class="cat">Service offert</span>
        <span class="badge actif">Actif</span>
      </div>
      <h3><a href="annonce-detail.php">Tutorat en mathématiques (calcul différentiel)</a></h3>
      <div class="author">Prix : <strong>25,00 $</strong></div>
    </div>
    <div class="actions">
      <a href="annonce-form.php" class="btn btn-sm btn-ghost">Modifier</a>
      <a href="#" class="btn btn-sm btn-ghost">Désactiver</a>
      <a href="annonce-retrait.php" class="btn btn-sm btn-danger">Retirer</a>
    </div>
  </article>

  <article class="annonce-row">
    <div class="num">02</div>
    <div class="thumb" style="background: linear-gradient(135deg, #c4a878, #8a7048);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-15, 18h45</span>
        <span class="id">Annonce #1015</span>
        <span class="cat">À vendre</span>
        <span class="badge inactif">Inactif</span>
      </div>
      <h3><a href="annonce-detail.php">Console PSP-3000 + 12 jeux + cartes mémoire</a></h3>
      <div class="author">Prix : <strong>140,00 $</strong></div>
    </div>
    <div class="actions">
      <a href="annonce-form.php" class="btn btn-sm btn-ghost">Modifier</a>
      <a href="#" class="btn btn-sm btn-ghost">Activer</a>
      <a href="annonce-retrait.php" class="btn btn-sm btn-danger">Retirer</a>
    </div>
  </article>

  <article class="annonce-row">
    <div class="num">03</div>
    <div class="thumb" style="background: linear-gradient(135deg, #9a8ca8, #6a5d80);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-03-22, 10h12</span>
        <span class="id">Annonce #978</span>
        <span class="cat">Recherche</span>
        <span class="badge actif">Actif</span>
      </div>
      <h3><a href="annonce-detail.php">Cherche disque dur externe 2 To en bon état</a></h3>
      <div class="author">Prix : <em style="color:var(--ink-faint);">N/A</em></div>
    </div>
    <div class="actions">
      <a href="annonce-form.php" class="btn btn-sm btn-ghost">Modifier</a>
      <a href="#" class="btn btn-sm btn-ghost">Désactiver</a>
      <a href="annonce-retrait.php" class="btn btn-sm btn-danger">Retirer</a>
    </div>
  </article>

</div>

<div class="pagination">
  <span class="disabled">«</span>
  <span class="disabled">‹</span>
  <span class="current">1</span>
  <span class="disabled">›</span>
  <span class="disabled">»</span>
</div>

<?php include 'footer.php'; ?>
