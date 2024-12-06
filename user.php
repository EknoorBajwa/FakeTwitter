
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($user_details['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user_details['username']); ?></h1>
                <?php if ($user_id === $current_user_id): ?>
                    <a href="edit_profile.php" class="edit-profile-btn">Edit Profile</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <div class="profile-details">
            <p class="user-bio"><?php echo nl2br(htmlspecialchars($user_details['bio'])); ?></p>
            <div class="follow-stats">
                <span>Followers: <?php echo $follow_counts['followers']; ?></span>
                <span>Following: <?php echo $follow_counts['following']; ?></span>
            </div>
<?php if ($user_id !== $current_user_id): ?>
    <form action="controller.php" method="POST">
        <input type="hidden" name="command" value="<?php echo $is_following ? 'unfollow' : 'follow'; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
	<input type="hidden" name="page" value="user">
        <button type="submit" class="follow-btn">
            <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
        </button>
    </form>
<?php endif; ?>

        </div>

<div class="tweet-section">
    <h2>Tweets</h2>
    <?php if ($user_tweets): ?>
        <?php foreach ($user_tweets as $tweet): ?>
            <div class="tweet">
                <p><?php echo nl2br(htmlspecialchars($tweet['content'])); ?></p>
                <span><?php echo date('F j, Y, g:i a', strtotime($tweet['created_at'])); ?></span>
		<span>Post ID: <?php echo htmlspecialchars($tweet['post_id']); ?></span>
                    <form action="controller.php" method="post" style="display:inline;">
                        <input type="hidden" name="page" value="user">
                        <input type="hidden" name="command" value="delete_post">
                        <input type="hidden" name="post_id" value="<?php echo $tweet['post_id']; ?>">
			<input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                        <button type="submit" class="delete-button">Delete</button>
                    </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No tweets available.</p>
    <?php endif; ?>
</div>

<form action="controller.php" method="POST">
    <h2>Edit Your Profile</h2>

    <label for="username">Username:</label>
    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user_details['username']); ?>">

    <label for="password">Password:</label>
    <input type="password" id="password" name="password">

    <label for="bio">Bio:</label>
    <textarea id="bio" name="bio"><?= htmlspecialchars($user_details['bio']); ?></textarea>

    <!-- Add user_id hidden field -->
    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>"> 

    <input type="hidden" name="command" value="update_profile">
    <input type="hidden" name="page" value="user">
    
    <input type="submit" value="Update Profile">
</form>

<!-- Add this section inside the user profile page (user.php) -->

<form method="POST" action="controller.php">
    <input type="hidden" name="page" value="user">
    <input type="hidden" name="command" value="delete_account">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
    <button type="submit" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">Delete Account</button>
</form>

 <form action="controller.php" method="POST">
            <input type="hidden" name="page" value="home">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <input type="hidden" name="command" value="view_home">
            <button type="submit" class="profile-button">
                Home
            </button>
        </form>

    </main>

    <footer>
        <p>&copy; 2024 Social Feed. All rights reserved.</p>
    </footer>
</body>
</html>
