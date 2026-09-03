<?php

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";


// ==========================================
// LISTAR USUÁRIOS
// ==========================================

$sql = "
    SELECT
        id_usuario,
        nome,
        email,
        nivel,
        status,
        data_criacao
    FROM usuarios
    ORDER BY id_usuario DESC
";

$resultado = $conn->query($sql);

?>

<main class="main">

    <header class="topbar">

        <h1>Usuários</h1>

        <div class="user-info">

            <div>

                <strong>
                    <?php
                    echo htmlspecialchars($_SESSION["nome"]);
                    ?>
                </strong>

                <small class="d-block text-muted">
                    <?php
                    echo htmlspecialchars($_SESSION["nivel"]);
                    ?>
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

        <div
            class="d-flex justify-content-between align-items-center mb-4"
        >

            <div>

                <h2 class="fw-semibold mb-1">
                    Gestão de Usuários
                </h2>

                <p class="text-muted mb-0">
                    Gerencie os utilizadores do sistema.
                </p>

            </div>


            <a
                href="cadastrar.php"
                class="btn btn-success"
            >
                + Novo Usuário
            </a>

        </div>


        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle"
                    >

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Nome</th>

                                <th>Email</th>

                                <th>Nível</th>

                                <th>Status</th>

                                <th>Data de criação</th>

                                <th class="text-end">
                                    Ações
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php
                            if (
                                $resultado
                                &&
                                $resultado->num_rows > 0
                            ):
                            ?>

                                <?php
                                while (
                                    $usuario =
                                    $resultado->fetch_assoc()
                                ):
                                ?>

                                    <tr>

                                        <td>
                                            #<?php
                                            echo $usuario[
                                                "id_usuario"
                                            ];
                                            ?>
                                        </td>


                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $usuario["nome"]
                                            );
                                            ?>
                                        </td>


                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $usuario["email"]
                                            );
                                            ?>
                                        </td>


                                        <td>

                                            <?php
                                            echo ucfirst(
                                                htmlspecialchars(
                                                    $usuario["nivel"]
                                                )
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                $usuario["status"]
                                                === "ativo"
                                            ): ?>

                                                <span
                                                    class="
                                                        badge
                                                        bg-success
                                                    "
                                                >
                                                    Ativo
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="
                                                        badge
                                                        bg-secondary
                                                    "
                                                >
                                                    Inativo
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td>

                                            <?php
                                            echo date(
                                                "d/m/Y H:i",
                                                strtotime(
                                                    $usuario[
                                                        "data_criacao"
                                                    ]
                                                )
                                            );
                                            ?>

                                        </td>


                                        <td class="text-end">

                                            <a
                                                href="
                                                    editar.php?id=
                                                    <?php
                                                    echo $usuario[
                                                        "id_usuario"
                                                    ];
                                                    ?>
                                                "
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-primary
                                                "
                                            >
                                                Editar
                                            </a>


                                            <a
                                                href="
                                                    eliminar.php?id=
                                                    <?php
                                                    echo $usuario[
                                                        "id_usuario"
                                                    ];
                                                    ?>
                                                "
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-danger
                                                "
                                                onclick="
                                                    return confirm(
                                                        'Tem certeza que deseja eliminar este usuário?'
                                                    );
                                                "
                                            >
                                                Eliminar
                                            </a>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="7"
                                        class="
                                            text-center
                                            text-muted
                                            py-4
                                        "
                                    >

                                        Nenhum usuário encontrado.

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