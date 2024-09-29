<?php
session_start();
require_once('funcs.php');
loginCheck(); // ログイン状態をチェック

// データベース接続
$pdo = db_conn();

// フィードバックの取得
$stmt = $pdo->prepare('SELECT * FROM Feedback ORDER BY CreatedDate DESC');
$status = $stmt->execute();

// データ表示
$feedbackData = '';
if ($status === false) {
    sql_error($stmt); // SQLエラー処理
} else {
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $feedbackData .= '<tr>';
        $feedbackData .= '<td>' . htmlspecialchars($r['Username']) . '</td>';
        $feedbackData .= '<td>' . htmlspecialchars($r['Cause']) . '</td>';
        $feedbackData .= '<td>' . htmlspecialchars($r['Countermeasure']) . '</td>';
        $feedbackData .= '<td>' . htmlspecialchars($r['CreatedDate']) . '</td>';
        $feedbackData .= '<td>';
        // 削除ボタン追加
        $feedbackData .= '<a href="delete_feedback.php?id=' . htmlspecialchars($r['ID']) . '" class="btn btn-danger btn-sm">削除</a>';
        $feedbackData .= '</td>'; // 操作用の列
        $feedbackData .= '</tr>';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フィードバック一覧</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>フィードバック一覧</h1>

        <!-- メッセージの表示 -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ユーザー名</th>
                    <th>想定原因</th>
                    <th>対策</th>
                    <th>登録日時</th>
                    <th>操作</th> <!-- 操作列を追加 -->
                </tr>
            </thead>
            <tbody>
                <?= $feedbackData ?>
            </tbody>
        </table>

        <p><a href="logout.php" class="btn btn-danger">ログアウト</a></p>
        <p><a href="index.php" class="btn btn-primary">ダッシュボードに戻る</a></p>
    </div>
</body>
</html>
