<?php
// ===========================================
// 고객 관리 API
// ===========================================
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

// 관리자 비밀번호 검증 함수
function checkAdmin() {
    $password = $_GET['admin_pw'] ?? $_POST['admin_pw'] ?? '';
    if (!isset($_SERVER['HTTP_X_ADMIN_PW'])) {
        $headers = getallheaders();
        $password = $headers['X-Admin-Pw'] ?? $password;
    } else {
        $password = $_SERVER['HTTP_X_ADMIN_PW'];
    }
    
    // POST 본문에서도 확인
    if (empty($password)) {
        $input = json_decode(file_get_contents('php://input'), true);
        $password = $input['admin_pw'] ?? '';
    }
    
    if ($password !== ADMIN_PASSWORD) {
        http_response_code(401);
        echo json_encode(['error' => '관리자 비밀번호가 틀렸습니다'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

try {
    // ============ GET: 전체 고객 조회 (누구나 가능) ============
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT id, phone, name, address, type, service, price, note, year, agent FROM customers ORDER BY 
            CASE WHEN agent = '' OR agent IS NULL THEN 1 ELSE 0 END,
            agent, name");
        $customers = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $customers], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============ POST: 추가/수정/삭제 (관리자 전용) ============
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        // 모든 쓰기 작업은 관리자 비번 필요
        if ($input['admin_pw'] !== ADMIN_PASSWORD) {
            http_response_code(401);
            echo json_encode(['error' => '관리자 비밀번호가 틀렸습니다'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // === 추가 ===
        if ($action === 'add') {
            $c = $input['customer'];
            $stmt = $pdo->prepare("INSERT INTO customers (id, phone, name, address, type, service, price, note, year, agent) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $c['id'] ?? uniqid('new_'),
                $c['phone'] ?? '',
                $c['name'] ?? '',
                $c['address'] ?? '',
                $c['type'] ?? 'other',
                $c['service'] ?? '',
                intval($c['price'] ?? 0),
                $c['note'] ?? '',
                $c['year'] ?? '',
                $c['agent'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => '추가 완료'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // === 수정 ===
        if ($action === 'update') {
            $c = $input['customer'];
            $stmt = $pdo->prepare("UPDATE customers SET phone=?, name=?, address=?, type=?, service=?, price=?, note=?, year=?, agent=? WHERE id=?");
            $stmt->execute([
                $c['phone'] ?? '',
                $c['name'] ?? '',
                $c['address'] ?? '',
                $c['type'] ?? 'other',
                $c['service'] ?? '',
                intval($c['price'] ?? 0),
                $c['note'] ?? '',
                $c['year'] ?? '',
                $c['agent'] ?? '',
                $c['id']
            ]);
            echo json_encode(['success' => true, 'message' => '수정 완료'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // === 삭제 ===
        if ($action === 'delete') {
            $id = $input['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => '삭제 완료'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // === 일괄 import (초기 데이터 입력용) ===
        if ($action === 'bulk_import') {
            $customers = $input['customers'] ?? [];
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT IGNORE INTO customers (id, phone, name, address, type, service, price, note, year, agent) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $count = 0;
            foreach ($customers as $c) {
                $stmt->execute([
                    $c['id'],
                    $c['phone'] ?? '',
                    $c['name'] ?? '',
                    $c['address'] ?? '',
                    $c['type'] ?? 'other',
                    $c['service'] ?? '',
                    intval($c['price'] ?? 0),
                    $c['note'] ?? '',
                    $c['year'] ?? '',
                    $c['agent'] ?? ''
                ]);
                $count++;
            }
            $pdo->commit();
            echo json_encode(['success' => true, 'imported' => $count], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        echo json_encode(['error' => '알 수 없는 action'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
