=== AI SEO Readiness Auditor ===
Contributors: cjayacopra
Tags: seo, ai, audit, crawler, accessibility, structured-data, schema, readability, technical-seo
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Evaluate your website's visibility to AI crawlers and search engines with a weighted scoring matrix and evidence-based reporting.

== Description ==

AI SEO Readiness Auditor is a high-performance WordPress plugin designed to help business owners and developers understand how AI bots (like GPTBot) and search engines perceive their content. 

Unlike traditional SEO tools, this auditor focuses on **Machine Readability** and **Content Clarity**, using a 9-category weighted matrix to provide a "Crawler Visibility" score.

### Key Features:
*   **Weighted Scoring Matrix:** A 0-100 score based on Title, Metadata, Page Structure, Content Clarity, Readability, Image Alt Text, AI Clarity, Schema, and Technical SEO.
*   **Evidence-Based Reporting:** Don't just get a score; see the exact HTML elements that need fixing (e.g., missing alt tags, empty links, or heading gaps).
*   **JS Dependency Detection:** Heuristically identifies if your site relies on Client-Side Rendering (CSR), which might render it invisible to certain AI crawlers.
*   **Actionable Fix-it List:** Professional, non-technical advice for every issue detected.
*   **Security & Performance:** Optimized for speed with Chrome User-Agent mimicking and hardened with WordPress nonces.

== Installation ==

1. Upload the `ai-seo-readiness` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Access the auditor via the 'Web Crawler' menu in your admin dashboard, or use the shortcode `[ai-seo-readiness]` on any page.

== Frequently Asked Questions ==

= How is the score calculated? =
The score is a weighted average of 9 categories, with **Content Clarity** (20%) and **Page Structure** (15%) being the most significant factors for AI readability.

= Does this affect my SEO rankings? =
This tool is an auditor; it identifies gaps. Fixing the issues it highlights (like adding Schema or improving H1/H2 hierarchy) is a proven way to improve visibility for both AI and traditional search engines.

== Screenshots ==

1. The main audit interface showing the Crawler Visibility Score and metrics.
2. The detailed "Fix-it List" with HTML evidence and actionable advice.

== Changelog ==

= 2.0.0 =
*   **New Scoring Matrix:** Transitioned to a 9-category weighted model (v2.0).
*   **Evidence-Based Reporting:** Enhanced DOM crawler to extract exact HTML snippets for issues.
*   **JS Detection:** Added heuristic detection for Client-Side Rendering (CSR) framework fingerprints.
*   **UI/UX Revamp:** New progress bars, categorized issue cards, and technical "snippet dropdowns".
*   **Security Hardening:** Implemented `wc_crawl_nonce` verification for all AJAX operations.
*   **Performance:** Optimized network requests with Chrome User-Agent and reduced timeouts.
*   **Clean Versioning:** Normalized versioning to 2.0.0 across the codebase and integrated GitHub Update Checker.

== Upgrade Notice ==

= 2.0.0 =
Major update featuring the new weighted scoring matrix and evidence-based reporting. Recommended for all users to improve AI visibility.
