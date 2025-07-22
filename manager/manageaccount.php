<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}
?>
<!-- Read-only Manage Account for Manager -->
<?php include '../admin/manageaccount.php'; ?>
<script>
// Disable all form inputs and buttons for manager
window.onload = function() {
  document.querySelectorAll('input, select, textarea, button').forEach(function(el) {
    el.disabled = true;
  });
};
</script>
