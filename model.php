<?php
// model.php

$conn = mysqli_connect('localhost', 'w3ebajwa', 'w3ebajwa136', 'C354_w3ebajwa');
if (mysqli_connect_errno()) {
    echo "Error connecting to database: " . mysqli_connect_error();
    exit();
}

// Function to check if the username is already taken
function is_username_taken($username) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username);
    $sql = "SELECT * FROM Users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

// Function to register a new user
function register_user($username, $password, $email) {
    global $conn;

    if (!is_username_taken($username)) {
        $username = mysqli_real_escape_string($conn, $username);
        $password = mysqli_real_escape_string($conn, $password);
	$email = mysqli_real_escape_string($conn, $email);
        
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO Users (username, password, email) VALUES ('$username', '$hashed_password', '$email')";
        
        if (mysqli_query($conn, $sql)) {
            return true;
        } else {
            echo "Error adding username: " . mysqli_error($conn);
        }
    } else {
        echo "Username is taken";
    }
    return false; // Registration failed
}

// Function to validate user login
function validate_user($username, $password) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username);
    
    $sql = "SELECT * FROM Users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            // Verify the password
            return password_verify($password, $user['password']);
        }
    }
    return false; // User not found or password incorrect
}

// Function to get user ID from the database based on username
function get_user_id($username) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username); // Sanitize input
    $query = "SELECT user_id FROM Users WHERE username = '$username'"; // Adjusted column name
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['user_id']; // Assuming 'user_id' is the correct column name
    }
    
    return null; // Return null if the user is not found
}


// Function to fetch all posts from the database
function get_all_posts($conn) {
    // Query to get posts and user information
    $query = "
        SELECT p.post_id, p.content, p.created_at, p.user_id, u.username, u.profile_pic
        FROM Posts p
	Join Users u ON p.user_id = u.user_id
        ORDER BY p.created_at DESC
    ";

    // Run the query and return the result
    $result = mysqli_query($conn, $query);

    // Check for query execution errors
    if (!$result) {
        die("Database query failed: " . mysqli_error($conn));
    }

    return $result; // Return the result set
}

// Function to create a new post in the database
function create_post($user_id, $content, $conn) {
    $content = mysqli_real_escape_string($conn, $content); // Sanitize input
    $query = "INSERT INTO Posts (user_id, content) VALUES ('$user_id', '$content')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        return true; // Return true if the post is successfully created
    } else {
        return false; // Return false if the post creation failed
    }
}

/**
 * Fetch user details by user ID
 */
function get_user_details($user_id) {
    global $conn;

    // Sanitize the user_id to prevent SQL injection
    $user_id = mysqli_real_escape_string($conn, $user_id);

    // Query to fetch user details based on user_id
    $sql = "SELECT username, profile_pic, bio FROM Users WHERE user_id = '$user_id'";

    // Execute the query
    $result = mysqli_query($conn, $sql);

    // Check if the query was successful
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }

    // Fetch the user details
    $user_details = mysqli_fetch_assoc($result);

    // Close the database connection
    mysqli_free_result($result);

    return $user_details;
}

/**
 * Fetch all tweets by a user
 */
function get_user_tweets($user_id) {
    global $conn;

    // Sanitize user_id to prevent SQL injection
    $user_id = mysqli_real_escape_string($conn, $user_id);

    // Query to get all tweets from the user
    $sql = "SELECT post_id, content, created_at FROM Posts WHERE user_id = '$user_id' ORDER BY created_at DESC";
    
    // Execute the query
    $result = mysqli_query($conn, $sql);

    // Check for errors in query execution
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }

    // Fetch all tweets
    $tweets = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tweets[] = $row;
    }

    // Free the result set and close the connection
    mysqli_free_result($result);

    return $tweets;
}

/**
 * Get follower and following counts for a user
 */
function get_follow_counts($user_id) {
    global $conn;

    // Sanitize user_id to prevent SQL injection
    $user_id = mysqli_real_escape_string($conn, $user_id);

    // Count followers
    $sql_followers = "SELECT COUNT(*) AS followers FROM Follows WHERE followed_id = '$user_id'";
    $result_followers = mysqli_query($conn, $sql_followers);
    if (!$result_followers) {
        die("Error executing query: " . mysqli_error($conn));
    }
    $row_followers = mysqli_fetch_assoc($result_followers);
    $followers = $row_followers['followers'];

    // Count following
    $sql_following = "SELECT COUNT(*) AS following FROM Follows WHERE follower_id = '$user_id'";
    $result_following = mysqli_query($conn, $sql_following);
    if (!$result_following) {
        die("Error executing query: " . mysqli_error($conn));
    }
    $row_following = mysqli_fetch_assoc($result_following);
    $following = $row_following['following'];

    // Close the database connection
    mysqli_free_result($result_followers);
    mysqli_free_result($result_following);

    return ['followers' => $followers, 'following' => $following];
}

/**
 * Check if the current user is following another user
 */
function check_if_following($current_user_id, $target_user_id) {
    global $conn;

    // Sanitize input to prevent SQL injection
    $current_user_id = mysqli_real_escape_string($conn, $current_user_id);
    $target_user_id = mysqli_real_escape_string($conn, $target_user_id);

    // Query to check if current user is following the target user
    $sql = "SELECT COUNT(*) FROM Follows WHERE follower_id = '$current_user_id' AND followed_id = '$target_user_id'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }

    // Get the count result
    $row = mysqli_fetch_row($result);
    $count = $row[0];

    // Close the database connection
    mysqli_free_result($result);

    return $count > 0; // Returns true if following
}

/**
 * Follow a user
 */
function follow_user($follower_id, $followed_id) {
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $follower_id = mysqli_real_escape_string($conn, $follower_id);
    $followed_id = mysqli_real_escape_string($conn, $followed_id);

    // Insert query to follow a user
    $sql = "INSERT INTO Follows (follower_id, followed_id, created_at) VALUES ('$follower_id', '$followed_id', NOW())";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }

    // Close the database connection

    return $result;
}

/**
 * Unfollow a user
 */
function unfollow_user($follower_id, $followed_id) {
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $follower_id = mysqli_real_escape_string($conn, $follower_id);
    $followed_id = mysqli_real_escape_string($conn, $followed_id);

    // Delete query to unfollow a user
    $sql = "DELETE FROM Follows WHERE follower_id = '$follower_id' AND followed_id = '$followed_id'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }

    // Close the database connection

    return $result;
}

// Function to update user's profile (username, password, bio)
function update_user_profile($user_id, $username, $password = null, $bio = null) {
    global $conn;

    // Sanitize inputs
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $username = mysqli_real_escape_string($conn, $username);
    $bio = mysqli_real_escape_string($conn, $bio);

    // Prepare the update query (only update provided fields)
    $query = "UPDATE Users SET username = '$username'";

    // If password is provided, hash and include it in the query
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", password = '$hashed_password'";
    }

    // If bio is provided, include it in the query
    if (!empty($bio)) {
        $query .= ", bio = '$bio'";
    }

    // Add the WHERE clause to target the correct user
    $query .= " WHERE user_id = '$user_id'";

    // Execute the query
    $result = mysqli_query($conn, $query);

    // Return whether the update was successful
    return $result ? true : false;
}
function has_user_liked_post($user_id, $post_id) {
    global $conn;
    $query = "SELECT * FROM Likes WHERE user_id = $user_id AND post_id = $post_id";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0; // Return true if like exists
}
function add_like($user_id, $post_id) {
    global $conn;
    $query = "INSERT INTO Likes (user_id, post_id) VALUES ($user_id, $post_id)";
    return mysqli_query($conn, $query);
}
function remove_like($user_id, $post_id) {
    global $conn;
    $query = "DELETE FROM Likes WHERE user_id = $user_id AND post_id = $post_id";
    return mysqli_query($conn, $query);
}
function get_like_count($post_id) {
    global $conn;
    $query = "SELECT COUNT(*) AS like_count FROM Likes WHERE post_id = $post_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['like_count'];
}

function delete_user_account($user_id) {
    global $conn; // Ensure you have access to the database connection

    // Escape the user input (user_id) to avoid SQL injection
    $user_id = mysqli_real_escape_string($conn, $user_id);

    // First, delete the user's posts, likes, and follows
    $delete_posts_query = "DELETE FROM Posts WHERE user_id = $user_id";
    $result = mysqli_query($conn, $delete_posts_query);
    if (!$result) {
        return false; // Return false if the query failed
    }

    $delete_likes_query = "DELETE FROM Likes WHERE user_id = $user_id";
    $result = mysqli_query($conn, $delete_likes_query);
    if (!$result) {
        return false; // Return false if the query failed
    }

    $delete_follows_query = "DELETE FROM Follows WHERE follower_id = $user_id OR followed_id = $user_id";
    $result = mysqli_query($conn, $delete_follows_query);
    if (!$result) {
        return false; // Return false if the query failed
    }

    // Finally, delete the user record
    $delete_user_query = "DELETE FROM Users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $delete_user_query);
    if (!$result) {
        return false; // Return false if the query failed
    }

    // If all queries were successful, return true
    return true;
}

function delete_post($post_id, $user_id) {
    global $conn; // Use the global database connection

    // Delete the post directly
    $delete_post_query = "DELETE FROM Posts WHERE post_id = $post_id AND user_id = $user_id";

    if (mysqli_query($conn, $delete_post_query)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo "Post deleted successfully.";
            return true;
        } else {
            echo "Post not found or already deleted.";
            return false;
        }
    } else {
        echo "Error deleting post: " . mysqli_error($conn);
        return false;
    }
}

/**
 * Adds a comment to a post.
 */
function add_comment($post_id, $user_id, $content) {
    global $conn;

    // Sanitize input
    $post_id = mysqli_real_escape_string($conn, $post_id);
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $content = mysqli_real_escape_string($conn, $content);

    // Insert comment into the database
    $sql = "INSERT INTO Comments (post_id, user_id, content) VALUES ('$post_id', '$user_id', '$content')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        return true;
    } else {
        return false;
    }
}

/**
 * Fetch comments for a specific post.
 */
function get_comments_for_post($post_id) {
    global $conn;

    // Sanitize post_id to prevent SQL injection
    $post_id = mysqli_real_escape_string($conn, $post_id);

    // Query to get all comments for a specific post
    $sql = "SELECT c.content, c.created_at, u.username 
            FROM Comments c
            JOIN Users u ON c.user_id = u.user_id
            WHERE c.post_id = '$post_id'
            ORDER BY c.created_at DESC";

    $result = mysqli_query($conn, $sql);

    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }

    return  $comments;
}

function search_posts($query, $user_id) {
    global $conn;

    // Sanitize the input
    $query = mysqli_real_escape_string($conn, $query);

    // Perform the search
    $sql = "SELECT Posts.post_id, Posts.content, Posts.created_at, Users.username, Users.profile_pic
            FROM Posts
            JOIN Users ON Posts.user_id = Users.user_id
            WHERE Posts.content LIKE '%$query%'
            ORDER BY Posts.created_at DESC";

    $result = mysqli_query($conn, $sql);

    // Check for errors in query execution
    if (!$result) {
        die("Error searching posts: " . mysqli_error($conn));
    }

    // Fetch and return results
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }

    return $posts;
}


?>
