<?php
/**
 * Gaming Hub — Recommendation & Personalization Engine
 *
 * Algorithms implemented:
 *  1. Content-Based Filtering  — TF-IDF tag vectors + Cosine Similarity
 *  2. Collaborative Filtering  — User-Item matrix + Jaccard Similarity
 *  3. Trending                 — Time-Decayed Weighted Scoring (purchase > cart > view)
 *  4. Personalized             — Weighted subcategory affinity from user history
 *  5. Recently Viewed          — Chronological activity log
 */


/* ══════════════════════════════════════════════════════════════
   MATH HELPERS
   ══════════════════════════════════════════════════════════════ */

/**
 * Build a simple tag-frequency vector from a comma-separated tag string.
 * Treats each unique tag as a dimension; value = 1 (binary presence).
 */
function buildTagVector(string $tagStr): array {
    if (empty(trim($tagStr))) return [];
    $tags = array_map('trim', explode(',', strtolower($tagStr)));
    $vec  = [];
    foreach ($tags as $t) {
        if ($t !== '') $vec[$t] = ($vec[$t] ?? 0) + 1;
    }
    return $vec;
}

/**
 * Cosine Similarity between two sparse vectors (associative arrays).
 * Returns a float in [0, 1].
 */
function cosineSimilarity(array $a, array $b): float {
    if (empty($a) || empty($b)) return 0.0;

    $dot   = 0.0;
    $normA = 0.0;
    $normB = 0.0;

    foreach ($a as $dim => $val) {
        $dot   += $val * ($b[$dim] ?? 0);
        $normA += $val * $val;
    }
    foreach ($b as $val) {
        $normB += $val * $val;
    }

    $denom = sqrt($normA) * sqrt($normB);
    return $denom > 0 ? $dot / $denom : 0.0;
}

/**
 * Jaccard Similarity between two sets (arrays of items).
 * |A ∩ B| / |A ∪ B|
 */
function jaccardSimilarity(array $a, array $b): float {
    if (empty($a) || empty($b)) return 0.0;
    $intersection = count(array_intersect($a, $b));
    $union        = count(array_unique(array_merge($a, $b)));
    return $union > 0 ? $intersection / $union : 0.0;
}

/**
 * Time-decay factor: newer events get higher weight.
 * Uses exponential decay: e^(-λ·days)  where λ = 0.1
 */
function timeDecay(string $createdAt, float $lambda = 0.1): float {
    $days = max(0, (time() - strtotime($createdAt)) / 86400);
    return exp(-$lambda * $days);
}

/**
 * Action-type weight: purchases are stronger signals than views.
 */
function actionWeight(string $actionType): float {
    return match ($actionType) {
        'purchase' => 3.0,
        'cart'     => 2.0,
        'view'     => 1.0,
        default    => 0.5,
    };
}


/* ══════════════════════════════════════════════════════════════
   1. CONTENT-BASED FILTERING  (Cosine Similarity on Tag Vectors)
   ══════════════════════════════════════════════════════════════ */

/**
 * Get products similar to $productId using cosine similarity on product tags.
 * Falls back to same-subcategory if tags are sparse.
 */
function getSimilarProducts(int $productId, int $limit = 4): array {
    // Fetch the target product's tags and subcategory
    $target = executeQuery(
        "SELECT product_id, name, tags, subcategory_id FROM products WHERE product_id = ?",
        [$productId]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$target) return [];

    $targetVec = buildTagVector($target['tags'] ?? '');

    // Fetch all other active products with their tags
    $candidates = executeQuery(
        "SELECT p.product_id, p.name, p.price, p.discount_price, p.image, p.tags, p.subcategory_id,
                s.name AS subcategory_name, m.name AS category_name
         FROM products p
         JOIN subcategories s ON p.subcategory_id = s.subcategory_id
         JOIN main_categories m ON s.category_id = m.category_id
         WHERE p.product_id != ? AND p.is_active = TRUE",
        [$productId]
    )->fetchAll(PDO::FETCH_ASSOC);

    $scored = [];
    foreach ($candidates as $candidate) {
        $candidateVec = buildTagVector($candidate['tags'] ?? '');

        // Primary score: cosine similarity on tags
        $tagScore = cosineSimilarity($targetVec, $candidateVec);

        // Bonus: same subcategory gets +0.3 boost
        $subBonus = ($candidate['subcategory_id'] === $target['subcategory_id']) ? 0.3 : 0.0;

        $candidate['_score'] = $tagScore + $subBonus;
        $scored[]            = $candidate;
    }

    // Sort by score descending, take top $limit
    usort($scored, fn($a, $b) => $b['_score'] <=> $a['_score']);
    return array_slice($scored, 0, $limit);
}


/* ══════════════════════════════════════════════════════════════
   2. COLLABORATIVE FILTERING  (Jaccard Similarity on User Interactions)
   ══════════════════════════════════════════════════════════════ */

/**
 * "Customers also bought / viewed" using Jaccard similarity.
 *
 * Steps:
 *  a) Find all users who interacted with $productId
 *  b) For each such user, compute Jaccard(their items, target-product-users' items)
 *  c) Score candidate products by sum of Jaccard-weighted interactions
 */
function getCustomersAlsoBought(int $productId, int $limit = 4): array {
    // Get users who interacted with this product
    $targetUsers = executeQuery(
        "SELECT DISTINCT user_id FROM user_activity WHERE product_id = ? AND user_id IS NOT NULL",
        [$productId]
    )->fetchColumn();

    // If only one user returned as scalar, wrap; handle no results
    $allTargetUsers = executeQuery(
        "SELECT DISTINCT user_id FROM user_activity WHERE product_id = ? AND user_id IS NOT NULL",
        [$productId]
    )->fetchAll(PDO::FETCH_COLUMN);

    if (empty($allTargetUsers)) return [];

    // Build item-set for target-product-users
    $placeholders  = implode(',', array_fill(0, count($allTargetUsers), '?'));
    $targetItemSet = executeQuery(
        "SELECT DISTINCT product_id FROM user_activity
         WHERE user_id IN ($placeholders) AND product_id != ?",
        array_merge($allTargetUsers, [$productId])
    )->fetchAll(PDO::FETCH_COLUMN);

    if (empty($targetItemSet)) return [];

    // For every candidate product, compute Jaccard against $targetItemSet
    $candidatePlaceholders = implode(',', array_fill(0, count($targetItemSet), '?'));
    $candidates = executeQuery(
        "SELECT p.product_id, p.name, p.price, p.discount_price, p.image,
                s.name AS subcategory_name, m.name AS category_name
         FROM products p
         JOIN subcategories s ON p.subcategory_id = s.subcategory_id
         JOIN main_categories m ON s.category_id = m.category_id
         WHERE p.product_id IN ($candidatePlaceholders) AND p.is_active = TRUE",
        $targetItemSet
    )->fetchAll(PDO::FETCH_ASSOC);

    // Compute Jaccard for each: users who interacted with candidate vs target-users
    $scored = [];
    foreach ($candidates as $candidate) {
        $candidateUsers = executeQuery(
            "SELECT DISTINCT user_id FROM user_activity WHERE product_id = ? AND user_id IS NOT NULL",
            [$candidate['product_id']]
        )->fetchAll(PDO::FETCH_COLUMN);

        $candidate['_score'] = jaccardSimilarity($allTargetUsers, $candidateUsers);
        $scored[]            = $candidate;
    }

    usort($scored, fn($a, $b) => $b['_score'] <=> $a['_score']);
    return array_slice($scored, 0, $limit);
}


/* ══════════════════════════════════════════════════════════════
   3. TRENDING  (Time-Decayed Weighted Scoring)
   ══════════════════════════════════════════════════════════════ */

/**
 * Score = Σ [ actionWeight(type) × timeDecay(created_at) ]
 * This means a purchase yesterday beats 10 views from last week.
 */
function getTrendingProducts(int $limit = 4): array {
    // Pull all activity from last 30 days with timestamps
    $rows = executeQuery(
        "SELECT ua.product_id, ua.action_type, ua.created_at,
                p.name, p.price, p.discount_price, p.image, p.is_active,
                s.name AS subcategory_name, m.name AS category_name
         FROM user_activity ua
         JOIN products p ON ua.product_id = p.product_id
         JOIN subcategories s ON p.subcategory_id = s.subcategory_id
         JOIN main_categories m ON s.category_id = m.category_id
         WHERE ua.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
           AND p.is_active = TRUE
         ORDER BY ua.created_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        return getFeaturedProducts($limit); // fallback
    }

    // Aggregate weighted scores per product
    $scores   = [];
    $products = [];
    foreach ($rows as $row) {
        $pid = $row['product_id'];
        $w   = actionWeight($row['action_type']) * timeDecay($row['created_at']);

        $scores[$pid]   = ($scores[$pid] ?? 0) + $w;
        $products[$pid] = $row; // keep last row for product info
    }

    // Sort by score
    arsort($scores);
    $topIds = array_slice(array_keys($scores), 0, $limit);

    return array_values(array_map(fn($id) => $products[$id], $topIds));
}


/* ══════════════════════════════════════════════════════════════
   4. PERSONALIZED RECOMMENDATIONS  (Weighted Subcategory Affinity)
   ══════════════════════════════════════════════════════════════ */

/**
 * Build an affinity score for each subcategory the user interacted with,
 * then fetch products from top-affinity subcategories, re-ranked by
 * cosine similarity to the user's "interest vector" (union of their tags).
 */
function getPersonalizedRecommendations(int $userId, int $limit = 4): array {
    if (!$userId) return getTrendingProducts($limit);

    // Fetch user's entire interaction history with action type and timestamp
    $history = executeQuery(
        "SELECT ua.product_id, ua.action_type, ua.created_at,
                p.subcategory_id, p.tags
         FROM user_activity ua
         JOIN products p ON ua.product_id = p.product_id
         WHERE ua.user_id = ?
         ORDER BY ua.created_at DESC",
        [$userId]
    )->fetchAll(PDO::FETCH_ASSOC);

    if (empty($history)) return getTrendingProducts($limit);

    // ── Build subcategory affinity scores ──────────────────────────
    $affinityScores = [];
    $userTagVector  = [];
    $seenProducts   = [];

    foreach ($history as $row) {
        $seenProducts[] = $row['product_id'];
        $weight = actionWeight($row['action_type']) * timeDecay($row['created_at']);

        // Subcategory affinity
        $subId = $row['subcategory_id'];
        $affinityScores[$subId] = ($affinityScores[$subId] ?? 0) + $weight;

        // Build cumulative user interest tag vector
        $tagVec = buildTagVector($row['tags'] ?? '');
        foreach ($tagVec as $tag => $val) {
            $userTagVector[$tag] = ($userTagVector[$tag] ?? 0) + $val * $weight;
        }
    }

    // Normalize user tag vector (guard: empty if no products have tags yet)
    if (!empty($userTagVector)) {
        $maxVal = max($userTagVector) ?: 1;
        foreach ($userTagVector as &$v) $v /= $maxVal;
        unset($v);
    }

    // Top 3 subcategories by affinity
    arsort($affinityScores);
    $topSubs = array_slice(array_keys($affinityScores), 0, 3);

    if (empty($topSubs)) return getTrendingProducts($limit);

    $placeholders  = implode(',', array_fill(0, count($topSubs), '?'));
    $seenProducts  = array_unique($seenProducts);
    $excludeClause = '';
    $params        = $topSubs;

    if (!empty($seenProducts)) {
        $ep = implode(',', array_fill(0, count($seenProducts), '?'));
        $excludeClause = "AND p.product_id NOT IN ($ep)";
        $params = array_merge($params, $seenProducts);
    }

    // Fetch products from top-affinity subcategories (excluding already-seen)
    $candidates = executeQuery(
        "SELECT p.product_id, p.name, p.price, p.discount_price, p.image, p.tags,
                s.name AS subcategory_name, m.name AS category_name, p.subcategory_id
         FROM products p
         JOIN subcategories s ON p.subcategory_id = s.subcategory_id
         JOIN main_categories m ON s.category_id = m.category_id
         WHERE p.subcategory_id IN ($placeholders)
           AND p.is_active = TRUE $excludeClause",
        $params
    )->fetchAll(PDO::FETCH_ASSOC);

    if (empty($candidates)) {
        // Relax: allow seen products and try again
        $candidates = executeQuery(
            "SELECT p.product_id, p.name, p.price, p.discount_price, p.image, p.tags,
                    s.name AS subcategory_name, m.name AS category_name, p.subcategory_id
             FROM products p
             JOIN subcategories s ON p.subcategory_id = s.subcategory_id
             JOIN main_categories m ON s.category_id = m.category_id
             WHERE p.subcategory_id IN ($placeholders) AND p.is_active = TRUE",
            $topSubs
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Re-rank by cosine similarity to user interest vector ───────
    foreach ($candidates as &$candidate) {
        $productVec = buildTagVector($candidate['tags'] ?? '');

        // Cosine similarity vs user interest vector
        $tagScore = cosineSimilarity($userTagVector, $productVec);

        // Affinity boost: how much does the user like this subcategory?
        $affinityBoost = ($affinityScores[$candidate['subcategory_id']] ?? 0)
                         / (array_sum($affinityScores) ?: 1);

        $candidate['_score'] = ($tagScore * 0.7) + ($affinityBoost * 0.3);
    }
    unset($candidate);

    usort($candidates, fn($a, $b) => $b['_score'] <=> $a['_score']);
    return array_values(array_slice($candidates, 0, $limit));
}


/* ══════════════════════════════════════════════════════════════
   5. RECENTLY VIEWED  (Chronological, deduped)
   ══════════════════════════════════════════════════════════════ */

function getRecentlyViewed(int $userId, int $limit = 4): array {
    if (!$userId) return [];
    return executeQuery(
        "SELECT p.*, s.name AS subcategory_name, m.name AS category_name
         FROM user_activity ua
         JOIN products p ON ua.product_id = p.product_id
         JOIN subcategories s ON p.subcategory_id = s.subcategory_id
         JOIN main_categories m ON s.category_id = m.category_id
         WHERE ua.user_id = ? AND ua.action_type = 'view' AND p.is_active = TRUE
         GROUP BY p.product_id
         ORDER BY MAX(ua.created_at) DESC
         LIMIT " . (int)$limit,
        [$userId]
    )->fetchAll(PDO::FETCH_ASSOC);
}


/* ══════════════════════════════════════════════════════════════
   HELPER (kept for fallback)
   ══════════════════════════════════════════════════════════════ */

function resetKeysAndLimit(array $array, int $limit): array {
    return array_slice(array_values($array), 0, $limit);
}
