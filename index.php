<?php
require_once "Conexion.php";

$conexionDam = new Conexion();
$conexion = $conexionDam->conectar();
//selecciona todos los usuarios y une la tabla usuarios con la tabla tipo_documentos para obtener el tipo de documento correspondiente de cada usuario.
$sql = $conexion->prepare("
    SELECT * FROM usuarios  INNER JOIN tipo_documentos ON usuarios.id_tipo_documento = tipo_documentos.id_tipo_documento
    ");
$sql->execute();
$usuarios = $sql->fetchAll();
//Selecciona todos los registros de la tabla tipo_documentos para mostrarlos en el formulario de selección.
$sqlTiposDocumentos = $conexion->prepare("
    SELECT * FROM tipo_documentos
    ");
$sqlTiposDocumentos->execute();
$tipo_documentos = $sqlTiposDocumentos->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Modals</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <div class="container mx-auto mt-5">
        <!-- Botón para abrir la modal de crear usuario -->
        <button onclick="abrirModal()" class="btn btn-success mb-4">
            Crear Usuario
        </button>

        <!-- Tabla de usuarios -->
        <div class="table-responsive">
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo de Documento</th>
                        <th>Número de Documento</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Mostrar la información de cada usuario en una fila de la tabla -->
                    <?php foreach ($usuarios as $usuario) { ?>
                    <tr>
                        <td><?php echo $usuario['nombre']; ?></td>
                        <td><?php echo $usuario['glosa']; ?></td>
                        <td><?php echo $usuario['numero_documento']; ?></td>
                        <td>
                            <button class="btn btn-primary" onclick="abrirModal(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">Editar</button>
                            <button class="btn btn-danger" onclick="abrirModalBorrar(<?php echo $usuario['id_usuario']; ?>)">Eliminar</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Uno crear/editar usuario -->
    <div class="modal fade" id="modalOne" tabindex="-1" aria-labelledby="modalOneLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-white rounded-lg shadow-lg">
                <div class="modal-header bg-blue-500 text-white">
                    <h5 id="tituloModal" class="modal-title" id="modalOneLabel"></h5>
                    <button type="button" class="btn-close" id="closeModalOneIcon" aria-label="Cerrar"></button>
                </div>
                <!-- Formulario -->
                <form id="modalOneForm" method="POST" action="acciones.php">
                    <input type="hidden" name="accion" id="accion">
                    <input type="hidden" name="id" id="idUsuario">

                    <div class="mb-3 px-4">
                        <label for="nombre" class="form-label text-gray-700">Ingrese el Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Escribe aquí">
                    </div>
                    <div class="mb-3 px-4">
                        <label for="tipoDocumento" class="form-label text-gray-700">Tipo de documento</label>
                        <select class="form-control" name="tipoDocumento" id="tipoDocumento">
                            <option value="0">Seleccione Documento</option>
                            <?php foreach ($tipo_documentos as $tipo_documento) { ?>
                            <option value="<?= $tipo_documento['id_tipo_documento'] ?>"><?= $tipo_documento['glosa'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3 px-4">
                        <label class="form-label text-gray-700">Numero de Documento</label>
                        <input type="number" class="form-control" id="numeroDocumento" name="numeroDocumento" placeholder="Escribe aquí">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="closeModalOneFooterBtn">Cerrar</button>
                        <button type="submit" class="btn btn-danger" id="botonModalUno"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Dos -->
    <div class="modal fade" id="modalTwo" tabindex="-1" aria-labelledby="modalTwoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white rounded-lg shadow-lg">
                <div class="modal-header bg-green-500 text-white">
                    <h5 class="modal-title" id="modalTwoLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" id="closeModalTwoIcon" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-6 text-gray-700">
                    ¿Estás seguro de eliminar este usuario?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeModalTwoFooterBtn">Cerrar</button>
                    <button onclick="eliminar()" class="btn btn-primary">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar la Modal Uno
        const modalOneElement = document.getElementById('modalOne');
        const modalOne = new bootstrap.Modal(modalOneElement);

        // Inicializar la Modal Dos
        const modalTwoElement = document.getElementById('modalTwo');
        const modalTwo = new bootstrap.Modal(modalTwoElement);

        // Botón para abrir la Modal Uno
        function abrirModal(usuario = null) {
            if (usuario) {
                document.getElementById('tituloModal').innerText = 'Editar Usuario';
                document.getElementById('botonModalUno').innerText = 'Guardar';
                document.getElementById('idUsuario').value = usuario.id_usuario;
                document.getElementById('nombre').value = usuario.nombre;
                document.getElementById('tipoDocumento').value = usuario.id_tipo_documento;
                document.getElementById('numeroDocumento').value = usuario.numero_documento;
                document.getElementById('accion').value = 'editar';
            } else {
                document.getElementById('tituloModal').innerText = 'Crear Usuario';
                document.getElementById('botonModalUno').innerText = 'Crear';
                document.getElementById('modalOneForm').reset();
                document.getElementById('idUsuario').value = '';
                document.getElementById('accion').value = 'crear';
            }
            modalOne.show();
        }

        // Botón para cerrar la Modal Uno desde el pie de la modal
        document.getElementById('closeModalOneFooterBtn').addEventListener('click', function () {
            modalOne.hide();
        });

        // Ícono de cerrar en la cabecera de la Modal Uno
        document.getElementById('closeModalOneIcon').addEventListener('click', function () {
            modalOne.hide();
        });

        // Botón para abrir la Modal Dos
        function abrirModalBorrar(usuario) {
            document.getElementById('idUsuario').value = usuario.id_usuario;
            document.getElementById('accion').value = 'eliminar';
            modalTwo.show();
        }

        function eliminar() {
            document.getElementById('modalOneForm').submit();
        }

        // Botón para cerrar la Modal Dos desde el pie de la modal
        document.getElementById('closeModalTwoFooterBtn').addEventListener('click', function () {
            modalTwo.hide();
        });

        // Ícono de cerrar en la cabecera de la Modal Dos
        document.getElementById('closeModalTwoIcon').addEventListener('click', function () {
            modalTwo.hide();
        });
    </script>
</body>

</html>
