<?php
/**
 * parent-mood-data.php
 * JSON API — returns linked student's mood data from appropriate tables (mood_assessments & mood_logs).
 */

require_once __DIR__ . '/php-backend/init.php';
Auth::checkParentLogin();

header('Content-Type: application/json');

$out = [
    'studentName'      => 'your child',
    'weekLabels'       => [],
    'weekScores'       => [],
    'monthWeekAvgs'    => [0, 0, 0, 0],
    'dist'             => [0, 0, 0, 0],   // great, good, okay, stressed
    'distPct'          => ['0%', '0%', '0%', '0%'],
    'statAvg'          => '–',
    'statPeak'         => '–',
    'statLowest'       => '–',
    'statExercises'    => 0,
    'insightAvgPct'    => 0,
    'insightPeakDay'   => '–',
    'insightLowestDay' => '–',
];

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo json_encode($out);
    exit;
}

try {
    $sid = Session::get('active_student_id');

    if (!$sid) {
        $out['error'] = 'No active student selected.';
        echo json_encode($out);
        exit;
    }

    // Fetch student name
    $st = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $st->execute([$sid]);
    $studentName = $st->fetchColumn();
    $out['studentName'] = $studentName ?: 'your child';

    // ── Unified Mood Data (AI from chat_summary, Manual from mood_assessments) ──
    // Standardize all to 0-10 scale
    // chat_summary.avg_mood is already 0-10
    // mood_assessments.mood_score is usually 0-10, but we'll ensure consistency
    $moodUnionSQL = "
        SELECT avg_mood as score, summary_date as created_at FROM chat_summary WHERE user_id = :sid
        UNION ALL
        SELECT mood_score as score, created_at FROM mood_assessments WHERE user_id = :sid
    ";

    // 2. Weekly line chart: daily avg mood for last 7 days
    $qWeekly = "
        SELECT dy, ROUND(AVG(score),1) AS avg_mood FROM (
            SELECT DATE(summary_date) AS dy, avg_mood AS score FROM chat_summary WHERE user_id = :sid
            UNION ALL
            SELECT DATE(created_at) AS dy, mood_score AS score FROM mood_assessments WHERE user_id = :sid
        ) AS c
        WHERE dy >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY dy ORDER BY dy ASC
    ";
    $st = $pdo->prepare($qWeekly);
    $st->execute(['sid' => $sid]);
    $dayRows = $st->fetchAll();

    $dayMap = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $dayMap[$d] = 0;
    }
    foreach ($dayRows as $dr) $dayMap[$dr['dy']] = (float)$dr['avg_mood'];
    foreach ($dayMap as $date => $score) {
        $out['weekLabels'][] = date('D j', strtotime($date));
        $out['weekScores'][] = $score;
    }

    // 3. Stat cards — avg / peak / lowest (last 7 days aggregate)
    $qStats = "
        SELECT ROUND(AVG(score),1) as avg_score, MAX(score) as p, MIN(score) as l
        FROM ($moodUnionSQL) AS c
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ";
    $st = $pdo->prepare($qStats);
    $st->execute(['sid' => $sid]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    if ($r && $r['avg_score'] !== null) {
        $out['statAvg']    = (string)$r['avg_score'];
        $out['statPeak']   = (string)$r['p'];
        $out['statLowest'] = (string)$r['l'];
    }

    // Peak / lowest day name
    $qPeak = "SELECT dy, ROUND(AVG(score),1) AS m FROM(" . str_replace("score, created_at", "score, DATE(created_at) AS dy", $moodUnionSQL) . ") as c WHERE dy >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY dy ORDER BY m DESC LIMIT 1";
    $st = $pdo->prepare($qPeak); $st->execute(['sid' => $sid]);
    $pd = $st->fetch(); if ($pd) $out['insightPeakDay'] = date('l', strtotime($pd['dy']));

    $qLow = "SELECT dy, ROUND(AVG(score),1) AS m FROM(" . str_replace("score, created_at", "score, DATE(created_at) AS dy", $moodUnionSQL) . ") as c WHERE dy >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY dy ORDER BY m ASC LIMIT 1";
    $st = $pdo->prepare($qLow); $st->execute(['sid' => $sid]);
    $ld = $st->fetch(); if ($ld) $out['insightLowestDay'] = date('l', strtotime($ld['dy']));


    // 4. Monthly bar chart — week averages within the current month
    for ($w = 1; $w <= 4; $w++) {
        $wStart = date('Y-m-') . sprintf('%02d', ($w-1)*7+1);
        $wEnd   = date('Y-m-') . sprintf('%02d', min($w*7, (int)date('t')));
        $qMonthWk = "
            SELECT ROUND(AVG(score),1) 
            FROM (
                SELECT avg_mood as score, summary_date as created_at FROM chat_summary WHERE user_id = :sid
                UNION ALL
                SELECT mood_score as score, created_at FROM mood_assessments WHERE user_id = :sid
            ) AS c 
            WHERE DATE(created_at) BETWEEN :ws AND :we
        ";
        $st = $pdo->prepare($qMonthWk);
        $st->execute(['sid' => $sid, 'ws' => $wStart, 'we' => $wEnd]);
        $out['monthWeekAvgs'][$w-1] = (float)($st->fetchColumn() ?: 0);
    }

    // 5. Distribution counts — this month
    $qDist = "
        SELECT score FROM (
            SELECT avg_mood as score, summary_date as created_at FROM chat_summary WHERE user_id = :sid
            UNION ALL
            SELECT mood_score as score, created_at FROM mood_assessments WHERE user_id = :sid
        ) AS c 
        WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())
    ";
    $st = $pdo->prepare($qDist);
    $st->execute(['sid' => $sid]);
    $allMoods = $st->fetchAll(PDO::FETCH_COLUMN);
    $total = count($allMoods);
    foreach ($allMoods as $m) {
        if ($m >= 8)      $out['dist'][0]++;
        elseif ($m >= 6)  $out['dist'][1]++;
        elseif ($m >= 4)  $out['dist'][2]++;
        else              $out['dist'][3]++;
    }
    if ($total > 0) {
        $out['insightAvgPct'] = round(($out['dist'][0] + $out['dist'][1]) / $total * 100);
        $out['distPct'] = array_map(fn($n) => round($n/$total*100).'%', $out['dist']);
    }

    // 6. Exercises this month (from user_exercises table based on schema)
    $st = $pdo->prepare("SELECT COUNT(*) FROM user_exercises WHERE user_id=? AND MONTH(completed_at)=MONTH(NOW())");
    $st->execute([$sid]);
    $out['statExercises'] = (int)$st->fetchColumn();

} catch (Exception $e) {
    $out['error'] = "DB Error: " . $e->getMessage();
}

echo json_encode($out);
