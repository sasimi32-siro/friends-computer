<?php
// ===========================================
// DB 연결 설정 (사장님 정보)
// ===========================================
// ⚠️ 이 파일에 사장님 비밀번호 넣어주세요!
// 아래 'YOUR_PASSWORD_HERE' 부분만 실제 비밀번호로 바꾸시면 됩니다.

define('DB_HOST', 'localhost');
define('DB_NAME', 'sasimi32');
define('DB_USER', 'sasimi32');
define('DB_PASS', 'Tktlal32@naver');  // ← 여기에 사장님이 설정한 DB 비밀번호 입력!

// ===========================================
// 관리자 비밀번호 (사장님만 수정/삭제 가능)
// ===========================================
define('ADMIN_PASSWORD', 'Tktlal32@naver');  // ← 원하시면 이것도 바꾸세요!

// ===========================================
// DB 연결
// ===========================================
function getDB() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB 연결 실패: ' . $e->getMessage()]);
        exit;
    }
}

// CORS 허용 (브라우저에서 API 호출 가능하게)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
?>
