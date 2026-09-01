<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";
// ==============================
// PAGINAÇÃO
// ==============================

$por_pagina = 10;

$pagina = filter_input(
    INPUT_GET,
    "pagina",
    FILTER_VALIDATE_INT
);

if (!$pagina || $pagina < 1) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $por_pagina;


// ==============================
// PESQUISA
// ==============================

$pesquisa = trim($_GET["pesquisa"] ?? "");


// ==============================
// CONTAR CLIENTES
// ==============================

if ($pesquisa !== "") {

    $sql_count = "SELECT COUNT(*) AS total
                  FROM clientes
                  WHERE nome LIKE ?
                     OR telefone LIKE ?
                     OR email LIKE ?";

    $stmt_count = $conn->prepare($sql_count);

    $termo = "%" . $pesquisa . "%";

    $stmt_count->bind_param(
        "sss",
        $termo,
        $termo,
        $termo
    );

} else {

    $sql_count = "SELECT COUNT(*) AS total
                  FROM clientes";

    $stmt_count = $conn->prepare($sql_count);
}

$stmt_count->execute();

$resultado_count = $stmt_count->get_result();

$total_clientes = $resultado_count
    ->fetch_assoc()["total"];

$stmt_count->close();


// ==============================
// TOTAL DE PÁGINAS
// ==============================

$total_paginas = ceil(
    $total_clientes / $por_pagina
);


// ==============================
// BUSCAR CLIENTES
// ==============================

if ($pesquisa !== "") {

    $sql = "SELECT id_cliente, nome, telefone, email, endereco, data_criacao
            FROM clientes
            WHERE nome LIKE ?
               OR telefone LIKE ?
               OR email LIKE ?
            ORDER BY id_cliente DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssii",
        $termo,
        $termo,
        $termo,
        $por_pagina,
        $offset
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

} else {

    $sql = "SELECT id_cliente, nome, telefone, email, endereco, data_criacao
            FROM clientes
            ORDER BY id_cliente DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ii",
        $por_pagina,
        $offset
    );

    $stmt->execute();

    $resultado = $stmt->get_result();
}
$sucesso = $_GET["sucesso"] ?? "";

?>

<main class="main">

    <!-- TOPBAR -->

    <header class="topbar">

        <h1>Clientes</h1>

        <div class="user-info">

            <div>
                <strong>
                    <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                </strong>

                <small class="d-block text-muted">
                    <?php echo htmlspecialchars($_SESSION["nivel"]); ?>
                </small>
            </div>

            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION["nome"], 0, 1)); ?>
            </div>

        </div>

    </header>


    <!-- CONTEÚDO -->

    <section class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-semibold">
                    Clientes
                </h2>

                <p class="text-muted mb-0">
                    Gerencie os clientes da empresa.
                </p>

            </div>


            <a href="adicionar.php" class="btn btn-success">

                + Novo cliente

            </a>

        </div>
        <form
    method="GET"
    class="mb-4"
>

    <div class="input-group">

        <input
            type="search"
            name="pesquisa"
            class="form-control"
            placeholder="Pesquisar por nome, telefone ou email..."
            value="<?php echo htmlspecialchars($pesquisa); ?>"
        >

        <button
            type="submit"
            class="btn btn-success"
        >
            Pesquisar
        </button>

        <?php if ($pesquisa !== ""): ?>

            <a
                href="index.php"
                class="btn btn-outline-secondary"
            >
                Limpar
            </a>

        <?php endif; ?>

    </div>

</form>


        <!-- TABELA -->
<?php if ($sucesso !== ""): ?>

    <div class="alert alert-success">
        <?php echo htmlspecialchars($sucesso); ?>
    </div>

<?php endif; ?>

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Nome</th>

                                <th>Telefone</th>

                                <th>Email</th>

                                <th>Endereço</th>

                                <th>Data</th>

                                <th>Ações</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($cliente = $resultado->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo $cliente["id_cliente"]; ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars($cliente["nome"]);
                                            ?>
                                        </strong>

                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $cliente["telefone"] ?? "-"
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $cliente["email"] ?? "-"
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $cliente["endereco"] ?? "-"
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <?php
                                        echo date(
                                            "d/m/Y",
                                            strtotime($cliente["data_criacao"])
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <a
                                            href="editar.php?id=<?php echo $cliente["id_cliente"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Editar
                                        </a>

                                       <form
    action="excluir.php"
    method="POST"
    class="d-inline"
    onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');"
>

    <input
        type="hidden"
        name="id"
        value="<?php echo $cliente["id_cliente"]; ?>"
    >

    <button
        type="submit"
        class="btn btn-sm btn-outline-danger"
    >
        Excluir
    </button>

</form>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="7" class="text-center py-4">

                                    <p class="text-muted mb-0">
                                        Nenhum cliente cadastrado.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>
                    <?php if ($total_paginas > 1): ?>

    <nav class="mt-4">

        <ul class="pagination justify-content-center">

            <!-- ANTERIOR -->

            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">

                <a
                    class="page-link"
                    href="?pagina=<?php echo $pagina - 1; ?>&pesquisa=<?php echo urlencode($pesquisa); ?>"
                >
                    Anterior
                </a>

            </li>


            <!-- PÁGINAS -->

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>

                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">

                    <a
                        class="page-link"
                        href="?pagina=<?php echo $i; ?>&pesquisa=<?php echo urlencode($pesquisa); ?>"
                    >
                        <?php echo $i; ?>
                    </a>

                </li>

            <?php endfor; ?>


            <!-- PRÓXIMA -->

            <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">

                <a
                    class="page-link"
                    href="?pagina=<?php echo $pagina + 1; ?>&pesquisa=<?php echo urlencode($pesquisa); ?>"
                >
                    Próxima
                </a>

            </li>

        </ul>

    </nav>

<?php endif; ?>

                </div>

            </div>

        </div>

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>