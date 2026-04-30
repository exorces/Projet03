<?php
$pageTitle = 'Mon profil';
$navType   = 'user';
$current   = 'profil';
include '_partials/header.php';
?>

<h2 class="page-title">Mon profil</h2>
<p class="page-sub">Mettez à jour vos informations personnelles. Cochez « Public » pour rendre un numéro visible dans vos annonces.</p>

<div class="auth-wrap" style="margin-top:0; max-width:720px;">

  <div class="section-label">Identification</div>

  <div class="field">
    <label>Adresse de courriel</label>
    <input type="email" value="ken-li.roux@cgodin.qc.ca" disabled style="background:var(--paper-dim);">
    <div class="hint">L'adresse de courriel ne peut pas être modifiée.</div>
  </div>

  <div class="field">
    <label>Mot de passe</label>
    <a href="motdepasse.php" class="btn btn-sm btn-ghost">Modifier le mot de passe</a>
  </div>

  <div class="section-label">Identité</div>

  <div class="field-row">
    <div class="field">
      <label>Nom de famille</label>
      <input type="text" value="Roux">
    </div>
    <div class="field">
      <label>Prénom</label>
      <input type="text" value="Ken-Li">
    </div>
  </div>

  <div class="field-row">
    <div class="field">
      <label>Statut d'employé (facultatif)</label>
      <select>
        <option>— Aucun —</option>
        <option>Cadre</option>
        <option>Employé de soutien</option>
        <option>Enseignant</option>
        <option>Professionnel</option>
      </select>
    </div>
    <div class="field">
      <label>Numéro d'employé (facultatif)</label>
      <input type="text" placeholder="1 à 9999">
    </div>
  </div>

  <div class="section-label">Coordonnées (toutes facultatives)</div>

  <div class="field">
    <label>Téléphone à la maison</label>
    <div class="phone-row">
      <input type="text" placeholder="(999) 999-9999">
      <label class="field-inline">
        <input type="checkbox"> <span>Public</span>
      </label>
    </div>
  </div>

  <div class="field">
    <label>Téléphone au travail</label>
    <div class="phone-row">
      <input type="text" placeholder="(999) 999-9999 #9999">
      <label class="field-inline">
        <input type="checkbox" checked> <span>Public</span>
      </label>
    </div>
  </div>

  <div class="field">
    <label>Téléphone cellulaire</label>
    <div class="phone-row">
      <input type="text" placeholder="(999) 999-9999">
      <label class="field-inline">
        <input type="checkbox"> <span>Public</span>
      </label>
    </div>
  </div>

  <div class="actions-bar">
    <button class="btn btn-primary">Enregistrer les modifications</button>
    <a href="annonces.php" class="btn btn-ghost">Annuler</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
