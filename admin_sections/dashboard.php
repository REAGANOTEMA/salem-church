<?php
// ADMIN DASHBOARD SECTION - Salem Dominion Ministries
// Overview and statistics
?>

<div class="content-header">
    <h1 class="page-title">Dashboard Overview</h1>
    <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($admin_name); ?>! Here's your content management overview.</p>
</div>

<!-- Statistics Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <img src="<?php echo LOGO_PATH; ?>" alt="Salem Dominion Ministries" style="width: 40px; height: 40px;">
        </div>
        <div class="stat-number"><?php echo number_format($stats['sermons']); ?></div>
        <div class="stat-label">Total Sermons</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <img src="<?php echo LOGO_PATH; ?>" alt="Salem Dominion Ministries" style="width: 40px; height: 40px;">
        </div>
        <div class="stat-number"><?php echo number_format($engagement_stats['sermon_views']); ?></div>
        <div class="stat-label">Sermon Views</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <img src="<?php echo LOGO_PATH; ?>" alt="Salem Dominion Ministries" style="width: 40px; height: 40px;">
        </div>
        <div class="stat-number"><?php echo number_format($engagement_stats['sermon_reactions'] + $engagement_stats['gallery_reactions']); ?></div>
        <div class="stat-label">Total Reactions</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <img src="<?php echo LOGO_PATH; ?>" alt="Salem Dominion Ministries" style="width: 40px; height: 40px;">
        </div>
        <div class="stat-number"><?php echo number_format($engagement_stats['sermon_comments'] + $engagement_stats['gallery_comments']); ?></div>
        <div class="stat-label">Total Comments</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <img src="<?php echo LOGO_PATH; ?>" alt="Salem Dominion Ministries" style="width: 40px; height: 40px;">
        </div>
        <div class="stat-number"><?php echo number_format($engagement_stats['avg_testimonial_rating'], 1); ?></div>
        <div class="stat-label">Avg Rating</div>
    </div>
</div>

<!-- Engagement Details -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-chart-line"></i>
        Engagement Overview
    </h2>
    <div class="row">
        <div class="col-md-6">
            <h4 style="color: var(--midnight-blue); margin-bottom: 1rem;">Sermon Engagement</h4>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-eye me-2" style="color: var(--ocean-blue);"></i>Total Views</span>
                    <strong><?php echo number_format($engagement_stats['sermon_views']); ?></strong>
                </div>
            </div>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-heart me-2" style="color: var(--heavenly-gold);"></i>Reactions</span>
                    <strong><?php echo number_format($engagement_stats['sermon_reactions']); ?></strong>
                </div>
            </div>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-comment me-2" style="color: var(--ocean-blue);"></i>Comments</span>
                    <strong><?php echo number_format($engagement_stats['sermon_comments']); ?></strong>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <h4 style="color: var(--midnight-blue); margin-bottom: 1rem;">Gallery Engagement</h4>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-images me-2" style="color: var(--ocean-blue);"></i>Total Items</span>
                    <strong><?php echo number_format($stats['gallery']); ?></strong>
                </div>
            </div>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-heart me-2" style="color: var(--heavenly-gold);"></i>Reactions</span>
                    <strong><?php echo number_format($engagement_stats['gallery_reactions']); ?></strong>
                </div>
            </div>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-comment me-2" style="color: var(--ocean-blue);"></i>Comments</span>
                    <strong><?php echo number_format($engagement_stats['gallery_comments']); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-bolt"></i>
        Quick Actions
    </h2>
    <div class="row">
        <div class="col-md-6">
            <a href="?section=sermons" class="btn-submit" style="display: block; text-align: center; text-decoration: none;">
                <i class="fas fa-plus me-2"></i>Add New Sermon
            </a>
        </div>
        <div class="col-md-6">
            <a href="?section=events" class="btn-submit" style="display: block; text-align: center; text-decoration: none;">
                <i class="fas fa-plus me-2"></i>Create Event
            </a>
        </div>
        <div class="col-md-6">
            <a href="?section=news" class="btn-submit" style="display: block; text-align: center; text-decoration: none;">
                <i class="fas fa-plus me-2"></i>Publish News
            </a>
        </div>
        <div class="col-md-6">
            <a href="?section=gallery" class="btn-submit" style="display: block; text-align: center; text-decoration: none;">
                <i class="fas fa-plus me-2"></i>Upload Media
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-clock"></i>
        Recent Activity
    </h2>
    
    <!-- Recent Sermons -->
    <h4 style="margin: 2rem 0 1rem 0; color: var(--midnight-blue);">Recent Sermons</h4>
    <div class="data-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_items['sermons'])): ?>
                    <?php foreach ($recent_items['sermons'] as $sermon): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($sermon['title'], 0, 50)); ?></td>
                            <td><?php echo date('M j, Y', strtotime($sermon['sermon_date'] ?? $sermon['created_at'])); ?></td>
                            <td><span class="badge" style="background: var(--ocean-blue); color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;"><?php echo htmlspecialchars($sermon['category'] ?? 'General'); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?section=sermons" class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--ocean-blue);">
                            <i class="fas fa-microphone" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p style="margin: 1rem 0 0 0;">No sermons yet. Start by adding your first sermon!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent Events -->
    <h4 style="margin: 2rem 0 1rem 0; color: var(--midnight-blue);">Recent Events</h4>
    <div class="data-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_items['events'])): ?>
                    <?php foreach ($recent_items['events'] as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($event['title'], 0, 50)); ?></td>
                            <td><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                            <td>
                                <span class="badge" style="background: <?php echo $event['status'] === 'upcoming' ? 'var(--heavenly-gold)' : 'var(--ocean-blue)'; ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?section=events" class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--ocean-blue);">
                            <i class="fas fa-calendar" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p style="margin: 1rem 0 0 0;">No events yet. Create your first event!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent News -->
    <h4 style="margin: 2rem 0 1rem 0; color: var(--midnight-blue);">Recent News</h4>
    <div class="data-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Published</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_items['news'])): ?>
                    <?php foreach ($recent_items['news'] as $news): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($news['title'], 0, 50)); ?></td>
                            <td><?php echo date('M j, Y', strtotime($news['created_at'])); ?></td>
                            <td><span class="badge" style="background: var(--ocean-blue); color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;"><?php echo htmlspecialchars($news['category'] ?? 'General'); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?section=news" class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--ocean-blue);">
                            <i class="fas fa-newspaper" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p style="margin: 1rem 0 0 0;">No news articles yet. Publish your first article!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent Gallery -->
    <h4 style="margin: 2rem 0 1rem 0; color: var(--midnight-blue);">Recent Gallery Items</h4>
    <div class="data-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Media</th>
                    <th>Type</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_items['gallery'])): ?>
                    <?php foreach ($recent_items['gallery'] as $gallery): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($gallery['title'], 0, 50)); ?></td>
                            <td>
                                <span class="badge" style="background: var(--gradient-divine); color: var(--midnight-blue); padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">
                                    <i class="fas fa-<?php echo $gallery['file_type']; ?> me-1"></i>
                                    <?php echo ucfirst($gallery['file_type']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($gallery['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?section=gallery" class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--ocean-blue);">
                            <i class="fas fa-images" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p style="margin: 1rem 0 0 0;">No gallery items yet. Upload your first media!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
