<?php
// ===========================================
// DB 테이블 생성 (최초 1번만 실행)
// ===========================================
// 사용법: 브라우저에서 https://sasimi32.cafe24.com/customers/install.php 접속
// 테이블이 만들어지면 이 파일은 삭제하셔도 돼요!

require_once 'config.php';

try {
    $pdo = getDB();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS customers (
        id VARCHAR(50) PRIMARY KEY,
        phone VARCHAR(50) DEFAULT '',
        name VARCHAR(200) DEFAULT '',
        address TEXT,
        type VARCHAR(20) DEFAULT 'other',
        service VARCHAR(500) DEFAULT '',
        price INT DEFAULT 0,
        note TEXT,
        year VARCHAR(10) DEFAULT '',
        agent VARCHAR(50) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_year (year),
        INDEX idx_agent (agent),
        INDEX idx_phone (phone),
        INDEX idx_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    
    echo json_encode([
        'success' => true,
        'message' => '✅ 테이블 생성 완료! 이제 import.php 실행하세요.'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
