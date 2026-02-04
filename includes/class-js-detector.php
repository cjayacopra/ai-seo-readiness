<?php
/**
 * JS Dependency Detector
 * Heuristic analysis to determine if a site relies on Client-Side Rendering (CSR).
 */

if (!defined('ABSPATH')) exit;

class JS_Dependency_Detector {

    /**
     * Analyze HTML for JS dependency
     * 
     * @param string $html Raw HTML content
     * @return array Detection results
     */
    public static function detect($html) {
        if (empty($html)) {
            return [
                'is_js_reliant'      => false,
                'confidence_score'   => 0,
                'detected_framework' => 'Unknown',
                'technical_reason'   => 'Empty content provided.'
            ];
        }

        $html_len = strlen($html);
        
        // 1. Framework & Platform Identification
        $framework = self::identify_framework($html);
        $is_wordpress = (stripos($html, '/wp-content/') !== false || stripos($html, '/wp-includes/') !== false);
        
        // 2. Text-to-HTML Ratio Check
        $text_ratio = self::calculate_text_ratio($html);
        
        // 3. Main Content Container Check
        $empty_containers = self::check_empty_containers($html);

        // Calculate Confidence Score
        $confidence = 0;
        $reasons = [];

        // Signal: Framework Signatures
        if ($framework !== 'Unknown' && $framework !== 'WordPress') {
            $confidence += 45;
            $reasons[] = "Detected $framework signatures.";
        }

        // Signal: Text-to-Code Ratio
        if ($text_ratio < 0.02 && $html_len > 5000) {
            $confidence += 40;
            $reasons[] = "Extremely low text-to-code ratio (" . round($text_ratio * 100, 2) . "%).";
        } elseif ($text_ratio < 0.05) {
            $confidence += 20;
            $reasons[] = "Low text-to-code ratio.";
        }

        // Signal: Empty Mount Points
        if ($empty_containers) {
            $confidence += 35;
            $reasons[] = "Common JS framework mount points (like #root or #app) are empty in the raw source.";
        }

        // Signal: Common JS messages
        if (stripos($html, 'enable JavaScript') !== false || stripos($html, 'need to enable JavaScript') !== false) {
            $confidence += 10;
        }

        // Negative Signal: WordPress detection
        // WordPress is predominantly Server-Side Rendered (SSR)
        if ($is_wordpress) {
            $confidence -= 50;
            if ($framework === 'Unknown') $framework = 'WordPress';
        }

        $is_js_reliant = ($confidence >= 50);

        return [
            'is_js_reliant'      => $is_js_reliant,
            'confidence_score'   => max(0, min($confidence, 100)),
            'detected_framework' => $framework,
            'technical_reason'   => !empty($reasons) ? implode(' ', $reasons) : 'No significant JS dependency detected.'
        ];
    }

    /**
     * Identify JS Frameworks by fingerprints
     */
    private static function identify_framework($html) {
        $fingerprints = [
            'WordPress' => ['wp-block-library-css', '/wp-content/themes/', '/wp-includes/js/'],
            'Next.js'   => ['__NEXT_DATA__', 'id="__next"', 'id=\'__next\'', 'next-head-count'],
            'React'     => ['data-reactroot', 'react-id', 'id="root"', 'id=\'root\''],
            'Vue'       => ['v-cloak', 'data-v-', 'id="app"', 'id=\'app\''],
            'Angular'   => ['ng-version', 'ng-app', 'ng-controller'],
        ];

        foreach ($fingerprints as $name => $sigs) {
            foreach ($sigs as $sig) {
                if (stripos($html, $sig) !== false) return $name;
            }
        }

        return 'Unknown';
    }

    /**
     * Calculate ratio of actual text to raw HTML
     */
    private static function calculate_text_ratio($html) {
        // Remove scripts and styles first
        $clean = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $html);
        $text  = trim(strip_tags($clean));
        $text  = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
        
        $text_len = strlen($text);
        $html_len = strlen($html);

        return ($html_len > 0) ? ($text_len / $html_len) : 0;
    }

    /**
     * Check if common mount points are empty
     */
    private static function check_empty_containers($html) {
        // Look for common empty containers: <div id="root"></div> or <div id="app"></div>
        $pattern = '/<div\s+id=["\'](root|app|__next)["\']\s*><\/div>/i';
        return (bool) preg_match($pattern, $html);
    }
}
