<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Acceso - CeiLab</title>
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <link href="../Estilos/sign-in.css" rel="stylesheet">    
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body class="bg-primary text-center">
        <main class="form-signin">
            <form id="formAcceso" action="../Clases/ClsLogin.php" method="POST">
                <img class="mb-4" src="../assets/img/Ceilab-logo.jpg" alt="" width="120">
                <h1 class="fw-normal h3 mb-3 text-white">Acceso</h1>
                
                <label for="inputEmail" class="visually-hidden">Email address</label>
                <input type="email" id="inputEmail" class="form-control" name="femail" placeholder="Tu email" value="<?php echo htmlspecialchars($_COOKIE['remember_email'] ?? ''); ?>" required autofocus>
                
                <label for="inputPassword" class="visually-hidden">Password</label>
                <input type="password" id="inputPassword" class="form-control" name="fcontrasenia" placeholder="Tu contraseña" required>
                
                <div class="checkbox mb-3">
                    <label class="text-white">
                        <input type="checkbox" value="on" id="rememberMe" name="rememberMe" <?php echo isset($_COOKIE['remember_email']) ? 'checked' : ''; ?>> Recuérdame
                    </label>
                </div>
                
                <button class="btn btn-info btn-lg w-100 fw-bold" type="submit">Entrar</button>
                
                <div id="contenedorModal"></div>

                <p class="mb-3 mt-5 text-white">© 2026</p>
            </form>
        </main>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        <script>
        document.getElementById('formAcceso').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (document.activeElement) {
                document.activeElement.blur();
            }

            let formData = new FormData(this);

            fetch('../Clases/ClsLogin.php', { 
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                let contenedor = document.getElementById('contenedorModal');
                contenedor.innerHTML = html;

                var elementoModal = document.getElementById("modalRespuesta");
                if (elementoModal) {
                    var miModal = new bootstrap.Modal(elementoModal, { focus: false });
                    miModal.show();

                    // NUEVO: Quita el foco del botón interno antes de que el modal se oculte y active aria-hidden
                    elementoModal.addEventListener('hide.bs.modal', function () {
                        let elementoEnfocado = elementoModal.querySelector(':focus');
                        if (elementoEnfocado) {
                            elementoEnfocado.blur();
                        }
                    });

                    // Limpia el DOM al cerrar completamente el modal
                    elementoModal.addEventListener('hidden.bs.modal', function () {
                        contenedor.innerHTML = '';
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        });
        </script>
    </body>
</html>