<?php 
    session_start(); 
if (isset($_SESSION["id_usuario"]))
{ header("Location: ../dashboard/index.php"); exit; } 
require_once "../config/database.php"; 
$erro = ""; if ($_SERVER["REQUEST_METHOD"] === "POST") { $email = trim($_POST["email"] ?? ""); $senha = $_POST["senha"] ?? "";
                                                        if (empty($email)
                                                                                                                                   
                                                                               || empty($senha)) 
{ $erro = "Preencha todos os campos.";
} else { $sql = "SELECT id_usuario, nome, email, senha, nivel, status FROM usuarios WHERE email = ? LIMIT 1"; 
        $stmt = $conn->prepare($sql); 
        if (!$stmt) { die("Erro na preparação da consulta."); 
                    } $stmt->bind_param("s", $email); 
        $stmt->execute();
        $resultado = $stmt->get_result(); 
        if ($resultado->num_rows === 1) { $usuario = $resultado->fetch_assoc();
                                         if ($usuario["status"] !== "ativo") { $erro = "A sua conta está inativa.";
                                
                                 }elseif (password_verify($senha, $usuario["senha"])) 
                                         { session_regenerate_id(true); $_SESSION["id_usuario"] = $usuario["id_usuario"]; $_SESSION["nome"] = $usuario["nome"]; $_SESSION["email"] = $usuario["email"];
                                          $_SESSION["nivel"] = $usuario["nivel"];
                                          header("Location: ../dashboard/index.php");
                                          exit; 
                                         } else { $erro = "Email ou senha incorretos."; 
                                                } 
                                        } else { $erro = "Email ou senha incorretos.";
                                               } $stmt->close(); 
       } } 
?>
<!DOCTYPE html>
<html lang="pt">
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>Entrar - BizFlow</title>
        <style> 
            * { margin: 0; padding: 0;
                box-sizing: border-box; 
            } 
            body { font-family: Arial, Helvetica, sans-serif; 
                min-height: 100vh;
                background: linear-gradient(135deg, #0c7a3d, #075b2d); 
                display: flex; align-items: center; 
                justify-content: center; 
                padding: 20px; } 
            .login-container { 
                width: 100%; 
                max-width: 420px; 
            } 
            .login-card { 
                background: #ffffff; 
                border-radius: 18px; 
                padding: 40px 35px; 
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18); 
            } 
            .logo { 
                text-align: center;
                 margin-bottom: 30px; 
                } 
            .logo-icon { 
                width: 58px; 
                height: 58px; 
                margin: 0 auto 14px; 
                background: #0c7a3d; 
                color: #ffffff;
                 border-radius: 15px; 
                 display: flex; 
                 align-items: center; 
                 justify-content: center;
                  font-size: 27px; 
                  font-weight: bold; 
                } 
            .logo h1 { 
                color: #12372a;
                 font-size: 30px; 
                 margin-bottom: 5px; 
                }
            .logo p { 
                color: #777; 
                font-size: 14px; 
            }
            .welcome { 
                margin-bottom: 25px; 
            } 
            .welcome h2 { 
                color: #1d2b24; 
                font-size: 22px; 
                margin-bottom: 7px; }
            .welcome p { color: #777; font-size: 14px; } 
            .form-group { margin-bottom: 20px; }
            .form-group label { display: block; color: #26352e; font-size: 14px; font-weight: 600; margin-bottom: 8px; } 
            .input-wrapper { position: relative; } 
            .input-wrapper span { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 17px; color: #888; } 
            .input-wrapper input { width: 100%; height: 48px; border: 1px solid #d9dfdc; border-radius: 10px; padding: 0 14px 0 43px; font-size: 15px; outline: none; transition: 0.2s; } 
            .input-wrapper input:focus { border-color: #0c7a3d; box-shadow: 0 0 0 3px rgba(12, 122, 61, 0.10); } 
            .password-wrapper input { padding-right: 48px; } 
            .toggle-password { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 17px; color: #777; } 
            .erro { background: #fff1f1; color: #c62828; border: 1px solid #ffd0d0; border-radius: 9px; padding: 12px 14px; margin-bottom: 20px; font-size: 14px; } 
            .btn-login { width: 100%; height: 49px; border: none; border-radius: 10px; background: #0c7a3d; color: #ffffff; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.2s; } 
            .btn-login:hover { background: #096632; transform: translateY(-1px); } .footer { text-align: center; margin-top: 28px; padding-top: 20px; border-top: 1px solid #eeeeee; } 
            .footer p { color: #999; font-size: 12px; line-height: 1.6; } 
            .footer strong { color: #0c7a3d; } 
            .back-home {
    display: block;
    text-align: center;
    margin-top: 18px;
    color: #0c7a3d;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s;
}

.back-home:hover {
    color: #075b2d;
}
            @media (max-width: 480px) { 
                .login-card { padding: 30px 22px; } 
                .logo h1 { font-size: 27px; }
            }
        </style> 
    </head> 
    <body> 
        <div class="login-container"> 
            <div class="login-card">
                <div class="logo">
                    <div class="logo-icon"> B </div> 
                    <h1>BizFlow</h1> 
                    <p>Sistema de Gestão Empresarial</p>
                </div> <div class="welcome"> <h2>Bem-vindo de volta</h2> 
                <p>Entre na sua conta para continuar.</p> </div> 
                <?php if (!empty($erro)): ?>
                <div class="erro"> 
                    <?php echo htmlspecialchars($erro); ?>
                </div> 
                <?php endif; ?>
                <form method="POST"> 
                    <div class="form-group"> 
                        <label for="email"> Email </label> 
                        <div class="input-wrapper">
                            <span>✉</span> 
                            <input type="email" id="email" name="email" placeholder="Digite o seu email" autocomplete="email" required > 
                        </div> 
                    </div> 
                    <div class="form-group"> 
                        <label for="senha"> Senha </label> 
                        <div class="input-wrapper password-wrapper">
           <span>🔒</span>
                            <input type="password" id="senha" name="senha" placeholder="Digite a sua senha" autocomplete="current-password" required > 
                            <button type="button" class="toggle-password" onclick="mostrarSenha()" aria-label="Mostrar senha" > 👁 </button> 
                        </div> 
                    </div>
                    <button type="submit" class="btn-login"> Entrar </button>
                    <a href="../index.php" class="back-home">
    					← Voltar ao início</a>
                </form> 
                <div class="footer">
                    <p> © 2026 BizFlow<br> Desenvolvido por <strong>José Ngimbi</strong> </p> 
                </div> 
            </div>
        </div> 
        <script> function mostrarSenha() { const campo = document.getElementById("senha"); const botao = document.querySelector(".toggle-password");
        if (campo.type === "password")
        
        { campo.type = "text"; botao.textContent = "🙈"; }
                                          else { campo.type = "password"; botao.textContent = "👁"; } }
        </script>
     

        </div> 
        </footer> 
</body> 
</html>