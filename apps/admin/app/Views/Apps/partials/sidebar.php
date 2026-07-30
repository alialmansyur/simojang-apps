<?php
$userFullname = trim((string) ($seslog['fullname'] ?? $seslog['username'] ?? 'Pengguna'));
$userSecondary = trim((string) ($seslog['role'] ?? $seslog['username'] ?? 'User Login'));
$userInitials = '';

foreach (preg_split('/\s+/', $userFullname) as $word) {
	if ($word === '') {
		continue;
	}

	$userInitials .= strtoupper(substr($word, 0, 1));
	if (strlen($userInitials) >= 2) {
		break;
	}
}

if ($userInitials === '') {
	$userInitials = 'U';
}

$avatarName = trim((string) ($avatar ?? ''));
$avatarSrc = '';

if ($avatarName !== '' && strtolower($avatarName) !== 'user.jpg') {
	$faceAsset = FCPATH . 'apps/assets/images/faces/' . ltrim($avatarName, '/');
	if (is_file($faceAsset)) {
		$avatarSrc = asset_url('apps/assets/images/faces/' . ltrim($avatarName, '/'));
	} elseif (preg_match('~^(https?:)?//~', $avatarName) || str_starts_with($avatarName, '/')) {
		$avatarSrc = $avatarName;
	}
}
?>
<div id="sidebar" class="active">
	<div class="sidebar-wrapper active">
		<div class="sidebar-header position-relative">
			<div class="sidebar-header-top">
				<div class="logo">
					<a href="#">
						<img id="main-logo" class="app-main-logo" alt="Logo aplikasi">
					</a>
				</div> 
				<div class="sidebar-toggler x">
					<a href="#" class="sidebar-hide d-xl-none d-block" aria-label="Tutup sidebar"><i
							class="bi bi-x bi-middle"></i></a>
				</div>
			</div>
		</div>
		<div class="sidebar-search">
			<label for="sidebarMenuSearch" class="visually-hidden">Cari menu</label>
			<div class="sidebar-search-box">
				<span class="sidebar-search-icon" aria-hidden="true">
					<i class="bi bi-search"></i>
				</span>
				<input type="search" id="sidebarMenuSearch" class="sidebar-search-input" placeholder="Cari Cepat..."
					autocomplete="off">
				<button type="button" class="sidebar-search-shortcut" id="sidebarMenuSearchShortcut"
					aria-label="Fokus ke pencarian menu">
					Ctrl+F
				</button>
			</div>
		</div>
		<div class="sidebar-menu">
			<ul class="menu">
				<li class="sidebar-title">Main Menu</li>

				<?php foreach ($menus as $menu): ?>
				<?php if (count($menu['submenus']) > 0): ?>
				<li class="sidebar-item has-sub" id="menu<?= $menu['id'] ?>">
					<a href="#" class="sidebar-link">
						<i class="<?= $menu['icon'] ?>"></i>
						<span><?= $menu['name'] ?></span>
					</a>
					<ul class="submenu" id="submenu-parent<?= $menu['id'] ?>">
						<?php foreach ($menu['submenus'] as $subMenu): ?>
						<li class="submenu-item" id="submenu<?= $subMenu['id'] ?>">
							<a href="<?= base_url($subMenu['url']); ?>" class="menu-link"
								onclick="updateActiveMenu('<?= $subMenu['id'] ?>', '<?= $menu['id'] ?>')">
								<?= $subMenu['name'] ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</li>
				<?php else: ?>
				<li class="sidebar-item" id="menu<?= $menu['id'] ?>">
					<a href="<?= base_url($menu['url']) ?>" class="sidebar-link menu-link"
						onclick="updateActiveMenu('<?= $menu['id'] ?>', null)">
						<i class="<?= $menu['icon'] ?>"></i>
						<span><?= $menu['name'] ?></span>
					</a>
				</li>
				<?php endif; ?>
				<?php endforeach; ?>

				<li class="sidebar-title">Other</li>
				<li class="sidebar-item">
					<a href="#" class="sidebar-link" data-bs-toggle="modal" data-bs-target="#change-password">
						<i class="bi bi-gear"></i>
						<span>Change Password</span>
					</a>
				</li>
			</ul>
		</div>
		<div class="sidebar-footer">
			<div class="sidebar-user-card">
				<div class="sidebar-user-avatar<?= $avatarSrc !== '' ? ' has-image' : '' ?>">
					<?php if ($avatarSrc !== ''): ?>
					<img src="<?= esc($avatarSrc) ?>" alt="<?= esc($userFullname) ?>"
						onerror="this.parentNode.classList.remove('has-image'); this.remove();">
					<?php endif; ?>
					<span><?= esc($userInitials) ?></span>
				</div>
				<div class="sidebar-user-meta">
					<strong><?= esc($userFullname) ?></strong>
					<span><?= esc($userSecondary) ?></span>
				</div>
			</div>
		</div>
	</div>
</div>
