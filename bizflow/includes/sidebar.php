<aside class="sidebar">

    <div class="logo">
        <span>Biz</span>Flow
    </div>

    <nav class="menu">

        <a href="/dashboard/" class="menu-item">
            <span>🏠</span>
            Dashboard
        </a>

        <a href="/clientes/" class="menu-item">
            <span>👥</span>
            Clientes
        </a>

        <a href="/categorias/" class="menu-item">
            <span>🏷️</span>
            Categorias
        </a>

        <a href="/produtos/" class="menu-item">
            <span>📦</span>
            Produtos
        </a>

        <a href="/vendas/" class="menu-item">
            <span>🛒</span>
            Vendas
        </a>

        <a href="/caixa/" class="menu-item">
            <span>💰</span>
            Caixa
        </a>

        <a href="/relatorios/" class="menu-item">
            <span>📊</span>
            Relatórios
        </a>

        <?php if (isset($_SESSION["nivel"]) && $_SESSION["nivel"] === "administrador"): ?>

            <a href="/usuarios/" class="menu-item">
                <span>👤</span>
                Usuários
            </a>

        <?php endif; ?>

    </nav>

    <div class="sidebar-bottom">

        <a href="/auth/logout.php" class="menu-item logout">
            <span>🚪</span>
            Sair
        </a>

    </div>

</aside>