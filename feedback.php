<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle new feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);

    if ($rating >= 1 && $rating <= 5 && !empty($comments)) {
        $stmt = $pdo->prepare("INSERT INTO food_feedback (user_id, rating, comments) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $rating, $comments]);
        $success_msg = "Thank you! Your feedback has been submitted successfully.";
    } else {
        $error_msg = "Please provide a valid rating (1-5) and write your comments.";
    }
}

// Fetch all feedback to display
$feedback_stmt = $pdo->query("SELECT f.*, s.student_name AS full_name, s.register_number FROM food_feedback f JOIN students s ON f.user_id = s.id ORDER BY f.feedback_id DESC");
$feedbacks = $feedback_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Feedback & Reviews</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f4f8; margin: 0; }
        header { background: #004080; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 900px; margin: 30px auto; padding: 20px; }
        .panel { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; font-size: 14px; }
        select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; box-sizing: border-box; }
        textarea { resize: vertical; height: 100px; }
        button { background: #004080; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px; }
        button:hover { background: #002b5c; }
        .back-btn { background: rgba(255,255,255,0.2); color: white; padding: 7px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; }
        .review-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 13px; color: #555; }
        .stars { color: #f59e0b; font-weight: bold; font-size: 16px; }
        .review-body { color: #333; font-size: 14px; line-height: 1.5; }
    </style>
</head>
<body>

<header>
    <h2>PKR Food Court - Feedback Portal</h2>
    <a href="student_portal.php" class="back-btn">⬅ Back to Menu</a>
</header>

<div class="container">
    <div class="panel">
        <h3 style="margin-top:0; color:#004080;">⭐ Share Your Experience</h3>
        
        <?php if($success_msg): ?><div class="success"><?= $success_msg ?></div><?php endif; ?>
        <?php if($error_msg): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Rating (1 to 5 Stars):</label>
                <select name="rating" required>
                    <option value="5">⭐⭐⭐⭐⭐ (5 - Excellent)</option>
                    <option value="4">⭐⭐⭐⭐ (4 - Very Good)</option>
                    <option value="3">⭐⭐⭐ (3 - Good)</option>
                    <option value="2">⭐⭐ (2 - Fair)</option>
                    <option value="1">⭐ (1 - Poor)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Your Comments / Suggestions:</label>
                <textarea name="comments" placeholder="Tell us about the food quality, taste, service speed, etc..." required></textarea>
            </div>

            <button type="submit" name="submit_feedback">Submit Feedback</button>
        </form>
    </div>

    <div class="panel">
        <h3 style="margin-top:0; color:#004080;">💬 Community Reviews & Feedback</h3>
        <?php if(empty($feedbacks)): ?>
            <p style="color: #777; text-align:center;">No feedback submitted yet. Be the first to share your thoughts!</p>
        <?php else: ?>
            <?php foreach($feedbacks as $fb): ?>
                <div class="review-card">
                    <div class="review-header">
                        <span><b><?= htmlspecialchars($fb['full_name']) ?></b> (<small><?= htmlspecialchars($fb['register_number']) ?></small>)</span>
                        <span class="stars"><?= str_repeat('⭐', $fb['rating']) ?></span>
                    </div>
                    <div class="review-body">
                        <?= nl2br(htmlspecialchars($fb['comments'])) ?>
                    </div>
                    <div style="font-size: 11px; color: #888; margin-top: 8px; text-align: right;">
                        📅 <?= htmlspecialchars($fb['created_at']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
