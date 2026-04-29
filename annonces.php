<?php
$pageTitle = 'Annonces';
$navType   = 'user';
$current   = 'annonces';
include 'header.php';
?>

<h2 class="page-title">Annonces récentes</h2>
<p class="page-sub">Toutes les annonces actives, en ordre chronologique inverse de parution.</p>

<!-- Moteur de recherche -->
<div class="search-box">
  <div class="field">
    <label>Du</label>
    <input type="date">
  </div>
  <div class="field">
    <label>Au</label>
    <input type="date">
  </div>
  <div class="field">
    <label>Catégorie</label>
    <select>
      <option>— Toutes —</option>
      <option>Location</option>
      <option>Recherche</option>
      <option>À vendre</option>
      <option>À donner</option>
      <option>Service offert</option>
      <option>Autre</option>
    </select>
  </div>
  <div class="field">
    <label>Description / Auteur</label>
    <input type="text" placeholder="Mots-clés…">
  </div>
  <button class="btn">Rechercher</button>
</div>

<!-- Toolbar : tri + nb par page + comptes -->
<div class="toolbar">
  <div class="group">
    <label>Trier par</label>
    <select>
      <option>Date de parution</option>
      <option>Nom de l'auteur</option>
      <option>Catégorie</option>
    </select>
    <select>
      <option>Décroissant</option>
      <option>Croissant</option>
    </select>
  </div>
  <div class="group">
    <span class="count"><strong>247</strong> annonces actives</span>
  </div>
  <div class="group">
    <label>Par page</label>
    <select>
      <option>5</option>
      <option selected>10</option>
      <option>15</option>
      <option>20</option>
    </select>
  </div>
</div>

<!-- Liste des annonces -->
<div class="annonces-list">

  <article class="annonce-row">
    <div class="num">01</div>
    <div class="thumb" style="background: linear-gradient(135deg, #d4c5a0, #a89570);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-26, 14h32</span>
        <span class="id">Annonce #1042</span>
        <span class="cat">À vendre</span>
      </div>
      <h3><a href="annonce-detail.php">Vélo de route Trek Domane AL2, taille 56</a></h3>
      <div class="author">Par <a href="contact.php">Tremblay, Sophie</a></div>
    </div>
    <div class="price">350,00 $</div>
  </article>

  <article class="annonce-row">
    <div class="num">02</div>
    <div class="thumb" style="background: linear-gradient(135deg, #8b9c7e, #5a6b50);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-26, 11h08</span>
        <span class="id">Annonce #1041</span>
        <span class="cat">Service offert</span>
      </div>
      <h3><a href="annonce-detail.php">Tutorat en mathématiques (calcul différentiel)</a></h3>
      <div class="author">Vous (cette annonce)</div>
    </div>
    <div class="price">25,00 $</div>
  </article>

  <article class="annonce-row">
    <div class="num">03</div>
    <div class="thumb" style="background: linear-gradient(135deg, #b89575, #8a6840);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-25, 19h47</span>
        <span class="id">Annonce #1040</span>
        <span class="cat">À donner</span>
      </div>
      <h3><a href="annonce-detail.php">Manuels d'introduction à la philosophie</a></h3>
      <div class="author">Par <a href="contact.php">Roy-Beaulieu, Marc</a></div>
    </div>
    <div class="price na">N/A</div>
  </article>

  <article class="annonce-row">
    <div class="num">04</div>
    <div class="thumb" style="background: linear-gradient(135deg, #9a8ca8, #6a5d80);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-25, 16h22</span>
        <span class="id">Annonce #1039</span>
        <span class="cat">Location</span>
      </div>
      <h3><a href="annonce-detail.php">Chambre à louer près du cégep, dispo mai 2026</a></h3>
      <div class="author">Par <a href="contact.php">Nguyen, Linh</a></div>
    </div>
    <div class="price">525,00 $</div>
  </article>

  <article class="annonce-row">
    <div class="num">05</div>
    <div class="thumb" style="background: linear-gradient(135deg, #c4a878, #8a7048);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-25, 09h15</span>
        <span class="id">Annonce #1038</span>
        <span class="cat">Recherche</span>
      </div>
      <h3><a href="annonce-detail.php">Cherche guitariste pour groupe (rock alternatif)</a></h3>
      <div class="author">Par <a href="contact.php">Bélanger-Côté, Antoine</a></div>
    </div>
    <div class="price na">N/A</div>
  </article>

  <article class="annonce-row">
    <div class="num">06</div>
    <div class="thumb" style="background: linear-gradient(135deg, #7e9c8b, #506b5f);"></div>
    <div class="body">
      <div class="meta-line">
        <span>2026-04-24, 21h03</span>
        <span class="id">Annonce #1037</span>
        <span class="cat">À vendre</span>
      </div>
      <h3><a href="annonce-detail.php">Bureau ergonomique IKEA Bekant + chaise Markus</a></h3>
      <div class="author">Par <a href="contact.php">Gagnon, Julie</a></div>
    </div>
    <div class="price">280,00 $</div>
  </article>

</div>

<!-- Pagination -->
<div class="pagination">
  <span class="disabled">«</span>
  <span class="disabled">‹</span>
  <span class="current">1</span>
  <a href="#">2</a>
  <a href="#">3</a>
  <a href="#">…</a>
  <a href="#">25</a>
  <a href="#">›</a>
  <a href="#">»</a>
</div>

<?php include 'footer.php'; ?>
