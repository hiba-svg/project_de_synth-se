<?php
    session_start();

    // Redirect if no user is logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once 'db.php'; // Include your database connection

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id               = (int) $_POST['id'];
        $name             = trim($_POST['name']);
        $phone            = trim($_POST['phone']);
        $email            = trim($_POST['email']);
        $guests           = (int) $_POST['guests'];
        $reservation_date = $_POST['reservation_date'];
        $status           = $_POST['status'];

        $stmt = $mysqli->prepare("UPDATE reservations SET name = ?, phone = ?, email = ?, guests = ?, reservation_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param('sssssss', $name, $phone, $email, $guests, $reservation_date, $status, $id);

        if ($stmt->execute()) {
            header('Location: reservations.php?status=updated');
            exit;
        } else {
            error_log("Error updating reservation: " . $stmt->error);
            header('Location: reservations.php?status=error');
            exit;
        }
    }

    // Fetch reservation details for editing
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];

        $stmt = $mysqli->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            header('Location: reservations.php');
            exit;
        }

        $reservation = $result->fetch_assoc();
    } else {
        header('Location: reservations.php');
        exit;
    }
?>
<?php include_once 'includes/header.php'; ?>
<div class="container mt-5">
    <h2>Edit Reservation</h2>
    <form method="POST" action="update_reservation.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($reservation['id']); ?>">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($reservation['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($reservation['phone']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($reservation['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="guests">Number of Guests</label>
            <input type="number" min="1" class="form-control" id="guests" name="guests" value="<?php echo htmlspecialchars($reservation['guests']); ?>" required>
        </div>
        <div class="form-group">
            <label for="reservation_date">Reservation Date</label>
            <input type="date" class="form-control" id="reservation_date" name="reservation_date" value="<?php echo htmlspecialchars($reservation['reservation_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="pending"<?php echo($reservation['status'] === 'pending') ? ' selected' : ''; ?>>Pending</option>
                <option value="confirmed"<?php echo($reservation['status'] === 'confirmed') ? ' selected' : ''; ?>>Confirmed</option>
                <option value="canceled"<?php echo($reservation['status'] === 'canceled') ? ' selected' : ''; ?>>Canceled</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="reservations.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include_once 'includes/footer.php'; ?>