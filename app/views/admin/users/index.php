<?php
/** @var array $users */
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-people"></i>
            <span>User management</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">Manage User Accounts</h1>
        <p class="fh-muted mb-0">Change roles and activate/deactivate user access.</p>
    </div>
    <a class="btn btn-outline-success" href="<?= e(base_url('dashboard/admin')) ?>">Back to Admin Dashboard</a>
</div>

<?php if (!empty($users)): ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <?php $isActive = ((int)$u['is_active'] === 1); ?>
                <tr>
                    <td><?= e((string)$u['name']) ?></td>
                    <td><?= e((string)$u['email']) ?></td>
                    <td>
                        <form method="post" action="<?= e(base_url('admin/users/update-role')) ?>" class="d-flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
                            <select name="role" class="form-select form-select-sm">
                                <?php foreach (['visitor', 'owner', 'admin'] as $role): ?>
                                    <option value="<?= e($role) ?>" <?= $u['role'] === $role ? 'selected' : '' ?>>
                                        <?= e(ucfirst($role)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-success btn-sm">Save</button>
                        </form>
                    </td>
                    <td>
                        <?php if ($isActive): ?>
                            <span class="badge text-bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e((string)$u['created_at']) ?></td>
                    <td class="text-end">
                        <form method="post" action="<?= e(base_url('admin/users/toggle-status')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
                            <input type="hidden" name="is_active" value="<?= $isActive ? '0' : '1' ?>">
                            <button class="btn btn-sm <?= $isActive ? 'btn-outline-danger' : 'btn-outline-primary' ?>" type="submit">
                                <?= $isActive ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">No users found.</div>
<?php endif; ?>

