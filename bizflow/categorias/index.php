<?php
require_once "../includes/permissions.php";
require_once "../config/database.php";

permitirAcesso([
    "administrador",
    "gerente"
]);
require_once "../includes/header.php";
require_once "../includes/sidebar.php";

$pesquisa = trim($_GET["pesquisa"] ?? "");
$sucesso = $_GET["sucesso"] ?? "";
$erro = $_GET["erro"] ?? "";


// ==============================
// BUSCAR CATEGORIAS
// ==============================

if ($pesquisa !== "") {

    $sql = "SELECT id_categoria, nome, descricao
            FROM categorias
            WHERE nome LIKE ?
               OR descricao LIKE ?
            ORDER BY id_categoria DESC";

    $stmt = $conn->prepare($sql);

    $termo = "%" . $pesquisa . "%";

    $stmt->bind_param(
        "ss",
        $termo,
        $termo
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

} else {

    $sql = "SELECT id_categoria, nome, descricao
            FROM categorias
            ORDER BY id_categoria DESC";

    $resultado = $conn->query($sql);
}

?>

<main class="main">

    <header class="topbar">

        <h1>Categorias</h1>

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

                <?php
                echo strtoupper(
                    substr($_SESSION["nome"], 0, 1)
                );
                ?>

            </div>

        </div>

    </header>


    <section class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-semibold">
                    Categorias
                </h2>

                <p class="text-muted mb-0">
                    Organize os produtos da empresa.
                </p>

            </div>

            <a
                href="adicionar.php"
                class="btn btn-success"
            >
                + Nova categoria
            </a>

        </div>


        <?php if ($sucesso !== ""): ?>

            <div class="alert alert-success">
                <?php echo htmlspecialchars($sucesso); ?>
            </div>

        <?php endif; ?>


        <?php if ($erro !== ""): ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($erro); ?>
            </div>

        <?php endif; ?>


        <!-- PESQUISA -->

        <form method="GET" class="mb-4">

            <div class="input-group">

                <input
                    type="search"
                    name="pesquisa"
                    class="form-control"
                    placeholder="Pesquisar categoria..."
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

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Ações</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($categoria = $resultado->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo $categoria["id_categoria"];
                                        ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $categoria["nome"]
                                            );
                                            ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <?php

                                        echo $categoria["descricao"]
                                            ? htmlspecialchars(
                                                $categoria["descricao"]
                                            )
                                            : "-";

                                        ?>

                                    </td>

                                    <td>

                                        <a
                                            href="editar.php?id=<?php echo $categoria["id_categoria"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Editar
                                        </a>


                                        <form
                                            action="excluir.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo $categoria["id_categoria"]; ?>"
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

                                <td
                                    colspan="4"
                                    class="text-center py-4"
                                >

                                    <p class="text-muted mb-0">
                                        Nenhuma categoria cadastrada.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>