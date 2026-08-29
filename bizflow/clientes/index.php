<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";


// Buscar clientes
$sql = "SELECT id_cliente, nome, telefone, email, endereco, data_criacao
        FROM clientes
        ORDER BY id_cliente DESC";

$resultado = $conn->query($sql);
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

                </div>

            </div>

        </div>

    </section>

</main>


<?php

require_once "../includes/footer.php";

?>