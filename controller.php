<?php
ob_start(); // Start output buffering
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'model.php'; // Include model functions

// Handle redirection to startpage when no command is set and user is not signed in
if (empty($_POST['command']) && !isset($_SESSION['signedin'])) {
    include('startpage.php');
    exit();
}

$page = isset($_POST['page']) ? $_POST['page'] : 'startpage'; // Default page is startpage

// Handle different pages and commands
if ($page === 'startpage') {
    $command = isset($_POST['command']) ? $_POST['command'] : '';

    switch ($command) {
        case 'signup':
            $result = register_user($_POST['username'], $_POST['password'], $_POST['email']);
            echo $result ? "Registration successful <br>" : "Registration failed<br>";
            include('startpage.php');
            exit();

        case 'signin':
            if (validate_user($_POST['username'], $_POST['password'])) {
                $_SESSION['signedin'] = 'YES';
                $_SESSION['username'] = $_POST['username'];
                $_SESSION['user_id'] = get_user_id($_POST['username']);
                $view_posts_result = get_all_posts($conn);
                
                include('home.php');
                exit(); 
            } else {
                echo "Incorrect username or password<br>";
                include('startpage.php');
                exit();
            }

        case 'signout':
            session_unset();
            session_destroy();
            include('startpage.php');
            exit(); // Ensure script stops after redirect
        
        default:
            include('startpage.php');
    }
} elseif ($page === 'home') {
    if (isset($_SESSION['signedin']) && $_SESSION['signedin'] === 'YES') {
        $command = isset($_POST['command']) ? $_POST['command'] : '';
        switch ($command) {
            case 'create_post':
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_content'])) {
                    $post_content = mysqli_real_escape_string($conn, $_POST['post_content']);
                    $user_id = $_SESSION['user_id'];

                    $create_post_result = create_post($user_id, $post_content, $conn);
                    if ($create_post_result) {
			$view_posts_result = get_all_posts($conn);
                        include('home.php'); // Instead of header
                        exit();
                    } else {
                        echo "Error creating post.";
                    }
                }
                break;

	case 'view_home':
		$view_posts_result = get_all_posts($conn);
		include('home.php');
		exit();
		break;

            case 'like_post':
            case 'unlike_post':
                $post_id = $_POST['post_id'];
                $user_id = $_SESSION['user_id'];

                if ($command === 'like_post' && !has_user_liked_post($user_id, $post_id)) {
                    add_like($user_id, $post_id);
                } elseif ($command === 'unlike_post' && has_user_liked_post($user_id, $post_id)) {
                    remove_like($user_id, $post_id);
                }
		$view_posts_result = get_all_posts($conn);
                include('home.php'); // Instead of header
                exit();
                break;

            case 'add_comment':
                if (isset($_POST['post_id'], $_POST['comment_content']) && !empty($_POST['post_id']) && !empty($_POST['comment_content'])) {
                    $post_id = intval($_POST['post_id']);
                    $user_id = intval($_POST['user_id']);
                    $content = $_POST['comment_content'];

                    $comment_result = add_comment($post_id, $user_id, $content);

                    if ($comment_result) {
                        $_SESSION['message'] = "Comment added successfully!";
                    } else {
                        $_SESSION['message'] = "Error adding comment. Please try again.";
                    }
                } else {
                    $_SESSION['message'] = "Invalid comment data.";
                }
		$view_posts_result = get_all_posts($conn);
                include('home.php'); // Instead of header
                exit();
                break;

case 'search_posts':
    // Ensure the query exists and is not empty
    if (isset($_POST['query']) && !empty($_POST['query'])) {
        $search_query = $_POST['query']; // Use POST data here
        $user_id = $_SESSION['user_id']; // Ensure the user is logged in

        // Perform the search
        $search_results = search_posts($search_query, $user_id);

        // Pass the search results and include the home page
	$view_posts_result = get_all_posts($conn);
        include('home.php');
        exit();
    } else {
        // If the search term is empty, set a message and stay on home page
        $_SESSION['message'] = "Please enter a search term.";
	$view_posts_result = get_all_posts($conn);
        include('home.php');
        exit();
    }
    break;

        }
    } else {
        echo "Please log in to view this page.";
        include('startpage.php');
        exit();
    }
} elseif ($page === 'user') {
    if (!isset($_POST['user_id'])) {
        echo "User not specified.";
        exit();
    }

    $user_id = intval($_SESSION['user_id']);
    $current_user_id = $_SESSION['user_id'];
    $command = isset($_POST['command']) ? $_POST['command'] : '';

    if ($command === 'view_profile') {
        $user_details = get_user_details($user_id);
        $user_tweets = get_user_tweets($user_id);
        $follow_counts = get_follow_counts($user_id);
        $is_following = check_if_following($current_user_id, $user_id);
        include 'user.php';
        exit();
    }


    // Handle profile update
    elseif ($command === 'update_profile') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $bio = $_POST['bio'];
        $result = update_user_profile($user_id, $username, $password, $bio);

        $_SESSION['message'] = $result ? "Profile updated successfully!" : "Error updating profile.";
	 $user_details = get_user_details($user_id); // Get updated user details
    $user_tweets = get_user_tweets($user_id);   // Get updated user tweets
    $follow_counts = get_follow_counts($user_id); // Get updated follow counts
    $is_following = check_if_following($_SESSION['user_id'], $user_id);
        include('user.php');
        exit();
    }

    // Handle follow
    elseif ($command === 'follow') {
        $result = follow_user($current_user_id, $user_id);
        $_SESSION['message'] = $result ? "You are now following this user!" : "Error following user.";
	$user_details = get_user_details($user_id); // Get the user's profile details
    $user_tweets = get_user_tweets($user_id);   // Get the user's tweets
    $follow_counts = get_follow_counts($user_id); // Get follow counts
    $is_following = check_if_following($current_user_id, $user_id); // Check if current user follows the profile
	include('user.php');
        exit();
    }

    // Handle unfollow
    elseif ($command === 'unfollow') {
        $result = unfollow_user($current_user_id, $user_id);
        $_SESSION['message'] = $result ? "You have unfollowed this user." : "Error unfollowing user.";
	$user_details = get_user_details($user_id); // Get the user's profile details
    $user_tweets = get_user_tweets($user_id);   // Get the user's tweets
    $follow_counts = get_follow_counts($user_id); // Get follow counts
    $is_following = check_if_following($current_user_id, $user_id); // Check if current user follows the profile
        include('user.php');
        exit();
    }

    // Handle delete account
    elseif ($command === 'delete_account') {
        if ($user_id == $current_user_id) {
            $delete_result = delete_user_account($user_id);

            if ($delete_result) {
                session_unset();
                session_destroy();
                $_SESSION['message'] = "Your account has been deleted successfully.";
                include('startpage.php');
                exit();
            } else {
                $_SESSION['message'] = "Error deleting account. Please try again.";
                include('user.php');
                exit();
            }
        } else {
            $_SESSION['message'] = "You cannot delete another user's account.";
            include('user.php');
            exit();
        }
    }

    // Handle post deletion
    elseif ($command === 'delete_post') {
        if (empty($_POST['post_id']) || empty($_SESSION['user_id'])) {
            $_SESSION['message'] = "Invalid request. No post specified.";
            include('user.php');
            exit();
        }
        $current_user_id = $_SESSION['user_id'];
        $post_id = intval($_POST['post_id']);

        $delete_result = delete_post($post_id, $current_user_id);

        if ($delete_result) {
            $_SESSION['message'] = "Post deleted successfully!";
        } else {
            $_SESSION['message'] = "Error deleting post. Please try again, Post ID: " . $post_id . " ";
        }
	$user_details = get_user_details($current_user_id); // Get updated user details
    $user_tweets = get_user_tweets($current_user_id);   // Get updated user tweets (now without the deleted post)
    $follow_counts = get_follow_counts($current_user_id); // Get updated follow counts
    $is_following = check_if_following($_SESSION['user_id'], $current_user_id);
        include('user.php');
        exit();
    }
}

ob_end_flush(); // Send all buffered output
?>
