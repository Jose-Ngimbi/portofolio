<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";

$pesquisa = trim($_GET["pesquisa"] ?? "");

$sucesso = $_GET["sucesso"] ?? "";
$erro = $_GET["erro"] ?? "";


// ==============================
// BUSCAR PRODUTOS
// ==============================

if ($pesquisa !== "") {

    $sql = "SELECT
                p.id_produto,
                p.nome,
                p.descricao,
                p.preco_compra,
                p.preco_venda,
                p.quantidade,
                p.estoque_minimo,
                p.status,
                c.nome AS categoria
            FROM produtos p
            LEFT JOIN categorias c
                ON p.id_categoria = c.id_categoria
            WHERE p.nome LIKE ?
               OR p.descricao LIKE ?
               OR c.nome LIKE ?
            ORDER BY p.id_produto DESC";

    $stmt = $conn->prepare($sql);

    $termo = "%" . $pesquisa . "%";

    $stmt->bind_param(
        "sss",
        $termo,
        $termo,
        $termo
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

} else {

    $sql = "SELECT
                p.id_produto,
                p.nome,
                p.descricao,
                p.preco_compra,
                p.preco_venda,
                p.quantidade,
                p.estoque_minimo,
                p.status,
                c.nome AS categoria
            FROM produtos p
            LEFT JOIN categorias c
                ON p.id_categoria = c.id_categoria
            ORDER BY p.id_produto DESC";

    $resultado = $conn->query($sql);
}

?>

<main class="main">

    <header class="topbar">

        <h1>Produtos</h1>

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
                    Produtos
                </h2>

                <p class="text-muted mb-0">
                    Gerencie os produtos e o estoque da empresa.
                </p>

            </div>

            <a
                href="adicionar.php"
                class="btn btn-success"
            >
                + Novo produto
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
                    placeholder="Pesquisar produto ou categoria..."
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
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Preço compra</th>
                                <th>Preço venda</th>
                                <th>Estoque</th>
                                <th>Status</th>
                                <th>Ações</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($produto = $resultado->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo $produto["id_produto"];
                                        ?>
                                    </td>


                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $produto["nome"]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?php
                                        echo $produto["categoria"]
                                            ? htmlspecialchars(
                                                $produto["categoria"]
                                            )
                                            : "-";
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo number_format(
                                            $produto["preco_compra"],
                                            2,
                                            ",",
                                            "."
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo number_format(
                                            $produto["preco_venda"],
                                            2,
                                            ",",
                                            "."
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        $quantidade =
                                            (int) $produto["quantidade"];

                                        $minimo =
                                            (int) $produto["estoque_minimo"];

                                        echo $quantidade;

                                        if ($quantidade <= $minimo):

                                        ?>

                                            <span class="badge bg-warning text-dark">
                                                Estoque baixo
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php if ($produto["status"] === "ativo"): ?>

                                            <span class="badge bg-success">
                                                Ativo
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Inativo
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <a
                                            href="editar.php?id=<?php echo $produto["id_produto"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Editar
                                        </a>


                                        <form
                                            action="excluir.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este produto?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo $produto["id_produto"]; ?>"
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
                                    colspan="8"
                                    class="text-center py-4"
                                >

                                    <p class="text-muted mb-0">
                                        Nenhum produto cadastrado.
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