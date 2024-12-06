<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Page</title>
</head>
<body>
    <h1>Welcome to the Start Page</h1>
    
    <h2>Login</h2>
    <form action="controller.php" method="POST">
        <input type="hidden" name="page" value="startpage">
        <input type="hidden" name="command" value="signin">
        
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Login</button>
    </form>

    <h2>Sign Up</h2>
    <form action="controller.php" method="POST">
        <input type="hidden" name="page" value="startpage">
        <input type="hidden" name="command" value="signup">
        
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required>

	<label for="email">Email:</label>
        <input type="text" name="email" required>
        
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>
