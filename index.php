<?php
session_start();
require_once('funcs.php'); // DB接続関数をインクルード
$pdo = db_conn();

// メッセージが存在する場合に表示する
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // メッセージをクリア

// DBからActionの集計を取得
$query = $pdo->prepare('SELECT Action, COUNT(*) as count FROM UserActions GROUP BY Action');
$query->execute();
$dataPoints = $query->fetchAll(PDO::FETCH_ASSOC);

// ログインと失敗のカウントを取得
$loginCount = 0;
$failedLoginCount = 0;

// Failed Login Attemptのユーザーと回数を取得
$failedLoginUsersQuery = $pdo->prepare('SELECT UserID, COUNT(*) as count FROM UserActions WHERE Action = :action GROUP BY UserID');
$failedLoginUsersQuery->execute([':action' => 'Failed Login Attempt']);
$failedLoginUsers = $failedLoginUsersQuery->fetchAll(PDO::FETCH_ASSOC);

foreach ($dataPoints as $row) {
    if ($row['Action'] == 'Login') {
        $loginCount = $row['count'];
    } elseif ($row['Action'] == 'Failed Login Attempt') {
        $failedLoginCount = $row['count'];
    }
}

// 失敗の割合を計算
$failedLoginPercentage = 0;
if ($loginCount > 0) {
    $failedLoginPercentage = ($failedLoginCount / $loginCount) * 100;
}

// メール通知の条件チェック
if ($failedLoginPercentage >= $currentSettings['threshold']) {
    $to = $currentSettings['notify_email'];
    $subject = "警告: 失敗したログイン試行が高頻度です";
    $message = "注意: 失敗したログイン試行が " . number_format($failedLoginPercentage, 2) . "% に達しました。";

    send_notification_email($to, $subject, $message); // メール送信
}

// フォームが送信されたときの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $cause = $_POST['cause'];
    $countermeasure = $_POST['countermeasure'];

    // SQL文の準備
    $stmt = $pdo->prepare('INSERT INTO Feedback (Username, Cause, Countermeasure) VALUES (:username, :cause, :countermeasure)');
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':cause', $cause, PDO::PARAM_STR);
    $stmt->bindValue(':countermeasure', $countermeasure, PDO::PARAM_STR);

    // 実行とエラーハンドリング
    if ($stmt->execute()) {
        $_SESSION['message'] = "フィードバックが正常に登録されました。";
    } else {
        $_SESSION['message'] = "フィードバックの登録に失敗しました。";
    }

    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アクションログ</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>ユーザーアクションロググラフ</h1>

    <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <h2>失敗ログイン割合: <?= number_format($failedLoginPercentage, 2) ?>%</h2>

    <canvas id="actionChart" width="400" height="200"></canvas>
    <script>
        const data = <?= json_encode($dataPoints, JSON_HEX_TAG); ?>;
        const labels = data.map(item => item.Action);
        const counts = data.map(item => item.count);

        const ctx = document.getElementById('actionChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '# of Actions',
                    data: counts,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

    <h3>Failed Login Attemptユーザー</h3>
    <ul>
        <?php foreach ($failedLoginUsers as $user): ?>
            <li><?= htmlspecialchars($user['UserID']) ?>: <?= $user['count'] ?>回</li>
        <?php endforeach; ?>
    </ul>

    <!-- Navigation Links -->
    <nav>
        <a href="select.php" class="navbar-brand">ファイルアップロード</a>
        <a href="feedback.php" class="navbar-brand">フィードバック</a>
        <a href="setting.php" class="navbar-brand">設定</a>
        <a href="logout.php" class="navbar-brand">ログアウト</a>
    </nav>

    <!-- フリーアンケート -->
    <div class="container">
        <h1>フィードバックフォーム</h1>

        <!-- メッセージ表示 -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="mb-3">
                <label for="username" class="form-label">ユーザー名</label>
                <input type="text" id="username" name="username" required class="form-control">
            </div>
            <div class="mb-3">
                <label for="cause" class="form-label">想定原因</label>
                <input type="text" id="cause" name="cause" required class="form-control">
            </div>
            <div class="mb-3">
                <label for="countermeasure" class="form-label">対策</label>
                <input type="text" id="countermeasure" name="countermeasure" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">送信</button>
        </form>
    </div>
</body>
</html>
