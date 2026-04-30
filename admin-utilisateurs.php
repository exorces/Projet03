<?php
$pageTitle = 'Tous les utilisateurs';
$navType   = 'admin';
$current   = 'utilisateurs';
include '_partials/header.php';
?>

<h2 class="page-title">Tous les utilisateurs</h2>
<p class="page-sub">Liste alphabétique. Mot de passe et champ « Autres infos » non affichés.</p>

<div class="toolbar">
  <div class="group">
    <span class="count"><strong>142</strong> utilisateurs · <strong>128</strong> confirmés · <strong>14</strong> en attente</span>
  </div>
  <div class="group">
    <label>Filtrer par statut</label>
    <select>
      <option>Tous</option>
      <option>Confirmé</option>
      <option>En attente</option>
      <option>Administrateur</option>
    </select>
  </div>
</div>

<table class="data">
  <thead>
    <tr>
      <th>#</th>
      <th>Nom, Prénom</th>
      <th>Courriel</th>
      <th>Statut</th>
      <th>N° empl.</th>
      <th>Inscription</th>
      <th>Connexions</th>
      <th>Annonces<br><span style="font-weight:400; text-transform:none; letter-spacing:0;">(act/inact/ret)</span></th>
      <th>5 dernières connexions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>001</td>
      <td><strong>Bélanger-Côté, Antoine</strong></td>
      <td>antoine.belanger@cgodin.qc.ca</td>
      <td>Enseignant</td>
      <td>2104</td>
      <td class="small">2026-01-18</td>
      <td>47</td>
      <td>3 / 1 / 2</td>
      <td class="small">2026-04-26 09h12<br>2026-04-25 14h08<br>2026-04-24 11h45<br>2026-04-22 16h30<br>2026-04-21 10h22</td>
    </tr>
    <tr>
      <td>002</td>
      <td><strong>Gagnon, Julie</strong></td>
      <td>julie.gagnon@cgodin.qc.ca</td>
      <td>Professionnel</td>
      <td>3015</td>
      <td class="small">2026-02-03</td>
      <td>22</td>
      <td>1 / 0 / 1</td>
      <td class="small">2026-04-26 08h44<br>2026-04-23 13h17<br>2026-04-20 09h55<br>2026-04-18 16h02<br>2026-04-15 11h33</td>
    </tr>
    <tr>
      <td>003</td>
      <td><strong>Nguyen, Linh</strong></td>
      <td>linh.nguyen@cgodin.qc.ca</td>
      <td>Cadre</td>
      <td>1102</td>
      <td class="small">2026-01-09</td>
      <td>89</td>
      <td>2 / 0 / 4</td>
      <td class="small">2026-04-26 14h33<br>2026-04-26 08h21<br>2026-04-25 19h47<br>2026-04-25 09h14<br>2026-04-24 17h38</td>
    </tr>
    <tr>
      <td>004</td>
      <td><strong>Roux, Ken-Li</strong></td>
      <td>ken-li.roux@cgodin.qc.ca</td>
      <td>Employé de soutien</td>
      <td>4401</td>
      <td class="small">2026-03-15</td>
      <td>31</td>
      <td>2 / 1 / 0</td>
      <td class="small">2026-04-27 10h05<br>2026-04-26 11h08<br>2026-04-25 19h22<br>2026-04-25 08h30<br>2026-04-24 14h11</td>
    </tr>
    <tr>
      <td>005</td>
      <td><strong>Tremblay, Sophie</strong></td>
      <td>sophie.tremblay@cgodin.qc.ca</td>
      <td>Enseignant</td>
      <td>2305</td>
      <td class="small">2026-02-19</td>
      <td>54</td>
      <td>5 / 2 / 1</td>
      <td class="small">2026-04-26 14h32<br>2026-04-26 07h58<br>2026-04-25 18h44<br>2026-04-24 09h12<br>2026-04-23 16h05</td>
    </tr>
  </tbody>
</table>

<div class="pagination">
  <span class="disabled">«</span>
  <span class="disabled">‹</span>
  <span class="current">1</span>
  <a href="#">2</a>
  <a href="#">3</a>
  <a href="#">›</a>
  <a href="#">»</a>
</div>

<?php include '_partials/footer.php'; ?>
