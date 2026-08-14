<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1><?= $page_title ?? 'Dashboard' ?></h1>
      <div class="crumbs">Panel <?= ucfirst($_SESSION['role'] ?? 'User') ?> • <?= date('l, d F Y') ?></div>
    </div>
    <div class="topbar-right">
      <div class="user-chip">
        <div class="avatar"><i class="fas fa-user-shield"></i></div>
        <div>
          <div style="font-size:12px; color:var(--muted); font-weight:500;">Login sebagai</div>
          <div style="font-weight:700; color:var(--navy);"><?= ucfirst($_SESSION['role'] ?? 'User') ?></div>
        </div>
      </div>
    </div>
  </header>
  <section class="content">