<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}
?>
<!-- Read-only Stats for Manager -->
<?php include '../admin/stats.php'; ?>
<script>
window.onload = function() {
  document.querySelectorAll('input, select, textarea, button').forEach(function(el) {
    el.disabled = true;
  });
};
</script>
