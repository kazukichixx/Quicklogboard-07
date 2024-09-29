<?php
session_start();
require_once('funcs.php'); // DB接続関数をインクルード
$pdo = db_conn();

// 設定を取得する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $threshold = $_POST['threshold']; // 閾値を取得
    $notifyEmail = $_POST['notifyEmail']; // 通知用のメールアドレスを取得

    // SQLクエリを実行して設定を保存
    $stmt = $pdo->prepare('UPDATE settings SET threshold = :threshold, notify_email = :notifyEmail WHERE id = 1');
    $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
    $stmt->bindValue(':notifyEmail', $notifyEmail, PDO::PARAM_STR);
    
    // クエリの実行
    if ($stmt->execute()) {
        $_SESSION['message'] = "設定が更新されました。";
    } else {
        $_SESSION['message'] = "設定の更新に失敗しました。";
    }

    // リダイレクト
    header('Location: setting.php');
    exit();
}

// 現在の設定を取得
$stmt = $pdo->prepare('SELECT * FROM settings WHERE id = 1');
$stmt->execute();
$currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設定</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>設定</h1>

        <!-- メッセージ表示 -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="POST" action="setting.php">
            <div class="mb-3">
                <label for="threshold" class="form-label">失敗ログイン割合の閾値 (%)</label>
                <input type="number" id="threshold" name="threshold" value="<?= htmlspecialchars($currentSettings['threshold']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="notifyEmail" class="form-label">通知メールアドレス</label>
                <input type="email" id="notifyEmail" name="notifyEmail" value="<?= htmlspecialchars($currentSettings['notify_email']) ?>" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">設定を保存</button>
        </form>
        
        <p><a href="index.php" class="btn btn-secondary">ダッシュボードに戻る</a></p>
    </div>
</body>
</html>
