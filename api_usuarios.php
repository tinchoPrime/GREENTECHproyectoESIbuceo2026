<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuarios'])) {
    $_SESSION['usuarios'] = [
        ['id' => 1, 'nombre' => 'Administrador', 'email' => 'admin@ciclo.uy', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'rol' => 'admin', 'activo' => true],
        ['id' => 2, 'nombre' => 'Usuario',      'email' => 'usuario@ciclo.uy','password' => password_hash('usuario123', PASSWORD_DEFAULT),  'rol' => 'usuario', 'activo' => true],
    ];
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $email    = trim($body['email']    ?? '');
        $password =      $body['password'] ?? '';
        if (!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Email y contraseña son obligatorios.']);
            exit;
        }
        foreach ($_SESSION['usuarios'] as $u) {
            if ($u['email'] === $email && password_verify($password, $u['password'])) {
                if (!$u['activo']) {
                    echo json_encode(['success' => false, 'message' => 'Cuenta pendiente de aprobación.']);
                    exit;
                }
                echo json_encode(['success' => true, 'message' => 'OK', 'data' => ['id' => $u['id'], 'nombre' => $u['nombre'], 'email' => $u['email'], 'rol' => $u['rol']]]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
        break;

    case 'registro':
        $nombre   = trim($body['nombre']   ?? '');
        $email    = trim($body['email']    ?? '');
        $password =      $body['password'] ?? '';
        $rol      = trim($body['rol']      ?? '');
        if (!$nombre || !$email || !$password || !$rol) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            exit;
        }
        foreach ($_SESSION['usuarios'] as $u) {
            if ($u['email'] === $email) {
                echo json_encode(['success' => false, 'message' => 'Ese email ya está registrado.']);
                exit;
            }
        }
        $_SESSION['usuarios'][] = [
            'id'       => count($_SESSION['usuarios']) + 1,
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'rol'      => $rol,
            'activo'   => false
        ];
        echo json_encode(['success' => true, 'message' => 'Solicitud enviada. Un administrador la aprobará.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
}
