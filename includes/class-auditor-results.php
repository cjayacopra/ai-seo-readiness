<?php if (!empty($scores)) :

$js_analysis = $js_analysis ?? [];
$is_js_reliant = $js_analysis['is_js_reliant'] ?? false;
$evidence = $evidence ?? [];

function bar($value, $max) {
    return round(($value / $max) * 100);
}
?>

<div class="audit-card">

    <div class="audit-header">
        <h3>AI & SEO Readiness Score</h3>
        <p class="disclaimer">⚠️ This score checks your website’s structure and clarity. It does not include ads, backlinks, or social media.</p>
    </div>

    <div class="audit-body">

        <?php if ($is_js_reliant) : ?>
        <!-- JS RELIANCE WARNING -->
        <div class="js-warning">
            <div class="js-warning-header">
                <span class="warning-icon">⚠️</span>
                <strong>High JavaScript Reliance Detected</strong>
            </div>
            <p>This site appears to rely heavily on JavaScript (<?php echo esc_html($js_analysis['detected_framework']); ?>) to display content. Many AI bots cannot execute JavaScript and may see your site as a blank page.</p>
            <small><strong>Reason:</strong> <?php echo esc_html($js_analysis['technical_reason']); ?></small>
        </div>
        <?php endif; ?>

        <!-- SCORE GRAPH -->
        <div class="audit-score">
            <svg viewBox="0 0 220 220">
                <defs>
                    <!-- LOGO-MATCHED GRADIENT (25% STOPS) -->
                    <linearGradient id="scoreGradient" gradientUnits="userSpaceOnUse"
                        x1="0" y1="0" x2="220" y2="220">
                        <stop offset="0%"   stop-color="#facc15"/> 
                        <stop offset="25%"  stop-color="#f59e0b"/> 
                        <stop offset="50%"  stop-color="#f97316"/> 
                        <stop offset="75%"  stop-color="#0f766e"/> 
                        <stop offset="100%" stop-color="#0f766e"/>
                    </linearGradient>
                </defs>

                <!-- background ring -->
                <circle cx="110" cy="110" r="100" class="bg"/>

                <!-- progress ring -->
                <circle cx="110" cy="110" r="100"
                    class="progress"
                    stroke-dasharray="<?php echo ($total * 6.28); ?> 999"/>
            </svg>

            <div class="score-text">
                <span><?php echo $total; ?>%</span>
                <small><?php echo $remarks; ?></small>
            </div>
        </div>

        <!-- METRICS -->
        <div class="audit-metrics">

            <?php
            $rows = [
                'Page Title'      => bar($scores['title'], 5),
                'Metadata'        => bar($scores['metadata'], 5),
                'Page Structure'  => bar($scores['page_structure'], 5),
                'Content Clarity' => bar($scores['content_clarity'], 5),
                'Readability'     => bar($scores['readability'], 5),
                'Image Alt Text'  => bar($scores['image_alt'], 5),
                'AI Clarity'      => bar($scores['ai_clarity'], 5),
                'Schema Data'     => bar($scores['schema'], 5),
                'Technical SEO'   => bar($scores['tech_seo'], 5),
            ];

            foreach ($rows as $label => $val): ?>
                <div class="metric">
                    <div class="metric-header">
                        <span><?php echo esc_html($label); ?></span>
                        <strong><?php echo $val; ?>%</strong>
                    </div>
                    <div class="bar">
                        <div class="bar-fill" style="width:<?php echo $val; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <?php if (!empty($evidence)) : ?>
        <!-- EVIDENCE-BASED FIX-IT LIST -->
        <div class="audit-evidence">
            <h4>🛠️ Recommended Fix-it List</h4>
            <div class="evidence-list">
                <?php foreach ($evidence as $index => $item): ?>
                    <div class="evidence-item severity-<?php echo esc_attr($item['severity']); ?>">
                        
                        <div class="evidence-header">
                            <span class="evidence-badge"><?php echo strtoupper($item['severity']); ?></span>
                            <strong class="evidence-msg"><?php echo esc_html($item['message']); ?></strong>
                        </div>
                        
                        <div class="evidence-content">
                            <div class="evidence-summary">
                                <span>Found:</span> <?php echo esc_html($item['summary']); ?>
                            </div>
                            
                            <div class="evidence-fix">
                                <span>Action:</span> <?php echo esc_html($item['fix']); ?>
                            </div>

                            <?php if (!empty($item['details'])) : ?>
                                <div class="evidence-snippets-wrapper">
                                    <button class="snippet-toggle" type="button" data-target="snippets-<?php echo $index; ?>">
                                        Show Offending Elements (<?php echo count($item['details']); ?>) ▼
                                    </button>
                                    <div class="evidence-snippets" id="snippets-<?php echo $index; ?>" style="display:none;">
                                        <?php foreach ($item['details'] as $snippet): ?>
                                            <code><?php echo esc_html($snippet); ?></code>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else : ?>
            <div class="audit-evidence">
                <p class="all-pass">🎉 Excellent! No major issues detected in the raw HTML source.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php else : ?>
    <div class="audit-card">
        <h3>Web Crawling: Blocked</h3>
    </div>
<?php endif; ?>