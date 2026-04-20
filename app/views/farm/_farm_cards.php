<?php if (!empty($farms)): ?>
    <?php foreach ($farms as $farm): ?>
        <div class="col-md-4">
            <div class="card fh-card h-100">
                <img src="<?= e(!empty($farm['main_image']) ? base_url($farm['main_image']) : base_url('assets/img/farm-placeholder.jpg')) ?>"
                     class="card-img-top" alt="Farm image"
                     style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-success"><?= e($farm['name']) ?></h5>
                    <?php
                        $rInfo = $ratings[(int)$farm['id']] ?? ['count' => 0, 'average' => 0.0];
                        $rCount = (int)$rInfo['count'];
                        $rAvg = (float)$rInfo['average'];
                    ?>
                    <p class="card-text small mb-1">
                        <i class="bi bi-geo-alt"></i> <?= e($farm['city'] ?? 'Unknown city') ?>
                        <span class="fh-muted">&middot;</span>
                        <i class="bi bi-eye"></i> <?= (int)($visitCounts[(int)$farm['id']] ?? 0) ?> views
                        <?php if ($rCount > 0): ?>
                            <span class="fh-muted">&middot;</span>
                            <i class="bi bi-star-fill text-warning"></i>
                            <?= e(number_format($rAvg, 1)) ?>
                            <span class="fh-muted">(<?= (int)$rCount ?> reviews)</span>
                        <?php else: ?>
                            <span class="fh-muted">&middot;</span>
                            <i class="bi bi-star text-warning"></i>
                            <span class="fh-muted">No reviews</span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($sortedByDistance) && isset($farm['distance_km']) && $farm['distance_km'] !== null): ?>
                        <p class="card-text small text-success mb-1">
                            <i class="bi bi-signpost-2"></i>
                            <?= e(number_format((float)$farm['distance_km'], 1)) ?> km away
                        </p>
                    <?php endif; ?>
                    <p class="card-text small fh-muted flex-grow-1">
                        <?= e(mb_substr($farm['description'] ?? '', 0, 120)) ?>...
                    </p>
                    <a href="<?= e(base_url('farm/view?farm_id=' . (int)$farm['id'])) ?>" class="btn btn-outline-success btn-sm mt-2">
                        View Details &amp; Book
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-12">
        <div class="alert alert-light border mb-0">
            No farms matched your filters.
        </div>
    </div>
<?php endif; ?>
