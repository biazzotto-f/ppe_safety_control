<div class="dropdown">
    <a href="#" class="dropdown-toggle text-dark text-decoration-none" id="userDropdown" role="button"
        data-bs-toggle="dropdown" aria-expanded="false">
        <div class="user-info">
            <span class="user-name d-none d-sm-inline"><?= htmlspecialchars($_SESSION['nome_usuario']); ?></span>
            <div class="user-avatar">
                <?php if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])): ?>
                    <img src="<?= $appUrl . '/' . $_SESSION['foto_perfil'] ?>" alt="Avatar" class="avatar-img">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['nome_usuario'], 0, 1)) ?>
                <?php endif; ?>
            </div>
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li><a class="dropdown-item" href="<?= $appUrl ?>/perfil"><i class="fa-solid fa-user-edit fa-fw me-2"></i> Meu
                Perfil</a></li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item" href="<?= $appUrl ?>/logout"><i class="fa-solid fa-sign-out-alt fa-fw me-2"></i>
                Sair</a></li>
    </ul>
</div>