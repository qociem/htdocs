<?php
// api.php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

// allow simple CORS for local testing (adjust for production)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

function input_json(){
    $data = json_decode(file_get_contents('php://input'), true);
    return $data ? $data : [];
}

function respond($data){
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Simple router by path parameter: ?path=projects or ?path=notes
if ($path === 'projects') {
    // Projects CRUD
    if ($method === 'GET') {
        // optional id ?id=#
        if (isset($_GET['id'])) {
            $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            respond($stmt->fetch());
        } else {
            $stmt = $GLOBALS['pdo']->query("SELECT * FROM projects ORDER BY updated_at DESC");
            respond($stmt->fetchAll());
        }
    } elseif ($method === 'POST') {
        $d = input_json();
        $name = trim($d['name'] ?? '');
        $desc = $d['description'] ?? '';
        if ($name === '') { http_response_code(400); respond(['error'=>'Nama project dibutuhkan']); }
        $stmt = $GLOBALS['pdo']->prepare("INSERT INTO projects (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
        respond(['success'=>true, 'id'=>$GLOBALS['pdo']->lastInsertId()]);
    } elseif ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $put);
        $id = $put['id'] ?? null;
        $name = $put['name'] ?? null;
        $desc = $put['description'] ?? null;
        if (!$id) { http_response_code(400); respond(['error'=>'id dibutuhkan']); }
        $stmt = $GLOBALS['pdo']->prepare("UPDATE projects SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $desc, $id]);
        respond(['success'=>true]);
    } elseif ($method === 'DELETE') {
        parse_str(file_get_contents('php://input'), $del);
        $id = $del['id'] ?? null;
        if (!$id) { http_response_code(400); respond(['error'=>'id dibutuhkan']); }
        $stmt = $GLOBALS['pdo']->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        respond(['success'=>true]);
    }
} elseif ($path === 'notes') {
    // Notes CRUD. Handles file upload via multipart/form-data when POST/PUT with files.
    if ($method === 'GET') {
        // ?project_id=#
        if (isset($_GET['id'])) {
            $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM notes WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            respond($stmt->fetch());
        } elseif (isset($_GET['project_id'])) {
            $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM notes WHERE project_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_GET['project_id']]);
            respond($stmt->fetchAll());
        } else {
            $stmt = $GLOBALS['pdo']->query("SELECT * FROM notes ORDER BY created_at DESC");
            respond($stmt->fetchAll());
        }
    } elseif ($method === 'POST') {
        // expect multipart/form-data for attachment
        $project_id = $_POST['project_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        if (!$project_id || $title === '') { http_response_code(400); respond(['error'=>'project_id dan title dibutuhkan']); }

        // handle file
        $attachment = null;
        $mime = null;
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $allowed = ['image/jpeg','image/png','image/gif','video/mp4','video/quicktime','video/webm'];
            if (!in_array($file['type'], $allowed)) {
                http_response_code(400); respond(['error'=>'Tipe file tidak didukung']);
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('att_') . '.' . $ext;
            $target = __DIR__ . '/uploads/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $target)) {
                http_response_code(500); respond(['error'=>'Gagal menyimpan file']);
            }
            $attachment = $filename;
            $mime = $file['type'];
        }

        $stmt = $GLOBALS['pdo']->prepare("INSERT INTO notes (project_id, title, content, attachment, mime_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$project_id, $title, $content, $attachment, $mime]);
        respond(['success'=>true, 'id'=>$GLOBALS['pdo']->lastInsertId()]);
    } elseif ($method === 'PUT') {
        // For simplicity, accept JSON update for text-only edits.
        parse_str(file_get_contents('php://input'), $put);
        $id = $put['id'] ?? null;
        $title = $put['title'] ?? null;
        $content = $put['content'] ?? null;
        if (!$id) { http_response_code(400); respond(['error'=>'id dibutuhkan']); }
        $stmt = $GLOBALS['pdo']->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $id]);
        respond(['success'=>true]);
    } elseif ($method === 'DELETE') {
        parse_str(file_get_contents('php://input'), $del);
        $id = $del['id'] ?? null;
        if (!$id) { http_response_code(400); respond(['error'=>'id dibutuhkan']); }
        // delete attachment file if exists
        $stmt = $GLOBALS['pdo']->prepare("SELECT attachment FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && $row['attachment']) {
            $file = __DIR__.'/uploads/'.$row['attachment'];
            if (file_exists($file)) @unlink($file);
        }
        $stmt = $GLOBALS['pdo']->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        respond(['success'=>true]);
    }
} else {
    http_response_code(404);
    respond(['error'=>'Unknown path']);
}
