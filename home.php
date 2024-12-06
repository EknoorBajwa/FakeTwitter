<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Social Feed</title>
    <link rel="stylesheet" href="styles.css"> <!-- Assuming styles.css is used for styling -->
    <style>
        .search-results {
            max-height: 300px; /* Adjust the max height as needed */
            overflow-y: auto; /* Enable vertical scrolling */
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
            background-color: #f9f9f9;
        }

        .search-result-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .post {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="header">
        <form action="controller.php" method="POST">
            <input type="hidden" name="page" value="user">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <input type="hidden" name="command" value="view_profile">
            <button type="submit" class="profile-button">
                My Profile
            </button>
        </form>
        <?php if (isset($_SESSION['signedin']) && $_SESSION['signedin'] === 'YES'): ?>
            <form action="controller.php" method="POST">
                <input type="hidden" name="page" value="startpage">
                <input type="hidden" name="command" value="signout">
                <button type="submit" class="signout-button">Sign Out</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Top Navigation Bar -->
<header>
    <div class="top-nav">
	<div id="logo" class="logo">
    <a href="#">Logo</a>
    <button type="button" onclick="loadDoc()">Change Content</button>
</div>

<script>
function loadDoc() {
  const xhttp = new XMLHttpRequest();
  xhttp.onload = function() {
    // Parse the JSON response
    const jsonResponse = JSON.parse(this.responseText);
    
    // Assuming the JSON contains a 'message' key, you can update the content
    document.getElementById("logo").innerHTML = jsonResponse.message;
  }
  xhttp.open("GET", "ajax_info.json", true); // Make sure the file is JSON
  xhttp.send();
}
</script>

        <div class="search-bar">
            <form action="controller.php" method="POST">
                <input type="hidden" name="page" value="home">
                <input type="hidden" name="command" value="search_posts">
                <input type="text" name="query" placeholder="Search..." value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>
</header>

<!-- Search Results Section (scrollable) -->
<?php if (isset($search_results)): ?>
    <div class="search-results">
        <h3>Search Results</h3>
        <?php if (count($search_results) > 0): ?>
            <?php foreach ($search_results as $post): ?>
                <div class="search-result-item">
                    <div class="post-header">
                        <img src="<?php echo htmlspecialchars($post['profile_pic']); ?>" alt="User Pic">
                        <span class="username"><?php echo htmlspecialchars($post['username']); ?></span>
                        <span class="timestamp"><?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></span>
                    </div>
                    <div class="tweet-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts match your search criteria.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Main Content Section -->
<main>
    <div class="main-content">
        <!-- Post Creation Form Section -->
        <?php if (isset($_SESSION['signedin']) && $_SESSION['signedin'] === 'YES'): ?>
            <div class="post-creation">
                <form action="controller.php" method="POST">
                    <input type="hidden" name="page" value="home">
                    <input type="hidden" name="command" value="create_post">
                    <textarea name="post_content" placeholder="What's on your mind?" rows="4" required></textarea>
                    <button type="submit">Post</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Main Feed Section -->
        <div class="feed-section">
            <?php
            if (isset($view_posts_result) && mysqli_num_rows($view_posts_result) > 0) {
                while ($post = mysqli_fetch_assoc($view_posts_result)): 
            ?>
                <div class="post">
                    <div class="post-header">
                        <img src="<?php echo htmlspecialchars($post['profile_pic']); ?>" alt="User Pic">
                        <span class="username"><?php echo htmlspecialchars($post['username']); ?></span>
                        <span class="timestamp"><?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></span>
                    </div>
                    <div class="tweet-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                    <div class="post-actions">
                        <!-- Like/Unlike Button Form -->
                        <?php
                        $is_liked = has_user_liked_post($_SESSION['user_id'], $post['post_id']);
                        ?>
                        <form action="controller.php" method="POST" class="like-form">
                            <input type="hidden" name="page" value="home">
                            <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                            <input type="hidden" name="command" value="<?= $is_liked ? 'unlike_post' : 'like_post' ?>">
                            <button type="submit"><?= $is_liked ? 'Unlike' : 'Like' ?></button>
                        </form>

                        <!-- Comment Button: Show comment form when clicked -->
                        <button class="comment-button" onclick="toggleCommentForm(<?= $post['post_id'] ?>)">Comment</button>
                        
                        <!-- Comment Form (hidden by default) -->
                        <div id="comment-form-<?= $post['post_id'] ?>" style="display:none;">
                            <form action="controller.php" method="POST">
                                <input type="hidden" name="page" value="home">
                                <input type="hidden" name="command" value="add_comment">
                                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                                <textarea name="comment_content" placeholder="Write a comment..." required></textarea>
                                <button type="submit">Post Comment</button>
                            </form>
                        </div>

                        <button>Retweet</button>
                    </div>
                    
                    <!-- Display Comments -->
                    <div class="comments">
                        <?php
                        // Fetch comments for the current post
                        $comments = get_comments_for_post($post['post_id']); 
                        foreach ($comments as $comment):
                        ?>
                            <div class="comment">
                                <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                <span class="timestamp"><?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endwhile;
            } else {
                echo "<p>No posts available.</p>";
            }
            ?>
        </div>
    </div>
</main>

<script>
    // Toggle the visibility of the comment form
    function toggleCommentForm(postId) {
        var form = document.getElementById('comment-form-' + postId);
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }
</script>

<!-- Sidebar Section -->
<div class="sidebar">
    <div class="suggested-users">
        <h3>Suggested Users to Follow</h3>
        <div class="suggested-user">
            <img src="user3.jpg" alt="User Pic" class="user-pic">
            <span class="username">User1</span>
            <button>Follow</button>
        </div>
        <div class="suggested-user">
            <img src="user4.jpg" alt="User Pic" class="user-pic">
            <span class="username">User2</span>
            <button>Follow</button>
        </div>
    </div>
    <div class="trending-topics">
        <h3>Trending Topics</h3>
        <ul>
            <li>Topic 1</li>
            <li>Topic 2</li>
            <li>Topic 3</li>
        </ul>
    </div>
</div>

<footer>
    <p>&copy; 2024 Social Feed. All rights reserved.</p>
</footer>
</body>
</html>
