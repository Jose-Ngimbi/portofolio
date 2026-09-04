<aside class="sidebar">

    <div class="logo">
        <span>Biz</span>Flow
    </div>

    <nav class="menu">

        <!-- DASHBOARD -->
        <a href="/dashboard/" class="menu-item">
            <span>🏠</span>
            Dashboard
        </a>

        <!-- CLIENTES -->
        <a href="/clientes/" class="menu-item">
            <span>👥</span>
            Clientes
        </a>

        <!-- CATEGORIAS: ADMINISTRADOR E GERENTE -->
        <?php if (
            isset($_SESSION["nivel"]) &&
            (
                $_SESSION["nivel"] === "administrador" ||
                $_SESSION["nivel"] === "gerente"
            )
        ): ?>

            <a href="/categorias/" class="menu-item">
                <span>🏷️</span>
                Categorias
            </a>

        <?php endif; ?>


        <!-- PRODUTOS -->
        <a href="/produtos/" class="menu-item">
            <span>📦</span>
            Produtos
        </a>

        <!-- VENDAS -->
        <a href="/vendas/" class="menu-item">
            <span>🛒</span>
            Vendas
        </a>

        <!-- CAIXA: ADMINISTRADOR E GERENTE -->
        <?php if (
            isset($_SESSION["nivel"]) &&
            (
                $_SESSION["nivel"] === "administrador" ||
                $_SESSION["nivel"] === "gerente"
            )
        ): ?>

            <a href="/caixa/" class="menu-item">
                <span>💰</span>
                Caixa
            </a>

        <?php endif; ?>


        <!-- RELATÓRIOS -->
        <a href="/relatorios/" class="menu-item">
            <span>📊</span>
            Relatórios
        </a>


        <!-- USUÁRIOS: APENAS ADMINISTRADOR -->
        <?php if (
            isset($_SESSION["nivel"]) &&
            $_SESSION["nivel"] === "administrador"
        ): ?>

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