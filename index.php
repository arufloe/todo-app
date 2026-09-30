<?php

// DATABASE CONNECTION

$host = "localhost";
$username = "root";
$password = "";
$database = "todo_app";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


// ADD TASK

if (isset($_POST["add_task"])) {

    $title = trim($_POST["title"]);

    if (!empty($title)) {

        $stmt = $conn->prepare(
            "INSERT INTO tasks (title) VALUES (?)"
        );

        $stmt->bind_param("s", $title);

        $stmt->execute();

        $stmt->close();
    }

    header("Location: index.php");
    exit;
}


// UPDATE TASK

if (isset($_POST["update_task"])) {

    $id = intval($_POST["id"]);
    $title = trim($_POST["title"]);

    if (!empty($title)) {

        $stmt = $conn->prepare(
            "UPDATE tasks SET title = ? WHERE id = ?"
        );

        $stmt->bind_param("si", $title, $id);

        $stmt->execute();

        $stmt->close();
    }

    header("Location: index.php");
    exit;
}


// COMPLETE TASK

if (isset($_GET["complete"])) {

    $id = intval($_GET["complete"]);

    $stmt = $conn->prepare(
        "UPDATE tasks SET completed = TRUE WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $stmt->close();

    header("Location: index.php");
    exit;
}


// DELETE TASK

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare(
        "DELETE FROM tasks WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $stmt->close();

    header("Location: index.php");
    exit;
}


// EDIT TASK

$editTask = null;

if (isset($_GET["edit"])) {

    $id = intval($_GET["edit"]);

    $stmt = $conn->prepare(
        "SELECT * FROM tasks WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $result = $stmt->get_result();

    $editTask = $result->fetch_assoc();

    $stmt->close();
}


// GET ALL TASKS

$result = $conn->query(
    "SELECT * FROM tasks ORDER BY id DESC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Todo App</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
        }

        .add-form {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }

        input[type="text"] {
            flex: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 12px 18px;
            border: none;
            border-radius: 5px;
            background: #333;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #555;
        }

        .task {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        .task-title {
            flex: 1;
        }

        .completed {
            text-decoration: line-through;
            color: #999;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .actions a {
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .complete {
            background: green;
            color: white;
        }

        .edit {
            background: orange;
            color: black;
        }

        .delete {
            background: red;
            color: white;
        }

        .edit-box {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>My Todo List</h1>


    <!-- EDIT TASK -->

    <?php if ($editTask): ?>

        <div class="edit-box">

            <h3>Edit Task</h3>

            <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= $editTask['id'] ?>"
                >

                <input
                    type="text"
                    name="title"
                    value="<?= htmlspecialchars($editTask['title']) ?>"
                    required
                >

                <button type="submit" name="update_task">
                    Update
                </button>

                <a href="index.php">
                    Cancel
                </a>

            </form>

        </div>

    <?php endif; ?>


    <!-- ADD TASK -->

    <form method="POST" class="add-form">

        <input
            type="text"
            name="title"
            placeholder="Enter a task..."
            required
        >

        <button type="submit" name="add_task">
            Add Task
        </button>

    </form>


    <!-- DISPLAY TASKS -->

    <?php if ($result->num_rows > 0): ?>

        <?php while ($task = $result->fetch_assoc()): ?>

            <div class="task">

                <div class="task-title">

                    <?php if ($task['completed']): ?>

                        <span class="completed">
                            <?= htmlspecialchars($task['title']) ?>
                        </span>

                    <?php else: ?>

                        <?= htmlspecialchars($task['title']) ?>

                    <?php endif; ?>

                </div>


                <div class="actions">

                    <?php if (!$task['completed']): ?>

                        <a
                            class="complete"
                            href="?complete=<?= $task['id'] ?>"
                        >
                            Complete
                        </a>

                    <?php endif; ?>


                    <a
                        class="edit"
                        href="?edit=<?= $task['id'] ?>"
                    >
                        Edit
                    </a>


                    <a
                        class="delete"
                        href="?delete=<?= $task['id'] ?>"
                        onclick="return confirm('Delete this task?')"
                    >
                        Delete
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No tasks yet.</p>

    <?php endif; ?>


</div>

</body>

</html>