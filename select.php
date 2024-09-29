<?php
session_start();
require_once('funcs.php'); // DB接続関数をインクルード
loginCheck(); // ログイン状態をチェック

// データベース接続
$pdo = db_conn();

// ユーザーアクションの取得
$stmt = $pdo->prepare('SELECT * FROM UserActions');
$status = $stmt->execute();

// データ表示
$actionData = '';
if ($status === false) {
    sql_error($stmt); // SQLエラー処理
} else {
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $actionData .= '<tr>';
        $actionData .= '<td>' . htmlspecialchars($r['UserID']) . '</td>';
        $actionData .= '<td>' . htmlspecialchars($r['Timestamp']) . '</td>';
        $actionData .= '<td>' . htmlspecialchars($r['Action']) . '</td>';
        $actionData .= '<td>';
        // 削除ボタン
        $actionData .= '<a href="delete_action.php?id=' . htmlspecialchars($r['ID']) . '" class="btn btn-danger btn-sm">削除</a>'; // 注意: IDがPRIMARY KEYのカラムを仮定
        $actionData .= '</td>';
        $actionData .= '</tr>';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アクションデータ一覧</title>
    <link href="css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind CSS -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .navbar {
            background-color: #f6f6f6; /* グレーの背景色 */
        }
        .bg-gray-100 {
            background-color: #f7f7f7; /* 明るいグレー */
        }
        .btn-primary {
            background-color: #fbbf24; /* 黄色のボタン */
            border-color: #fbbf24; /* 黄色のボーダー */
        }
        .btn-danger {
            background-color: #e3342f; /* 赤のボタン */
            border-color: #e3342f; /* 赤のボーダー */
        }
        table {
            background-color: #ffffff; /* テーブルの背景色 */
        }
        th {
            background-color: #fbbf24; /* 黄色のヘッダー */
            color: #000; /* ヘッダーのテキスト色 */
        }
        tr:hover {
            background-color: #f7f7f7; /* ホバー時の明るいグレー */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">アクションデータ一覧</h1>
        <a href="index.php" class="btn btn-primary mb-3">ダッシュボード</a>

        <button id="toggleButton" class="btn btn-secondary mb-3" onclick="toggleTable()">トグルアクションデータ表示</button>

        <table class="table table-bordered toggle-table" id="actionTable">
            <thead>
                <tr>
                    <th>UserID</th>
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?= $actionData ?>
            </tbody>
        </table>

        <h3>ファイルアップロード</h3>
        <form method="post" action="upload.php" enctype="multipart/form-data">
            <label for="file">CSVファイルを選択:</label>
            <input type="file" name="file" id="file" accept=".csv" required class="form-control mb-2">
            <button type="submit" class="btn btn-primary">アップロード</button>
        </form>

        <p><a href="logout.php" class="btn btn-danger">ログアウト</a></p>
    </div>

    <script>
        function toggleTable() {
            const table = document.getElementById('actionTable');
            const displayStyle = table.style.display === 'none' ? 'table' : 'none
