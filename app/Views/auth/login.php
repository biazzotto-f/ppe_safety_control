<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PPE's Safety Control</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Link para o seu ficheiro de estilos principal -->
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body class="login-body">
    <div class="login-wrapper">
        <div class="login-branding">
            <i class="fa-solid fa-helmet-safety logo-icon"></i>
            <h1>PPE's Safety Control</h1>
            <p>Gestão completa e simplificada de Equipamentos de Proteção Individual.</p>
        </div>

<div class="login-form-container">
            <h2>Acesse à sua conta</h2>
        <form action="<?= $_ENV['APP_URL'] ?>/auth/login" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
               <?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger p-2 text-center" role="alert"><?= $_SESSION['error'];
            unset($_SESSION['error']); ?></div><?php endif; ?>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>