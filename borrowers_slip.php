<?php 
require_once __DIR__ . '/db.php';

// Handle delete
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM borrowers_slips WHERE id = $id");
    header("Location: borrowers_slip.php?deleted=1");
    exit();
}

require_once __DIR__ . '/header.php';
?>

<style>
    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }
    .header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .btn-primary {
        background: var(--color-brand);
        color: white;
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary:hover {
        background: var(--color-brand-dark);
    }
    .table-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        background: var(--color-overlay);
        padding: 15px;
        text-align: left;
        font-size: var(--text-xs);
        text-transform: uppercase;
        color: var(--color-text-secondary);
        border-bottom: 1px solid var(--color-border);
    }
    td {
        padding: 15px;
        border-bottom: 1px solid var(--color-border);
        font-size: var(--text-sm);
    }
    .badge {
        padding: 4px 10px;
        border-radius: var(--radius-full);
        font-size: 11px;
        font-weight: 600;
    }
    .badge-student { background: #e3f2fd; color: #1976d2; }
    .badge-personnel { background: #f3e5f5; color: #7b1fa2; }
    .badge-yes { background: #e8f5e9; color: #2e7d32; }
    .badge-no { background: #ffebee; color: #c62828; }
    
    .actions {
        display: flex;
        gap: 10px;
    }
    .btn-icon {
        padding: 6px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--color-border);
        background: white;
        color: var(--color-text-secondary);
        cursor: pointer;
        display: inline-flex;
    }
    .btn-icon:hover {
        background: var(--color-overlay);
        color: var(--color-text-primary);
    }
    .no-data {
        padding: 50px;
        text-align: center;
        color: var(--color-text-muted);
    }
</style>

<div class="container">
    <div class="header-flex">
        <div>
            <h2 style="font-size: var(--text-2xl); color: var(--color-text-primary);">Borrower's Slips</h2>
            <p style="color: var(--color-text-secondary); font-size: var(--text-sm);">Manage medical equipment borrowing records</p>
        </div>
        <a href="add_borrower_slip.php" class="btn-primary">
            <i data-lucide="plus" style="width: 18px; height: 18px;"></i> Create New Slip
        </a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Borrower Name</th>
                    <th>Category</th>
                    <th>Availability</th>
                    <th>Date Created</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $res = $conn->query("SELECT s.*, (SELECT COUNT(*) FROM borrower_slip_items WHERE slip_id = s.id) as item_count FROM borrowers_slips s ORDER BY s.created_at DESC");
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $cat_class = strtolower($row['category']) == 'student' ? 'badge-student' : 'badge-personnel';
                        $avail_class = strtolower($row['availability']) == 'yes' ? 'badge-yes' : 'badge-no';
                        echo "<tr>
                            <td>#{$row['id']}</td>
                            <td><strong>" . htmlspecialchars($row['borrower_name']) . "</strong></td>
                            <td><span class='badge $cat_class'>" . htmlspecialchars($row['category']) . "</span></td>
                            <td><span class='badge $avail_class'>" . htmlspecialchars($row['availability']) . "</span></td>
                            <td>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</td>
                            <td>" . $row['item_count'] . " item(s)</td>
                            <td class='actions'>
                                <a href='view_borrower_slip.php?id={$row['id']}' class='btn-icon' title='View & Print'><i data-lucide='eye' style='width: 16px; height: 16px;'></i></a>
                                <form method='POST' style='display:inline;' class='delete-form'>
                                    <input type='hidden' name='delete_id' value='{$row['id']}'>
                                    <button type='button' class='btn-icon delete-btn' style='color: #e53935;' title='Delete'><i data-lucide='trash-2' style='width: 16px; height: 16px;'></i></button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='no-data'>No borrower slips found. Click 'Create New Slip' to add one.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const confirmed = await showConfirm("Delete Slip?", "Are you sure you want to delete this borrower's slip? This action cannot be undone.");
            if (confirmed) {
                this.closest('form').submit();
            }
        });
    });
</script>
</body>
</html>
