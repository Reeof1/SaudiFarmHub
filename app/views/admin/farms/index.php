<?php
/** @var array $pending */
/** @var array $approved */
/** @var array $rejected */
?>

<div class="row">
    <div class="col-lg-10">
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-building-check"></i>
            <span>Farm governance</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-3 fh-section-title">Farm Approvals</h1>

        <ul class="nav nav-tabs fh-nav-tabs mb-3" id="farmTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    Pending (<?= count($pending) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                    Approved (<?= count($approved) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                    Rejected (<?= count($rejected) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <?php if (!empty($pending)): ?>
                    <?php foreach ($pending as $farm): ?>
                        <div class="card fh-card mb-2">
                            <div class="card-body d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h5 class="card-title text-success mb-1"><?= e((string)$farm['name']) ?></h5>
                                    <div class="small fh-muted mb-1">
                                        Owner: <?= e((string)$farm['owner_name']) ?> · City: <?= e((string)$farm['city']) ?>
                                    </div>
                                    <p class="card-text small fh-muted mb-0">
                                        <?= e(mb_substr((string)$farm['description'], 0, 160)) ?>...
                                    </p>
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <form method="post" action="<?= e(base_url('admin/farms/update-status')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e((string)$farm['id']) ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-outline-success btn-sm" type="submit">Approve</button>
                                    </form>
                                    <form method="post" action="<?= e(base_url('admin/farms/update-status')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e((string)$farm['id']) ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Reject</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="fh-card-soft p-4">
                        No farms waiting for approval.
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="approved" role="tabpanel">
                <?php if (!empty($approved)): ?>
                    <div class="list-group fh-card-soft p-2">
                        <?php foreach ($approved as $farm): ?>
                            <div class="list-group-item border-0 rounded-4 mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold text-success"><?= e((string)$farm['name']) ?></span>
                                    <span class="small fh-muted"> · <?= e((string)$farm['city']) ?></span>
                                </div>
                                <span class="badge text-bg-success">Approved</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="fh-card-soft p-4">
                        No approved farms yet.
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <?php if (!empty($rejected)): ?>
                    <div class="list-group fh-card-soft p-2">
                        <?php foreach ($rejected as $farm): ?>
                            <div class="list-group-item border-0 rounded-4 mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold text-danger"><?= e((string)$farm['name']) ?></span>
                                    <span class="small fh-muted"> · <?= e((string)$farm['city']) ?></span>
                                </div>
                                <span class="badge text-bg-danger">Rejected</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="fh-card-soft p-4">
                        No rejected farms.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

