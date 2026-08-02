<?php
// Expects $activeTab to be set by the including page and config.php + auth.php already required.
$navItems = [
    'hub'         => ['href' => 'hub.php',         'label' => 'Home'],
    'dashboard'   => ['href' => 'dashboard.php',   'label' => 'Executive Dashboard'],
    'yoy'         => ['href' => 'yoy.php',         'label' => 'Year-over-Year'],
    'rankings'    => ['href' => 'rankings.php',    'label' => 'Rankings'],
    'performance' => ['href' => 'performance.php', 'label' => 'Category & Region'],
    'import'      => ['href' => 'import.php',      'label' => 'Data Upload'],
];
?>
<aside class="sidebar">
  <div class="brand">
    <span class="brand-mark">◆</span>
    <div class="brand-text">
      <h1><?php echo htmlspecialchars(APP_NAME); ?></h1>
      <p>Sales analytics platform</p>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($navItems as $key => $item): ?>
      <a href="<?php echo $item['href']; ?>" class="sidebar-link <?php echo ($activeTab ?? '') === $key ? 'active' : ''; ?>">
        <?php echo htmlspecialchars($item['label']); ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <span class="user-icon">●</span> <?php echo htmlspecialchars(current_username()); ?>
    </div>
    <a href="logout.php" class="sidebar-logout">Log out</a>
  </div>
</aside>
