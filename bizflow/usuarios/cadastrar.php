<?php
require_once "../includes/admin_only.php";
require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../config/database.php";


// ==========================================
// CADASTRAR USUÁRIO
// ==========================================

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $nivel = $_POST["nivel"];
    $status = $_POST["status"];


    // Verificar campos obrigatórios

    if (
        empty($nome)
        ||
        empty($email)
        ||
        empty($senha)
    ) {

        $mensagem =
            "Preencha todos os campos obrigatórios.";

    } else {

        // Verificar se o email já existe

        $sql_verificar = "
            SELECT id_usuario
            FROM usuarios
            WHERE email = ?
        ";

        $stmt = $conn->prepare($sql_verificar);

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();


        if ($resultado->num_rows > 0) {

            $mensagem =
                "Este email já está cadastrado.";

        } else {

            // Criptografar senha

            $senha_hash =
                password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );


            // Inserir usuário

            $sql = "
                INSERT INTO usuarios
                (
                    nome,
                    email,
                    senha,
                    nivel,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ";

            $stmt =
                $conn->prepare($sql);

            $stmt->bind_param(
                "sssss",
                $nome,
                $email,
                $senha_hash,
                $nivel,
                $status
            );

            if ($stmt->execute()) {

                header(
                    "Location: index.php"
                );

                exit;

            } else {

                $mensagem =
                    "Erro ao cadastrar usuário.";

            }

        }

    }

}

?>

<main class="main">

    <header class="topbar">

        <h1>Novo Usuário</h1>

        <div class="user-info">

            <div>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $_SESSION["nome"]
                    );
                    ?>
                </strong>

                <small class="d-block text-muted">

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["nivel"]
                    );
                    ?>

                </small>

            </div>

            <div class="user-avatar">

                <?php

                echo strtoupper(
                    substr(
                        $_SESSION["nome"],
                        0,
                        1
                    )
                );

                ?>

            </div>

        </div>

    </header>


    <section class="content">

        <div class="mb-4">

            <a
                href="index.php"
                class="
                    btn
                    btn-outline-secondary
                    btn-sm
                    mb-3
                "
            >
                ← Voltar
            </a>


            <h2 class="fw-semibold">
                Cadastrar novo usuário
            </h2>

            <p class="text-muted">
                Preencha os dados do novo utilizador.
            </p>

        </div>


        <?php if (!empty($mensagem)): ?>

            <div class="alert alert-warning">

                <?php
                echo htmlspecialchars(
                    $mensagem
                );
                ?>

            </div>

        <?php endif; ?>


        <div
            class="
                card
                border-0
                shadow-sm
            "
        >

            <div class="card-body">

                <form method="POST">


                    <div class="row g-3">


                        <!-- NOME -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Nome

                            </label>

                            <input
                                type="text"
                                name="nome"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- EMAIL -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Email

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- SENHA -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Senha

                            </label>

                            <input
                                type="password"
                                name="senha"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- NÍVEL -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Nível de acesso

                            </label>

                            <select
                                name="nivel"
                                class="form-select"
                                required
                            >

                                <option
                                    value="funcionario"
                                >
                                    Funcionário
                                </option>

                                <option
                                    value="gerente"
                                >
                                    Gerente
                                </option>

                                <option
                                    value="administrador"
                                >
                                    Administrador
                                </option>

                            </select>

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select"
                                required
                            >

                                <option
                                    value="ativo"
                                >
                                    Ativo
                                </option>

                                <option
                                    value="inativo"
                                >
                                    Inativo
                                </option>

                            </select>

                        </div>


                    </div>


                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Salvar Usuário
                        </button>


                        <a
                            href="index.php"
                            class="
                                btn
                                btn-outline-secondary
                            "
                        >
                            Cancelar
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </section>

</main>


<?php
require_once "../includes/footer.php";
?>