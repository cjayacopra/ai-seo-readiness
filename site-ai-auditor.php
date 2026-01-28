<?php 
/**
 * Plugin Name: AI SEO Readiness Auditor
 * Description: A high-performance SEO auditor that mimics AI crawlers to evaluate title tags, metadata, heading hierarchies, and image accessibility. Includes advanced heuristic detection for JavaScript-based sites and a scoring matrix to identify visibility gaps in the "first wave" of AI indexing.
 * Version: 2.3
 * Author: CJay D Acopra
 */

if( !defined('ABSPATH') ) exit;
require_once plugin_dir_path(__FILE__) . 'includes/class-auditor-scan.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-js-detector.php';

class WebCrawler {

    public function __construct()
    {
         // Admin menu
        add_action('admin_menu', [$this, 'wc_plugin_menu']);
        add_action("admin_enqueue_scripts", [$this, 'wc_plugin_assets']);

        // Frontend assets (for shortcode)
        add_action('wp_enqueue_scripts', [$this, 'wc_plugin_assets']);

        // Shortcode
        add_shortcode('ai-seo-readiness', [$this, 'render_shortcode']);

        // Ajax Handlers
        add_action('wp_ajax_website_crawl', [$this, 'ajax_handler']);
        add_action('wp_ajax_nopriv_website_crawl', [$this, 'ajax_handler']);
    }

    // Add Plugin Assets [css, js] to plugin
    public function wc_plugin_assets() {
        // CSS
        wp_enqueue_style('wc-style', plugin_dir_url(__FILE__) . 'assets/style.css');

        // JS - Incremented version to 2.0 for cache-busting
        wp_enqueue_script('wc-script', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery'], '2.0', true);

        // Add variables like ajaxurl and security nonce
        wp_localize_script('wc-script', 'wcVars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wc_crawl_nonce')
        ]);
    }

    // Add a plugin menu to WP Admin
    public function wc_plugin_menu() {
        add_menu_page(
            'Web Crawler', 
            'Web Crawler', 
            'manage_options', 
            'web-crawler', 
            [$this, 'handle_wc_menu_click']
        );
    }

    public function render_shortcode() {
        ob_start();
        $this->handle_wc_menu_click();
        return ob_get_clean();
    }

    // Handle Menu Click
    public function handle_wc_menu_click() {
       ?>
        <div class="wrap webcrawler-wrapper">
            <h2>Test Your Website</h2>
            <p class="desc">
                Enter a website URL to extract the metadata, headings, images and links.
            </p>

            <form action="javascript:void(0);" id="webform">
                <input type="text" name="website" id="website" placeholder="Enter Website URL (https://example.com)">
                <button type="submit" class="btn-crawl">Crawl Website</button>
            </form>

            <div id="results"></div>
        </div>
       <?php
    }

    public function ajax_handler() {
        // Security: Nonce verification
        check_ajax_referer('wc_crawl_nonce', 'security');

        $websiteURL = esc_url_raw($_POST['website_url']);
        
        // Performance: Single fetch with Chrome User-Agent and 30s timeout
        $response = wp_remote_get($websiteURL, [
            'timeout'    => 30,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36'
        ]);

        if( is_wp_error($response) ) {
            wp_send_json_error([
                'error' => 'Unable to fetch the URL data: ' . $response->get_error_message()
            ]);
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code === 403 || $code === 429) {
            wp_send_json_error([
                'error' => 'This site blocks automated crawlers (HTTP ' . $code . ').'
            ]);
        }

        // Crawl Website Logic
        $html = wp_remote_retrieve_body($response);

        // JS Dependency Detection
        $js_detection = JS_Dependency_Detector::detect($html);

        // Stability: Suppress HTML5 parsing errors
        libxml_use_internal_errors(true);
        $domObject = new DOMDocument();
        $domObject->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $reports = [
            'title' => ($titleTag = $domObject->getElementsByTagName('title')->item(0)) 
                        ? trim($titleTag->textContent) 
                        : 'N/A',
            'meta_description'  => '',
            'meta_robots'       => '',
            'headings'          => [],
            'paragraphs'        => [],
            'images'            => [],
            'links'             => [],
            'internal_links'    => [],
            'schema'            => [],
            'text_length'       => 0,
            'html_length'       => strlen($html),
            'list_elements'     => 0,
            'sentence_count'    => 0,
            'avg_sentence_len'  => 0,
            'avg_para_len'      => 0,
            'js_detection'      => $js_detection,
            'viewport'          => '',
            'identity_signals'  => [],
            'evidence_raw'      => [
                'missing_alt' => [],
                'empty_links' => [],
                'h1_tags'     => [],
            ]
        ];


        // Performance: Optimized meta extraction in a single loop
        foreach($domObject->getElementsByTagName('meta') as $meta) {
            $name    = strtolower($meta->getAttribute("name"));
            $property = strtolower($meta->getAttribute("property"));
            $content = trim($meta->getAttribute('content'));
            
            if( $name == "description" || $property == "og:description" ) {
                $reports['meta_description'] = $content;
            }

            if ($name === 'robots') {
                $reports['meta_robots'] = $content;
            }

            if ($name === 'viewport') {
                $reports['viewport'] = $content;
            }
        }

        // Heading [H1 -> H6] & Identity Signals
        $identityKeywords = ['about', 'contact', 'service', 'product', 'privacy', 'term'];
        foreach(['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $headTag) {
            foreach($domObject->getElementsByTagName($headTag) as $node) {
                $text = trim($node->textContent);
                $reports['headings'][] = [
                    'tag' => $headTag,
                    'text' => $text
                ];
                if ($headTag === 'h1') {
                    $reports['evidence_raw']['h1_tags'][] = $text;
                }
                
                // Check for identity signals in headings
                foreach ($identityKeywords as $keyword) {
                    if (stripos($text, $keyword) !== false) {
                        $reports['identity_signals'][] = $text;
                    }
                }
            }
        }

        // Paragraphs & Text Analysis
        $totalSentences = 0;
        foreach ($domObject->getElementsByTagName('p') as $p) {
            $text = trim($p->textContent);
            if ($text !== '') {
                $reports['paragraphs'][] = $text;
                $wordCount = str_word_count($text);
                $reports['text_length'] += $wordCount;
                
                // Estimate sentences
                $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
                $totalSentences += count($sentences);
            }
        }
        $reports['sentence_count'] = $totalSentences;
        if ($totalSentences > 0) {
            $reports['avg_sentence_len'] = $reports['text_length'] / $totalSentences;
        }
        if (count($reports['paragraphs']) > 0) {
            $reports['avg_para_len'] = $reports['text_length'] / count($reports['paragraphs']);
        }

        // Images
        foreach($domObject->getElementsByTagName('img') as $imageTag) {
            $src = $imageTag->getAttribute('src');
            $alt = $imageTag->getAttribute('alt');
            $reports['images'][] = [
                'src' => $src,
                'alt' => $alt
            ];
            
            if (empty($alt)) {
                $reports['evidence_raw']['missing_alt'][] = [
                    'src' => $src,
                    'tag' => '<img src="' . esc_attr($src) . '">'
                ];
            }
        }

        //Schema Markup
        foreach ($domObject->getElementsByTagName('script') as $script) {
            if ($script->getAttribute('type') === 'application/ld+json') {
                $reports['schema'][] = trim($script->textContent);
            }
        }

        // Internal Links 
        $parsedUrl = parse_url($websiteURL);
        $baseHost  = $parsedUrl['host'] ?? '';

        foreach ($domObject->getElementsByTagName('a') as $a) {
            $href = trim($a->getAttribute('href'));
            $text = trim($a->textContent);
            $aria = $a->getAttribute('aria-label');

            if (!$href) continue;

            $reports['links'][] = $href;

            if (strpos($href, $baseHost) !== false || strpos($href, '/') === 0) {
                $reports['internal_links'][] = $href;
            }

            // Evidence: Empty Links
            if (empty($text) && empty($aria)) {
                $reports['evidence_raw']['empty_links'][] = [
                    'href' => $href,
                    'tag'  => '<a href="' . esc_attr($href) . '"></a>'
                ];
            }

            // Check for identity signals in links
            foreach ($identityKeywords as $keyword) {
                if (stripos($text, $keyword) !== false) {
                    $reports['identity_signals'][] = $text;
                }
            }
        }

        // Text-to-Code Ratio
        $reports['text_to_code_ratio'] = $reports['html_length'] > 0
            ? round(($reports['text_length'] / $reports['html_length']) * 100, 2)
            : 0;

        // Lists
        foreach (['ul', 'ol'] as $listTag) {
            $reports['list_elements'] += $domObject->getElementsByTagName($listTag)->length;
        }


        // Run Auditor Scan
        $auditor = new Site_Auditor_Scan($reports);
        $scores  = $auditor->getScores();
        $evidence = $auditor->getEvidence();
        $js_analysis = $auditor->getJSAnalysis();
        $total   = $auditor->getTotal();
        $remarks = $auditor->getRemarks();


        ob_start();
        include plugin_dir_path(__FILE__) . 'includes/class-auditor-results.php';
        $html_output = ob_get_clean();

        wp_send_json([
            'status' => true,
            'website_link' => $websiteURL,
            'metrics' => $scores,
            'evidence' => $evidence,
            'js_analysis' => $js_analysis,
            'html' => $html_output
        ]);
    }
}

new WebCrawler();